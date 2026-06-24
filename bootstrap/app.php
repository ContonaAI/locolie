<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Trust the ngrok (or any) proxy so https URLs are generated correctly when tunneled.
        $middleware->trustProxies(at: '*');

        $middleware->alias([
            'portal' => \App\Http\Middleware\PortalAuth::class,
            'sync.token' => \App\Http\Middleware\VerifySyncToken::class,
        ]);

        // Unauthenticated business-guard requests go to the business login.
        $middleware->redirectGuestsTo(fn () => route('business.login'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
