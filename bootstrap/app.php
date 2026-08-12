<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->redirectUsersTo('/dashboard');

        // Parité avec l'ancien Kernel : throttle:api n'est pas dans le groupe
        // api par défaut de la nouvelle structure.
        $middleware->throttleApi();

        // Sanctum mode SPA : les requêtes du front (SANCTUM_STATEFUL_DOMAINS)
        // sont authentifiées par cookie de session.
        $middleware->statefulApi();
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
