<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Configuration\Exceptions;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // A) Globaal op alle web-routes:
        $middleware->web(append: [
            \App\Http\Middleware\SetUserLocale::class,
        ]);

        // alias om route-gewijs te kunnen gebruiken
        $middleware->alias([
            'set.locale' => \App\Http\Middleware\SetUserLocale::class,
            'inbound.mail.secret' => \App\Http\Middleware\VerifyInboundMailSecret::class,
            'company_id' => \App\Http\Middleware\EnsureCompanyId::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
