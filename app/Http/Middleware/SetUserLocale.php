<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;

class SetUserLocale
{
    public function handle($request, Closure $next)
    {
        if ($request->user()?->locale) {
            App::setLocale($request->user()->locale);
        }

        $response = $next($request);

        // Helpt Chrome/Edge/Safari om te snappen in welke taal de pagina staat
        $response->headers->set('Content-Language', App::getLocale());

        return $response;
    }
}