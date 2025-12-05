<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifyInboundMailSecret
{
    public function handle(Request $request, Closure $next)
    {
        /**
         * Microsoft Graph handshake:
         * Als validationToken aanwezig is, moeten we direct doorlaten.
         */
        if ($request->query('validationToken')) {
            return $next($request);
        }

        $expected = (string) config('services.m365.webhook_secret');

        // Als je ook je oude inbound provider wilt blijven ondersteunen,
        // kun je hier eventueel ook een fallback secret uit env pakken.
        if ($expected === '') {
            $expected = (string) env('INBOUND_MAIL_SECRET', '');
        }

        // Geen secret ingesteld? Dan niet blokkeren.
        if ($expected === '') {
            return $next($request);
        }

        $given =
            $request->header('X-Inbound-Secret')
            ?? $request->header('X-M365-Secret')
            ?? $request->query('secret');

        if (!$given || !hash_equals($expected, (string) $given)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}
