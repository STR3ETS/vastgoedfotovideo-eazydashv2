<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AanvraagWebsite;
use App\Models\AanvraagStatusLog;
use App\Models\Project;
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
                'owner', // ✅ toevoegen
                'tasks.questions',
                'callLogs' => fn ($q) => $q->latest()->with('user'),
                'statusLogs' => fn ($q) => $q->latest()->with('user'),
                'files',
            ])
            ->select(
                'id',
                'owner_id', // ✅ HIER
                'choice',
                'company',
                'contactName',
                'contactEmail',
                'contactPhone',
                'created_at',
                'status',
                'intake_at',
                'intake_duration',
                'intake_done',
                'intake_completed_at',
                'ai_summary'
            )
            ->latest()
            ->paginate(12);

        // Ruwe values uit de property (prospect/contact/intake/dead/lead)
        $allowedStatusVals = array_values($this->statusMap);

        // Voor de UI: label => value (sidebar)
        $statusMap = [];
        // Voor kaarten / logboek: value => label
        $statusByValue = [];

        foreach ($allowedStatusVals as $value) {
            $label = __('potentiele_klanten.statuses.' . $value);

            $statusMap[$label]     = $value;
            $statusByValue[$value] = $label;
        }

        $statusLabels = array_keys($statusMap);

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
            'status'           => ['required','string','in:'.implode(',', $allowed)],
            'intake_at_local'  => ['nullable','date_format:Y-m-d\TH:i:s'],
            'intake_duration'  => ['nullable','integer','min:15','max:240'],
            'tz'               => ['nullable','string'],
        ]);

        $oldStatus = $aanvraag->status;
        $newStatus = $data['status'];

        // 🚫 Intake alleen toegestaan vanaf 'contact'
        if ($newStatus === 'intake' && $oldStatus !== 'contact') {
            return response()->json([
                'success' => false,
                'message' => __('potentiele_klanten.errors.intake_only_from_contact'),
            ], 422);
        }

        // ✅ Nieuwe bron van waarheid: conduct_intake taak
        $conductDone = $aanvraag->tasks()
            ->where('type', 'conduct_intake')
            ->whereIn('status', ['done', 'completed', 'closed'])
            ->exists();

        // 🚫 Lead alleen toegestaan als conduct_intake = done
        if ($newStatus === 'lead' && !$conductDone) {
            return response()->json([
                'success' => false,
                'message' => __('potentiele_klanten.errors.lead_requires_intake'),
            ], 422);
        }

        $aanvraag->status = $newStatus;

        // ✅ Houd intake_done consistent met conduct_intake
        if ($newStatus === 'lead' && $conductDone) {
            $aanvraag->intake_done = true;
            $aanvraag->intake_completed_at = $aanvraag->intake_completed_at ?? now();
        }

        // ✅ Intake plannen mag ook terwijl status nog 'contact' blijft
        if (!empty($data['intake_at_local']) && in_array($newStatus, ['contact', 'intake'], true)) {
            $tz = $data['tz'] ?? config('app.timezone', 'Europe/Amsterdam');
            $local = \Carbon\Carbon::createFromFormat('Y-m-d\TH:i:s', $data['intake_at_local'], $tz);

            $aanvraag->intake_at = $local;
            $aanvraag->intake_duration = (int) ($data['intake_duration'] ?? 30);
        }

        $aanvraag->save();

        if ($newStatus === 'lead') {
            $aanvraag->project()->firstOrCreate(
                [], // geen extra voorwaarden, alleen aanvraag_id uit de relatie
                [
                    'status'        => 'preview',
                    'company'       => $aanvraag->company,
                    'contact_name'  => $aanvraag->contactName,
                    'contact_email' => $aanvraag->contactEmail,
                    'contact_phone' => $aanvraag->contactPhone,
                ]
            );
        }

        // value => label op basis van vertalingen
        $statusByValue = [];
        foreach (array_values($this->statusMap) as $value) {
            $statusByValue[$value] = __('potentiele_klanten.statuses.' . $value);
        }

        $label = $statusByValue[$aanvraag->status]
            ?? \Illuminate\Support\Str::headline(str_replace('_', ' ', $aanvraag->status));

        $log = \App\Models\AanvraagStatusLog::create([
            'aanvraag_website_id' => $aanvraag->id,
            'user_id'             => auth()->id(),
            'from_status'         => $oldStatus,
            'to_status'           => $newStatus,
            'changed_at'          => now(),
        ]);
        $log->loadMissing('user');

        $fromLabel = $oldStatus
            ? ($statusByValue[$oldStatus] ?? \Illuminate\Support\Str::headline(str_replace('_',' ', $oldStatus)))
            : '—';
        $toLabel = $newStatus
            ? ($statusByValue[$newStatus] ?? \Illuminate\Support\Str::headline(str_replace('_',' ', $newStatus)))
            : '—';
        $changedAt = ($log->changed_at ?? $log->created_at)?->format('d-m-Y H:i');
        $userName  = optional($log->user)->name ?? 'Onbekend';

        $logHtml = view('hub.potentiele-klanten.partials.status-log-item', [
            'log'          => $log,
            'valueToLabel' => $statusByValue,
        ])->render();

        $intakeHtml = null;
        if ($aanvraag->status === 'intake' && $aanvraag->intake_at) {
            // Server-side render van de intake-panel partial
            $intakeHtml = view('hub.potentiele-klanten.partials.intake-panel', [
                'aanvraag' => $aanvraag->fresh(), // zekerheid dat we de net opgeslagen waarden hebben
                'valueToLabel' => array_flip($this->statusMap),
            ])->render();
        }

        return response()->json([
            'success' => true,
            'id'      => $aanvraag->id,
            'status'  => $aanvraag->status,
            'label'   => $label,
            'intake_done' => (bool) $conductDone,
            'log'     => [
                'id'          => $log->id,
                'from_status' => $log->from_status,
                'to_status'   => $log->to_status,
                'from_label'  => $fromLabel,
                'to_label'    => $toLabel,
                'user_name'   => $userName,
                'changed_at'  => $changedAt,
                'html'        => $logHtml,
            ],
            'intake_html' => $intakeHtml, // ⇐ NIEUW
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
