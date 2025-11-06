<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AanvraagWebsite;
use App\Models\AanvraagStatusLog;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Validation\Rule;

class PotentieleKlantenController extends Controller
{
    /** UI label => DB slug */
    private array $statusMap = [
        'Prospect' => 'prospect',
        'Contact'  => 'contact',
        'Intake'   => 'intake',
        'Dead'     => 'dead',
        'Lead'     => 'lead',
    ];

    public function index()
    {
        $user = auth()->user();

        $aanvragen = AanvraagWebsite::with([
                'tasks.questions',
                'callLogs' => fn ($q) => $q->latest()->with('user'),
                'statusLogs' => fn ($q) => $q->latest()->with('user'),
            ])
            ->select('id','choice','company','contactName','contactEmail','contactPhone','created_at','status')
            ->latest()
            ->paginate(12);

        $statusMap         = $this->statusMap;
        $statusByValue     = array_flip($this->statusMap); // slug => label
        $statusLabels      = array_keys($this->statusMap);
        $allowedStatusVals = array_values($this->statusMap);

        $statusCounts = AanvraagWebsite::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('hub.potentiele-klanten.index', compact(
            'user',
            'aanvragen',
            'statusMap',
            'statusByValue',
            'statusLabels',
            'allowedStatusVals',
            'statusCounts'
        ));
    }

    public function updateStatus(Request $request, AanvraagWebsite $aanvraag)
    {
        $allowed = array_values($this->statusMap);

        $data = $request->validate([
            'status' => ['required','string','in:'.implode(',', $allowed)],
        ]);

        $oldStatus = $aanvraag->status;
        $newStatus = $data['status'];

        $aanvraag->update(['status' => $newStatus]);

        // slug => label
        $statusByValue = array_flip($this->statusMap);
        $label = $statusByValue[$aanvraag->status] ?? Str::headline(str_replace('_',' ', $aanvraag->status));

        // ✅ Statuslog maken
        $log = AanvraagStatusLog::create([
            'aanvraag_website_id' => $aanvraag->id,
            'user_id'             => auth()->id(),
            'from_status'         => $oldStatus,
            'to_status'           => $newStatus,
            'changed_at'          => now(),
        ]);

        $log->loadMissing('user');

        $fromLabel = $oldStatus
            ? ($statusByValue[$oldStatus] ?? Str::headline(str_replace('_',' ', $oldStatus)))
            : '—';

        $toLabel = $newStatus
            ? ($statusByValue[$newStatus] ?? Str::headline(str_replace('_',' ', $newStatus)))
            : '—';

        $changedAt = ($log->changed_at ?? $log->created_at)?->format('d-m-Y H:i');
        $userName  = optional($log->user)->name ?? 'Onbekend';

        // 🔁 Render dezelfde Blade-partial als bij een harde reload
        $logHtml = view('hub.potentiele-klanten.partials.status-log-item', [
            'log'          => $log,
            'valueToLabel' => $statusByValue,
        ])->render();

        return response()->json([
            'success' => true,
            'id'      => $aanvraag->id,
            'status'  => $aanvraag->status, // slug
            'label'   => $label,            // mooi label voor de badge
            'log'     => [
                'id'          => $log->id,
                'from_status' => $log->from_status,
                'to_status'   => $log->to_status,
                'from_label'  => $fromLabel,
                'to_label'    => $toLabel,
                'user_name'   => $userName,
                'changed_at'  => $changedAt,
                'html'        => $logHtml,  // 👈 hier pak je in JS de li-markup uit
            ],
        ]);
    }

    public function storeCall(Request $request, AanvraagWebsite $aanvraag)
    {
        $data = $request->validate([
            'outcome' => ['required', 'string', Rule::in(['geen_antwoord', 'gesproken'])],
            'note'    => ['nullable', 'string'],
        ]);

        $call = $aanvraag->callLogs()->create([
            'user_id'   => auth()->id(),
            'outcome'   => $data['outcome'],
            'note'      => $data['note'] ?? null,
            'called_at' => now(),
        ]);

        $call->loadMissing('user');

        return response()->json([
            'success' => true,
            'call'    => [
                'id'        => $call->id,
                'called_at' => $call->called_at?->format('d-m-Y H:i'),
                'outcome'   => $call->outcome,
                'note'      => $call->note,
                'user_name' => optional($call->user)->name,
            ],
        ]);
    }
}
