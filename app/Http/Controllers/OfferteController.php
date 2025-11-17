<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Offerte;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class OfferteController extends Controller
{
    public function klant(string $token)
    {
        /** @var Offerte $offerte */
        $offerte = Offerte::with('project')
            ->where('public_uuid', $token)
            ->firstOrFail();

        return view('hub.projecten.offerte.klant', [
            'offerte' => $offerte,
            'project' => $offerte->project,
        ]);
    }
    
    public function beheerder(string $token)
    {
        $user = auth()->user();

        /** @var Offerte $offerte */
        $offerte = Offerte::with('project')
            ->where('public_uuid', $token)
            ->firstOrFail();

        return view('hub.projecten.offerte.beheerder', [
            'user'    => $user,
            'offerte' => $offerte,
            'project' => $offerte->project,
        ]);
    }

    public function download(string $token)
    {
        /** @var Offerte $offerte */
        $offerte = Offerte::with('project')
            ->where('public_uuid', $token)
            ->firstOrFail();

        $offerteDate   = $offerte->created_at ?? now();
        $offerteNummer = $offerte->number
            ?? ('OF-' . $offerteDate->format('Ym') . str_pad($offerte->id ?? 1, 4, '0', STR_PAD_LEFT));

        $pdf = Pdf::loadView('hub.projecten.offerte.pdf', [
            'offerte'       => $offerte,
            'project'       => $offerte->project,
            'offerteDate'   => $offerteDate,
            'offerteNummer' => $offerteNummer,
        ])->setPaper('a4');

        return $pdf->download($offerteNummer . '.pdf');
    }
}
