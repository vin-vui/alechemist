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
        // Le site est servi derrière un proxy qui termine TLS (Cloudflare pour
        // le domaine on-forge.com de Forge). Sans cette déclaration, Laravel
        // voit du HTTP et génère des liens http:// dans les mails de
        // réinitialisation. `at: '*'` est le réglage usuel sur Forge : à
        // restreindre aux IP du proxy si le serveur reste joignable en direct.
        $middleware->trustProxies(at: '*');

        $middleware->redirectUsersTo('/');

        // Backend sans vues depuis la phase 4 : il n'y a plus de route nommée
        // `login` vers laquelle rediriger un invité. Sans ça, toute requête non
        // authentifiée qui n'annonce pas Accept: application/json casse en 500
        // (RouteNotFoundException) au lieu de rendre un 401.
        $middleware->redirectGuestsTo(fn () => null);

        // Parité avec l'ancien Kernel : throttle:api n'est pas dans le groupe
        // api par défaut de la nouvelle structure.
        $middleware->throttleApi();

        // Sanctum mode SPA : les requêtes du front (SANCTUM_STATEFUL_DOMAINS)
        // sont authentifiées par cookie de session.
        $middleware->statefulApi();
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Backend sans vues depuis la phase 4 : la route nommée `login` n'existe
        // plus, donc la redirection des invités échouerait en 500. Tout client
        // (navigateur compris) reçoit du JSON, y compris un 401 propre.
        $exceptions->shouldRenderJsonWhen(fn () => true);
    })->create();
