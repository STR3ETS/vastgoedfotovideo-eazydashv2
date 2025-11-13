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
        $data = $request->validate([
            'files'   => ['required', 'array'],
            'files.*' => ['file', 'max:10240'], // 10MB per bestand, pas aan naar wens
        ]);

        $created = [];

        foreach ($data['files'] as $uploaded) {
            $path = $uploaded->store('aanvragen/'.$aanvraag->id, 'private'); // of 'public'

            $file = $aanvraag->files()->create([
                'original_name' => $uploaded->getClientOriginalName(),
                'path'          => $path,
                'mime_type'     => $uploaded->getClientMimeType(),
                'size'          => $uploaded->getSize(),
            ]);

            $created[] = $this->toFrontendArray($file);
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
