<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AanvraagWebsite;
use App\Models\AanvraagStatusLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class IntakeController extends Controller
{
    /**
     * GET /app/support/intake/availability?date=YYYY-MM-DD&tz=Europe/Amsterdam
     *
     * Levert drukke blokken terug in LOKALE tijd (geen Z), zonder UTC-conversie.
     * We gaan ervan uit dat intake_at in de database óók in lokale tijd is weggeschreven.
     */
    public function availability(Request $request)
    {
        $date = $request->query('date'); // 'YYYY-MM-DD'
        $tz   = $request->query('tz', config('app.timezone', 'Europe/Amsterdam'));

        if (!$date) {
            return response()->json(['busy' => []]);
        }

        // Daggrenzen in LOKALE TZ
        $localStart = Carbon::parse($date, $tz)->startOfDay();
        $localEnd   = Carbon::parse($date, $tz)->endOfDay();

        // Query direct op lokale waardes (GEEN ->timezone('UTC') of vergelijkbaar)
        $intakes = AanvraagWebsite::query()
            ->whereNotNull('intake_at')
            ->whereBetween('intake_at', [
                $localStart->format('Y-m-d H:i:s'),
                $localEnd->format('Y-m-d H:i:s'),
            ])
            ->get(['intake_at', 'intake_duration']);

        // Response: ook in LOKALE tijd, string zonder Z
        $busy = $intakes->map(function ($row) use ($tz) {
            // Neem intake_at zoals opgeslagen (lokale tijd) en forceer interpretatie in $tz
            $start = Carbon::parse($row->intake_at, $tz);
            $end   = (clone $start)->addMinutes((int)($row->intake_duration ?: 30));

            return [
                'start' => $start->format('Y-m-d\TH:i:s'), // bv. "2025-11-06T10:30:00"
                'end'   => $end->format('Y-m-d\TH:i:s'),
            ];
        })->values();

        return response()->json(['busy' => $busy]);
    }

    /**
     * PATCH /app/support/intake/{aanvraag}/complete
     * Markeer intake als afgerond. (Kies zelf wat je hier precies wil doen.)
     * Hier zet ik status op 'lead', zonder tijdconversies.
     */
    public function complete(Request $request, AanvraagWebsite $aanvraag)
    {
        $aanvraag->forceFill([
            'intake_done'         => true,
            'intake_completed_at' => now(),
        ])->save();

        return response()->json([
            'success'   => true,
            'completed' => true,
        ]);
    }

    /**
     * PATCH /app/support/intake/{aanvraag}/clear
     * Verwijder de geplande intake (maakt velden leeg), geen TZ-conversies.
     */
    public function clear(Request $request, AanvraagWebsite $aanvraag)
    {
        $oldStatus = $aanvraag->status;

        // 1) Intake leegmaken + status terug naar contact
        $aanvraag->forceFill([
            'intake_at'           => null,
            'intake_duration'     => null,
            'intake_done'         => false,
            'intake_completed_at' => null,
            'status'              => 'contact',
        ])->save();

        // 2) Label-map (slug => label)
        $statusByValue = [
            'prospect' => 'Prospect',
            'contact'  => 'Contact',
            'intake'   => 'Intake',
            'dead'     => 'Dead',
            'lead'     => 'Lead',
        ];

        $label = $statusByValue[$aanvraag->status]
            ?? Str::headline(str_replace('_', ' ', $aanvraag->status));

        // 3) Logboek-regel aanmaken
        $log = AanvraagStatusLog::create([
            'aanvraag_website_id' => $aanvraag->id,
            'user_id'             => auth()->id(),
            'from_status'         => $oldStatus,
            'to_status'           => $aanvraag->status,
            'changed_at'          => now(),
        ]);
        $log->loadMissing('user');

        $fromLabel = $oldStatus
            ? ($statusByValue[$oldStatus] ?? Str::headline(str_replace('_', ' ', $oldStatus)))
            : '—';
        $toLabel = $aanvraag->status
            ? ($statusByValue[$aanvraag->status] ?? Str::headline(str_replace('_', ' ', $aanvraag->status)))
            : '—';

        $changedAt = ($log->changed_at ?? $log->created_at)?->format('d-m-Y H:i');
        $userName  = optional($log->user)->name ?? 'Onbekend';

        $logHtml = view('hub.potentiele-klanten.partials.status-log-item', [
            'log'          => $log,
            'valueToLabel' => $statusByValue,
        ])->render();

        // intake_html is nu NULL (we hebben hem verwijderd)
        return response()->json([
            'success' => true,
            'id'      => $aanvraag->id,
            'status'  => $aanvraag->status, // 'contact'
            'label'   => $label,
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
            'intake_html' => null,
        ]);
    }
}
