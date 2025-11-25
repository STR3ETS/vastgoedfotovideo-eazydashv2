<?php

namespace App\Http\Controllers;

use App\Models\WorkSession;
use Illuminate\Http\Request;

class WorkSessionController extends Controller
{
    public function clockIn(Request $request)
    {
        $user = $request->user();

        // Als er al een open sessie is, niks doen
        $openSession = $user->workSessions()
            ->whereNull('clock_out_at')
            ->latest('clock_in_at')
            ->first();

        if ($openSession) {
            return back()->with('status', 'Je bent al ingeklokt.');
        }

        WorkSession::create([
            'user_id'     => $user->id,
            'clock_in_at' => now(),
        ]);

        return back()->with('status', 'Succesvol ingeklokt.');
    }

    public function clockOut(Request $request)
    {
        $user = $request->user();

        $openSession = $user->workSessions()
            ->whereNull('clock_out_at')
            ->latest('clock_in_at')
            ->first();

        if (! $openSession) {
            return back()->with('status', 'Je bent niet ingeklokt.');
        }

        // Zet eerst het uitkloktijdstip
        $now = now();
        $openSession->clock_out_at = $now;

        // Bereken verschil in seconden (kan om wat voor reden dan ook negatief worden),
        // dus vang het altijd op met max(0, …)
        $diffSeconds = $openSession->clock_in_at->diffInSeconds($now); // standaard: absolute verschil ≥ 0
        $openSession->worked_seconds = $diffSeconds;              // nooit negatief

        $openSession->save();

        return back()->with('status', 'Succesvol uitgeklokt.');
    }
}
