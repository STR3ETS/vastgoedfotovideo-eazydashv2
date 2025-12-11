<?php

namespace App\Http\Controllers;

use App\Models\AanvraagWebsite;
use App\Models\AanvraagFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AanvraagFileController extends Controller
{
    public function store(Request $request, AanvraagWebsite $aanvraag)
    {
        // ✅ 1) Eerst: check of er überhaupt files binnenkomen
        if (!$request->hasFile('files')) {
            return response()->json([
                'success' => false,
                'message' => 'Geen bestanden ontvangen (files ontbreekt).',
            ], 422);
        }

        // ✅ 2) Haal files op en normaliseer altijd naar array
        $files = $request->file('files');

        if ($files instanceof \Illuminate\Http\UploadedFile) {
            $files = [$files];
        }

        if (!is_array($files)) {
            $files = [];
        }

        // ✅ 3) Extra validatie op de daadwerkelijke file items
        // (niet alleen op input-structuur)
        foreach ($files as $f) {
            if (!$f instanceof \Illuminate\Http\UploadedFile) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ongeldig bestandstype ontvangen.',
                ], 422);
            }
        }

        $created = [];

        foreach ($files as $file) {
            // ✅ 4) Upload error check
            if (!$file->isValid()) {
                continue;
            }

            // ✅ 5) HARD GUARD tegen jouw specifieke crash
            $realPath = $file->getRealPath();
            if (!$realPath || !is_string($realPath) || trim($realPath) === '') {
                \Log::warning('[AanvraagFile] Empty real path skip', [
                    'aanvraag_id' => $aanvraag->id,
                    'original'    => $file->getClientOriginalName(),
                    'error'       => $file->getError(),
                ]);
                continue;
            }

            // ✅ 6) Opslaan
            $path = $file->store("aanvragen/{$aanvraag->id}", 'private');

            $dbFile = $aanvraag->files()->create([
                'name'          => basename($path),
                'original_name' => $file->getClientOriginalName(),
                'disk'          => 'private',
                'path'          => $path,
                'mime_type'     => $file->getClientMimeType(),
                'size'          => $file->getSize(),
            ]);

            $created[] = [
                'id'          => $dbFile->id,
                'name'        => $dbFile->original_name ?? $dbFile->name,
                'url'         => route('support.potentiele-klanten.files.download', $dbFile),
                'extension'   => strtolower(pathinfo($dbFile->original_name ?? $dbFile->name, PATHINFO_EXTENSION)),
                'size_human'  => $dbFile->size_human ?? null,
                'uploaded_at' => optional($dbFile->created_at)->format('d-m-Y H:i'),
            ];
        }

        if (!count($created)) {
            return response()->json([
                'success' => false,
                'message' => 'Geen geldig bestand ontvangen.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'files'   => $created,
        ]);
    }

    public function destroy(AanvraagFile $file)
    {
        // eventueel extra check: auth / policy / zelfde bedrijf

        if ($file->path && Storage::disk('private')->exists($file->path)) {
            Storage::disk('private')->delete($file->path);
        }

        $file->delete();

        return response()->json(['success' => true]);
    }

    public function download(AanvraagFile $file)
    {
        $disk = Storage::disk('private'); // of 'public'

        if (!$disk->exists($file->path)) {
            abort(404);
        }

        return $disk->download(
            $file->path,
            $file->original_name,
            $file->mime_type ? ['Content-Type' => $file->mime_type] : []
        );
    }

    protected function toFrontendArray(AanvraagFile $file): array
    {
        return [
            'id'          => $file->id,
            'name'        => $file->original_name,
            'url'         => route('support.potentiele-klanten.files.download', $file),
            'extension'   => strtolower(pathinfo($file->original_name, PATHINFO_EXTENSION)),
            'size_human'  => $file->size_human,
            'uploaded_at' => optional($file->created_at)->format('d-m-Y H:i'),
        ];
    }
}
