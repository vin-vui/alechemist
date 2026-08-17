# Déploiement phase 4 (cumul phases 1 à 4)

## Prérequis serveur
- PHP >= 8.5 + extensions (bcmath, sqlite3/pdo selon prod) AVANT tout déploiement
- Node 20+ uniquement sur la machine de build (le serveur ne sert que du statique)

## Backend Laravel
1. `composer install --no-dev`
2. `.env` prod :
   - `APP_URL=https://<domaine>`
   - `FRONTEND_URL=https://<domaine>` (same-origin)
   - `SANCTUM_STATEFUL_DOMAINS=<domaine>`
   - `SESSION_SECURE_COOKIE=true`
   - `SESSION_DOMAIN` : laisser vide (same-origin)
3. `php artisan migrate --force`
4. `php artisan optimize:clear` puis `php artisan config:cache && php artisan route:cache`
5. Purger `public/files/` (reliquat phase 1, si pas déjà fait)
6. Le passage des sessions en json (phase 3) déconnecte tous les utilisateurs une fois (re-login)

## Frontend Nuxt
1. `cd frontend && NUXT_PUBLIC_SANCTUM_BASE_URL=https://<domaine> npm ci && npm run generate`
2. Déployer `frontend/.output/public/` comme racine web statique

## Reverse proxy (gabarit nginx)

Decision user 2026-08-13 : routage PAR METHODE pour les URIs partagees. /login, /register,
/forgot-password, /reset-password, /two-factor-challenge sont a la fois des pages Nuxt (GET,
navigation) et des endpoints Fortify (POST). Un GET doit servir la SPA, tout le reste part
vers Laravel. Sans cela, ouvrir /login dans le navigateur donnerait un 404 apres decommission.

    server {
        server_name <domaine>;
        root /var/www/alechemist/frontend-dist;   # contenu de .output/public
        index index.html;
        client_max_body_size 4m;   # import BeerXML jusqu'a 2 Mo (defaut nginx = 1 Mo -> 413)

        # 100% Laravel, toutes methodes : API, cookie CSRF, endpoints /user/*, logout,
        # sonde /up (health check de deploiement Forge : sans elle ici, la SPA
        # repondrait a sa place et la sonde ne testerait plus PHP)
        # try_files $uri @laravel (et non /index.php?$query_string) : une redirection
        # interne relance la recherche de location au niveau serveur, donc un location
        # ~ \.php$ IMBRIQUE ici ne serait jamais consulte (on retomberait sur location /
        # et l'API renverrait du HTML). Un named location (@laravel) ne repasse pas par
        # cette recherche : c'est le seul moyen fiable de forcer php-fpm ici.
        location ~ ^/(api|sanctum|user|logout|up)(/|$) {
            root /var/www/alechemist/public;      # public/ du repo Laravel
            try_files $uri @laravel;
        }

        # URIs partagees : GET = page Nuxt (shell SPA), autres methodes = Fortify (Laravel).
        # Pattern nginx standard : on ne peut pas brancher sur la methode dans un location,
        # d'ou le return 418 intercepte par error_page vers un named location.
        location ~ ^/(login|register|forgot-password|reset-password|two-factor-challenge)(/|$) {
            error_page 418 = @laravel;
            if ($request_method != GET) { return 418; }
            try_files /index.html =404;
        }

        location @laravel {
            root /var/www/alechemist/public;
            include fastcgi_params;
            fastcgi_pass unix:/run/php/php8.5-fpm.sock;
            fastcgi_param SCRIPT_FILENAME $realpath_root/index.php;
            fastcgi_param REQUEST_URI $request_uri;
        }

        # SPA : tout le reste retombe sur index.html
        location / {
            try_files $uri $uri/ /index.html;
        }
    }

## Vérifications post-bascule
- Login (avec et sans 2FA), refresh de page authentifiée (cookie stateful OK)
- `curl -s https://<domaine>/up` doit renvoyer la page de santé de Laravel (200), et non le
  shell HTML de la SPA : c'est la sonde du health check de déploiement Forge
- `curl -i https://<domaine>/api/user` doit renvoyer un 401 JSON, PAS du HTML (sinon la SPA
  intercepte l'API : voir la note sur les named locations ci-dessus)
- Email de reset : le lien pointe vers https://<domaine>/reset-password/...
- Import BeerXML, création de brassin, chrono boil sans requêtes répétées
- PWA installable (manifest + SW servis)
- Si l'affichage reste figé après bascule (assets obsolètes), vider le cache du navigateur et
  désenregistrer l'ancien service worker (DevTools > Application > Service Workers)
- Effet de bord assumé dès la bascule du reverse proxy : l'ancien front Livewire devient
  inatteignable (les routes web ne sont plus servies par ce gabarit), même en cas de rollback
  applicatif côté Laravel
