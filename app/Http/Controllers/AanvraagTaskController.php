<?php

namespace App\Http\Controllers;

use App\Models\AanvraagWebsite;
use App\Models\AanvraagTask;
use Illuminate\Http\Request;

class AanvraagTaskController extends Controller
{
    public function updateStatus(Request $request, AanvraagWebsite $aanvraag)
    {
        /**
         * ✅ Alleen echte taken in deze module
         */
        $allowedTypes = [
            'call_customer',
            'schedule_intake',
            'conduct_intake',
        ];

        $data = $request->validate([
            'type'   => ['required', 'string', 'in:' . implode(',', $allowedTypes)],
            'status' => ['required', 'string', 'in:open,done'],
        ]);

        $type = $data['type'];
        $newStatus = $data['status'];

        $titles = [
            'call_customer'   => 'Aanvraag bespreken',
            'schedule_intake' => 'Intakegesprek inplannen',
            'conduct_intake'  => 'Intakegesprek voeren',
        ];

        /**
         * ✅ Task aanmaken als hij nog niet bestaat
         */
        $task = AanvraagTask::firstOrCreate(
            [
                'aanvraag_website_id' => $aanvraag->id,
                'type' => $type,
            ],
            [
                'title'  => $titles[$type] ?? null,
                'status' => 'open',
            ]
        );

        /**
         * ✅ Server-side rules (matcht UI funnel)
         */
        $currentStatus = strtolower((string) ($aanvraag->status ?? 'prospect'));

        $canMarkDone = match ($type) {
            'call_customer'   => $currentStatus === 'contact',
            'schedule_intake' => $currentStatus === 'contact',
            'conduct_intake'  => $currentStatus === 'intake',
            default           => false,
        };

        if ($newStatus === 'done' && !$canMarkDone) {
            return response()->json([
                'message' => 'Deze taak kan in de huidige status nog niet worden afgevinkt.',
            ], 422);
        }

        $task->status = $newStatus;
        $task->save();

        return response()->json([
            'id'     => $task->id,
            'type'   => $task->type,
            'status' => $task->status,
        ]);
    }
}