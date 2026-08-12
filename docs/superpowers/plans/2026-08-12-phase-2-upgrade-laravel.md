# Phase 2 — Upgrade Laravel 11 → 12 → 13 : plan d'implémentation

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Amener Alechemist de Laravel 11.7 / PHP ^8.1 / PHPUnit 10 à Laravel 13 / PHP ^8.3 / PHPUnit 12, avec adoption de la nouvelle structure `bootstrap/app.php`, sans régression (suite verte à chaque tâche).

**Architecture :** upgrade incrémental en petits paliers indépendamment testables : CI d'abord, puis PHP 8.3, puis mise à jour intra-majeures sur L11, puis migration de structure (kernels → ApplicationBuilder) sur L11, puis sauts L12 et L13, puis normalisation du disque `public`. Le schéma de base de données est inchangé.

**Tech Stack :** Laravel 13.x, PHP 8.3, PHPUnit 12, Jetstream 5.5, Livewire 3.8 (PAS 4.x), Sanctum 4.3, Fortify 1.x, Docker (aucun PHP/Composer local).

## Point de décision de la roadmap : RÉSOLU

La roadmap prévoyait un arrêt à L12 si Jetstream bloquait L13. Vérifié le 2026-08-12 sur Packagist :
- `laravel/jetstream` v5.5.3 (2026-05-19) requiert `illuminate/support ^11.0|^12.0|^13.0` → **L13 supporté, pas de repli sur L12**.
- Compat L13 confirmée aussi pour : `livewire/livewire` ≥ 3.8.4, `laravel/sanctum` ≥ 4.3.0, `nunomaduro/collision` 8.9.5 (conflict `laravel/framework <11.48.0 || >=14.0.0`, `phpunit <11.5.50 || >=14.0.0`), `laravel-lang/lang` 15.x (conflict uniquement `laravel/framework <11.0.7`, pas de borne haute), `spatie/laravel-ignition` 2.12 (`^11|^12|^13`), `laravel/sail` 1.66, `laravel/tinker` **3.0 requis** (2.x s'arrête à L12).
- `laravel/framework` v13.25.0 requiert **`php ^8.3`**.

## Global Constraints

- **Aucun PHP/Composer local — tout via Docker.** Commandes canoniques (à utiliser verbatim, depuis la racine du repo) :
  - Composer : `docker run --rm -u $(id -u):$(id -g) -e COMPOSER_HOME=/tmp/composer -v "$PWD":/app -w /app composer:2.7 composer <args> --ignore-platform-req=ext-bcmath`
    (l'image `composer:2` embarque PHP 8.5 et casse l'install → **toujours** `composer:2.7` ; le flag bcmath est requis car l'image n'a pas l'extension)
  - PHP / artisan : `docker run --rm -u $(id -u):$(id -g) -v "$PWD":/app -w /app php:8.3-cli php <args>`
  - Tests : `docker run --rm -u $(id -u):$(id -g) -v "$PWD":/app -w /app php:8.3-cli vendor/bin/phpunit`
- **Baseline de la suite : 47 tests, 0 failure, 7 skipped** (features Fortify désactivées → skips attendus). Toute tâche se termine par cette suite verte (même comptage sauf mention) + un commit.
- `livewire/livewire` reste contraint `^3.0` — **ne jamais passer à Livewire 4.x** (le frontend part en phase 4).
- Schéma DB inchangé ; tests en SQLite `:memory:` (déjà configuré dans `phpunit.xml`).
- Piège connu (mémoire projet) : la relation `BrewingStep::recipe()` est cassée (la FK est `brewing_id`) — ne jamais utiliser `->for()` sur `BrewingStep` dans un test.
- En test Livewire 3, les `abort()` ne bullent pas — utiliser `->assertStatus(403)` (aucun nouveau test prévu ici, mais règle valable si on en ajoute).
- Messages de commit : préfixes `ci:` / `chore:` / `refactor:` / `docs:` comme dans l'historique, en français.

## Notes de déploiement (à exécuter côté serveur, hors scope du code)

- **Prérequis : PHP ≥ 8.3 sur le serveur de prod** avant de déployer cette branche (composer.json passera à `php: ^8.3`).
- Au déploiement : `composer install --no-dev`, puis `php artisan optimize:clear` (les caches `bootstrap/cache/*.php` de l'ancienne structure sont incompatibles).
- Toujours en attente depuis la phase 1 : purge de `public/files/` en prod.
- Les sessions actives sont préservées : `config/session.php` garde `serialization => php` (ajouté explicitement en tâche 6) et le nom du cookie ne change pas.

---

### Task 1 : Branche de travail + CI GitHub Actions

La roadmap demandait une CI « dès la fin de phase 1 » ; elle n'existe pas encore. Le remote `origin` est **GitHub** (`github.com/vin-vui/alechemist`), pas GitLab — la roadmap mentionnait GitLab CI par erreur. On met donc en place GitHub Actions. Elle validera chaque palier de l'upgrade au push.

**Files:**
- Create: `.github/workflows/ci.yml`

**Interfaces:**
- Consumes: `phpunit.xml` existant (SQLite `:memory:`), `.env.example`.
- Produces: workflow nommé `tests`, job `phpunit`, déclenché sur `push` et `pull_request`. Les tâches suivantes n'y touchent plus.

- [ ] **Step 1 : Créer la branche**

```bash
git checkout -b phase-2-upgrade-laravel
```

- [ ] **Step 2 : Écrire `.github/workflows/ci.yml`**

```yaml
name: tests

on:
  push:
  pull_request:

jobs:
  phpunit:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4

      - uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'
          extensions: bcmath, sqlite3, pdo_sqlite
          coverage: none

      - name: Install dependencies
        run: composer install --prefer-dist --no-progress --no-interaction

      - name: Prepare environment
        run: |
          cp .env.example .env
          php artisan key:generate

      - name: Run tests
        run: vendor/bin/phpunit
```

- [ ] **Step 3 : Valider la syntaxe YAML**

Run: `python3 -c "import yaml; yaml.safe_load(open('.github/workflows/ci.yml')); print('YAML OK')"`
Expected: `YAML OK`

- [ ] **Step 4 : Vérifier que la suite locale est verte (baseline avant tout changement)**

Run: `docker run --rm -u $(id -u):$(id -g) -v "$PWD":/app -w /app php:8.3-cli vendor/bin/phpunit`
Expected: `OK, but some tests were skipped! Tests: 47 ... Skipped: 7` (0 failure)

- [ ] **Step 5 : Commit**

```bash
git add .github/workflows/ci.yml
git commit -m "ci: pipeline GitHub Actions (phpunit sur PHP 8.3)"
```

Note : la CI ne tournera réellement qu'au premier push de la branche — la validation locale des tâches suivantes reste la référence.

---

### Task 2 : Exiger PHP ^8.3

Les tests tournent déjà sur `php:8.3-cli` ; on aligne la contrainte composer. `laravel/framework` 13 exigera `php ^8.3` de toute façon.

**Files:**
- Modify: `composer.json:8` (`"php": "^8.1"`)

**Interfaces:**
- Consumes: rien.
- Produces: `composer.json` avec `"php": "^8.3"` ; `composer.lock` dont le hash est recalculé (versions inchangées). Les tâches 3, 5, 6 partent de là.

- [ ] **Step 1 : Éditer `composer.json`**

Remplacer dans le bloc `require` :

```json
        "php": "^8.1",
```

par :

```json
        "php": "^8.3",
```

- [ ] **Step 2 : Recalculer le lock sans changer les versions**

Run: `docker run --rm -u $(id -u):$(id -g) -e COMPOSER_HOME=/tmp/composer -v "$PWD":/app -w /app composer:2.7 composer update --lock --ignore-platform-req=ext-bcmath`
Expected: `Writing lock file` sans installation de nouvelles versions.

- [ ] **Step 3 : Suite verte**

Run: `docker run --rm -u $(id -u):$(id -g) -v "$PWD":/app -w /app php:8.3-cli vendor/bin/phpunit`
Expected: 47 tests, 0 failure, 7 skipped.

- [ ] **Step 4 : Commit**

```bash
git add composer.json composer.lock
git commit -m "chore: exige PHP 8.3 minimum"
```

---

### Task 3 : Mise à jour intra-majeures sur Laravel 11

Avant les sauts de majeure, on met tout à jour **dans les contraintes actuelles** : framework 11.7 → 11.x dernier (collision 8.9.x exigera ≥ 11.48), Jetstream 5.1 → 5.5, Livewire 3.4 → 3.8, Fortify, Sanctum 4.0 → 4.3, etc. Ça isole les régressions « mineures » (surtout Livewire 3.4→3.8 et Jetstream 5.1→5.5) des régressions de majeure.

**Files:**
- Modify: `composer.lock` (seul fichier modifié — `composer.json` inchangé)

**Interfaces:**
- Consumes: `composer.json` de la tâche 2.
- Produces: lock avec `laravel/framework` 11.x ≥ 11.48, `laravel/jetstream` 5.5.x, `livewire/livewire` 3.8.x, `laravel/sanctum` 4.3.x. Point de départ des tâches 4 et 5.

- [ ] **Step 1 : Update complet dans les contraintes**

Run: `docker run --rm -u $(id -u):$(id -g) -e COMPOSER_HOME=/tmp/composer -v "$PWD":/app -w /app composer:2.7 composer update --ignore-platform-req=ext-bcmath`
Expected: mise à jour d'un paquet de dépendances, sans erreur de résolution.

- [ ] **Step 2 : Vérifier les versions clés obtenues**

Run: `docker run --rm -u $(id -u):$(id -g) -e COMPOSER_HOME=/tmp/composer -v "$PWD":/app -w /app composer:2.7 composer show laravel/framework laravel/jetstream livewire/livewire laravel/sanctum laravel/fortify --ignore-platform-req=ext-bcmath | grep -E '^(name|versions)'`
Expected: framework `v11.4x` (≥ 11.48), jetstream `v5.5.x`, livewire `v3.8.x` (PAS v4), sanctum `v4.3.x`.

- [ ] **Step 3 : Suite verte**

Run: `docker run --rm -u $(id -u):$(id -g) -v "$PWD":/app -w /app php:8.3-cli vendor/bin/phpunit`
Expected: 47 tests, 0 failure, 7 skipped. Si un test Livewire/Jetstream casse ici, c'est le saut 3.4→3.8 / 5.1→5.5 : diagnostiquer avant de continuer (ne pas empiler avec les tâches suivantes).

- [ ] **Step 4 : Smoke test de boot**

Run: `docker run --rm -u $(id -u):$(id -g) -v "$PWD":/app -w /app php:8.3-cli php artisan route:list --except-vendor`
Expected: la liste des 12 routes web s'affiche sans exception.

- [ ] **Step 5 : Commit**

```bash
git add composer.lock
git commit -m "chore: deps à jour dans les majeures actuelles (framework 11.x, jetstream 5.5, livewire 3.8)"
```

---

### Task 4 : Adoption de la nouvelle structure bootstrap (sur Laravel 11)

Suppression des kernels HTTP/Console, du Handler et des 9 middleware stock, remplacés par `bootstrap/app.php` style ApplicationBuilder. Tout l'existant est stock **sauf** : le rate limiter `api` et les routes (dans `RouteServiceProvider`), la redirection des connectés vers `/dashboard` (`RedirectIfAuthenticated` + constante `HOME`). `BroadcastServiceProvider` est commenté dans `config/app.php` (mort) → supprimé avec `routes/channels.php`.

**Files:**
- Rewrite: `bootstrap/app.php`, `artisan`, `public/index.php`
- Create: `bootstrap/providers.php`
- Modify: `app/Providers/AppServiceProvider.php`, `config/app.php` (retrait `providers`/`aliases`), `config/fortify.php:3,77`, `tests/TestCase.php`, `tests/Feature/AuthenticationTest.php`, `tests/Feature/EmailVerificationTest.php`, `tests/Feature/RegistrationTest.php`
- Delete: `app/Http/Kernel.php`, `app/Console/Kernel.php`, `app/Exceptions/Handler.php`, `app/Http/Middleware/` (9 fichiers), `app/Providers/RouteServiceProvider.php`, `app/Providers/BroadcastServiceProvider.php`, `routes/channels.php`, `tests/CreatesApplication.php`

**Interfaces:**
- Consumes: framework 11.x ≥ 11.48 (tâche 3) — `Application::configure()`, `handleRequest()`, `handleCommand()` disponibles.
- Produces: `bootstrap/app.php` retournant l'app configurée (contrat pour `artisan`, `public/index.php` et le `TestCase` du framework) ; `bootstrap/providers.php` listant les 5 providers applicatifs ; rate limiter nommé `api` défini dans `AppServiceProvider::boot()` + `throttleApi()` activé dans le bootstrap (le groupe `api` de la nouvelle structure n'inclut pas `throttle:api` par défaut, vérifié dans la source du framework — nécessaire pour l'API de la phase 3) ; redirection utilisateurs connectés → `/dashboard` via `redirectUsersTo`.

Équivalences vérifiées (aucune perte de comportement) : `Authenticate::redirectTo` → défaut framework (route `login`) ; `TrimStrings::$except` (current_password/password/password_confirmation) → défaut framework identique ; `TrustProxies`/`EncryptCookies`/`VerifyCsrfToken`/`PreventRequestsDuringMaintenance`/`ValidateSignature` → vides = défauts framework ; `TrustHosts` → n'était pas enregistré ; `Handler::$dontFlash` → défaut framework identique ; `EventServiceProvider` (listener Registered) conservé tel quel.

- [ ] **Step 1 : Réécrire `bootstrap/app.php` (contenu intégral)**

```php
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

        // Parité avec l'ancien Kernel : throttle:api n'est PAS dans le groupe
        // api par défaut de la nouvelle structure, il faut l'activer.
        $middleware->throttleApi();
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
```

- [ ] **Step 2 : Créer `bootstrap/providers.php` (contenu intégral)**

```php
<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\AuthServiceProvider::class,
    App\Providers\EventServiceProvider::class,
    App\Providers\FortifyServiceProvider::class,
    App\Providers\JetstreamServiceProvider::class,
];
```

- [ ] **Step 3 : Déplacer le rate limiter `api` dans `AppServiceProvider` (contenu intégral du fichier)**

```php
<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}
```

- [ ] **Step 4 : Réécrire `artisan` (contenu intégral)**

```php
#!/usr/bin/env php
<?php

use Symfony\Component\Console\Input\ArgvInput;

define('LARAVEL_START', microtime(true));

// Register the Composer autoloader...
require __DIR__.'/vendor/autoload.php';

// Bootstrap Laravel and handle the command...
$status = (require_once __DIR__.'/bootstrap/app.php')
    ->handleCommand(new ArgvInput);

exit($status);
```

- [ ] **Step 5 : Réécrire `public/index.php` (contenu intégral)**

```php
<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
(require_once __DIR__.'/../bootstrap/app.php')
    ->handleRequest(Request::capture());
```

- [ ] **Step 6 : `config/app.php` — supprimer les clés `providers` et `aliases`**

Supprimer les deux `use` en tête de fichier (`use Illuminate\Support\Facades\Facade;` et `use Illuminate\Support\ServiceProvider;`), le bloc `'providers' => ServiceProvider::defaultProviders()->merge([...])->toArray(),` (avec son commentaire d'en-tête) et le bloc `'aliases' => Facade::defaultAliases()->merge([...])->toArray(),` (avec son commentaire). Le reste du fichier est inchangé.

- [ ] **Step 7 : `config/fortify.php` — remplacer la constante HOME**

Ligne 3 : supprimer `use App\Providers\RouteServiceProvider;`.
Ligne 77 : remplacer `'home' => RouteServiceProvider::HOME,` par `'home' => '/dashboard',`.

- [ ] **Step 8 : Mettre à jour les 3 tests qui référencent HOME**

Dans `tests/Feature/AuthenticationTest.php`, `tests/Feature/EmailVerificationTest.php`, `tests/Feature/RegistrationTest.php` :
- supprimer la ligne `use App\Providers\RouteServiceProvider;`
- remplacer `RouteServiceProvider::HOME.'?verified=1'` par `'/dashboard?verified=1'` (EmailVerificationTest)
- remplacer `RouteServiceProvider::HOME` par `'/dashboard'` (les deux autres)

- [ ] **Step 9 : `tests/TestCase.php` — retirer le trait (contenu intégral)**

```php
<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }
}
```

(En L11+, `Illuminate\Foundation\Testing\TestCase` crée l'application tout seul via `bootstrap/app.php` — le trait custom devient inutile.)

- [ ] **Step 10 : Supprimer les fichiers obsolètes**

```bash
git rm app/Http/Kernel.php app/Console/Kernel.php app/Exceptions/Handler.php \
  app/Http/Middleware/Authenticate.php app/Http/Middleware/EncryptCookies.php \
  app/Http/Middleware/PreventRequestsDuringMaintenance.php app/Http/Middleware/RedirectIfAuthenticated.php \
  app/Http/Middleware/TrimStrings.php app/Http/Middleware/TrustHosts.php app/Http/Middleware/TrustProxies.php \
  app/Http/Middleware/ValidateSignature.php app/Http/Middleware/VerifyCsrfToken.php \
  app/Providers/RouteServiceProvider.php app/Providers/BroadcastServiceProvider.php \
  routes/channels.php tests/CreatesApplication.php
```

- [ ] **Step 11 : Purger les caches générés + autoload**

```bash
rm -f bootstrap/cache/*.php
docker run --rm -u $(id -u):$(id -g) -e COMPOSER_HOME=/tmp/composer -v "$PWD":/app -w /app composer:2.7 composer dump-autoload --ignore-platform-req=ext-bcmath
```

- [ ] **Step 12 : Vérifier qu'aucune référence morte ne subsiste**

Run: `grep -rn "App\\\\Http\\\\Kernel\|App\\\\Console\\\\Kernel\|App\\\\Exceptions\|App\\\\Http\\\\Middleware\|RouteServiceProvider\|BroadcastServiceProvider\|CreatesApplication" app bootstrap config routes tests --include='*.php'`
Expected: aucun résultat.

- [ ] **Step 13 : Smoke tests de boot (HTTP + console + commande custom)**

```bash
docker run --rm -u $(id -u):$(id -g) -v "$PWD":/app -w /app php:8.3-cli php artisan --version
docker run --rm -u $(id -u):$(id -g) -v "$PWD":/app -w /app php:8.3-cli php artisan route:list --except-vendor
docker run --rm -u $(id -u):$(id -g) -v "$PWD":/app -w /app php:8.3-cli php artisan list
```

Expected: version `Laravel Framework 11.x`, les routes web listées, et la commande custom (`AddLanguageKeys`, dossier `app/Console/Commands` auto-découvert) visible dans `php artisan list`.

- [ ] **Step 14 : Suite verte**

Run: `docker run --rm -u $(id -u):$(id -g) -v "$PWD":/app -w /app php:8.3-cli vendor/bin/phpunit`
Expected: 47 tests, 0 failure, 7 skipped.

- [ ] **Step 15 : Commit**

```bash
git add -A
git commit -m "refactor: adoption de la structure bootstrap/app.php (suppression kernels, handler et middleware stock)"
```

---

### Task 5 : Laravel 11 → 12 + PHPUnit 11

D'après le guide officiel, quasi aucun breaking change ne touche cette app : Carbon est déjà en 3.x (lock : `nesbot/carbon 3.3.1`), pas de `HasUuids`, pas de `Storage::`/disque `local` utilisé, pas de rule `image`, pas de `Concurrency`/`mergeIfMissing`. Reste le bump des deps.

**Files:**
- Modify: `composer.json` (framework, phpunit), `composer.lock`, éventuellement `phpunit.xml` (migration de schéma)

**Interfaces:**
- Consumes: structure bootstrap de la tâche 4.
- Produces: `laravel/framework ^12.0`, `phpunit/phpunit ^11.5.50` installés ; point de départ de la tâche 6.

- [ ] **Step 1 : Éditer `composer.json`**

Dans `require` : `"laravel/framework": "^11.0"` → `"laravel/framework": "^12.0"`.
Dans `require-dev` : `"phpunit/phpunit": "^10.1"` → `"phpunit/phpunit": "^11.5.50"` (plancher aligné sur le conflict de collision 8.9.x, qui exige phpunit ≥ 11.5.50).

- [ ] **Step 2 : Update**

Run: `docker run --rm -u $(id -u):$(id -g) -e COMPOSER_HOME=/tmp/composer -v "$PWD":/app -w /app composer:2.7 composer update --ignore-platform-req=ext-bcmath`
Expected: résolution sans conflit ; framework v12.x, phpunit v11.5.x, collision 8.9.x.

- [ ] **Step 3 : Migrer `phpunit.xml` si le schéma est signalé obsolète**

Run: `docker run --rm -u $(id -u):$(id -g) -v "$PWD":/app -w /app php:8.3-cli vendor/bin/phpunit --version` puis lancer la suite une première fois. Si PHPUnit affiche un warning « XML configuration validates against a deprecated schema », exécuter :
`docker run --rm -u $(id -u):$(id -g) -v "$PWD":/app -w /app php:8.3-cli vendor/bin/phpunit --migrate-configuration`
puis vérifier le diff de `phpunit.xml` (les `<env>` doivent être conservés). Sinon, ne rien faire.

- [ ] **Step 4 : Suite verte + version**

```bash
docker run --rm -u $(id -u):$(id -g) -v "$PWD":/app -w /app php:8.3-cli vendor/bin/phpunit
docker run --rm -u $(id -u):$(id -g) -v "$PWD":/app -w /app php:8.3-cli php artisan --version
```

Expected: 47 tests, 0 failure, 7 skipped ; `Laravel Framework 12.x`.

- [ ] **Step 5 : Commit**

```bash
git add composer.json composer.lock phpunit.xml
git commit -m "chore: upgrade Laravel 12 + PHPUnit 11"
```

(Ne committer `phpunit.xml` que s'il a été modifié au step 3.)

---

### Task 6 : Laravel 12 → 13 + PHPUnit 12

Breaking changes L13 croisés avec l'app : `VerifyCsrfToken` → `PreventRequestForgery` (aucune référence custom ne subsiste après la tâche 4 — vérifié) ; sujets de mail par défaut (aucun test n'asserte de sujet) ; polyfill `array_first`/`array_last` (aucun usage) ; préfixes cache/cookies (définis dans les configs publiées → sans effet). Seul point actif : **figer `serialization => php` dans `config/session.php`** pour préserver les sessions actives en prod (le passage à `json` se fera en phase 3/4, quand la bascule d'auth invalidera les sessions de toute façon).

**Files:**
- Modify: `composer.json` (framework, phpunit, tinker), `composer.lock`, `config/session.php`, éventuellement `phpunit.xml`

**Interfaces:**
- Consumes: L12 vert de la tâche 5.
- Produces: `laravel/framework ^13.0`, `phpunit/phpunit ^12.0`, `laravel/tinker ^3.0` installés — l'état cible de la phase (les tâches 7-8 ne touchent plus aux deps).

- [ ] **Step 1 : Éditer `composer.json`**

Dans `require` : `"laravel/framework": "^12.0"` → `"laravel/framework": "^13.0"` et `"laravel/tinker": "^2.8"` → `"laravel/tinker": "^3.0"`.
Dans `require-dev` : `"phpunit/phpunit": "^11.5.50"` → `"phpunit/phpunit": "^12.0"`.

- [ ] **Step 2 : Update**

Run: `docker run --rm -u $(id -u):$(id -g) -e COMPOSER_HOME=/tmp/composer -v "$PWD":/app -w /app composer:2.7 composer update --ignore-platform-req=ext-bcmath`
Expected: résolution sans conflit ; framework v13.x, phpunit v12.x, tinker v3.0.x, jetstream v5.5.x, collision v8.9.5+, sanctum v4.3.x, livewire v3.8.x (toujours pas de 4.x).

- [ ] **Step 3 : Figer la sérialisation des sessions au comportement actuel**

Dans `config/session.php`, juste après la clé `'expire_on_close'`, ajouter :

```php
    /*
    |--------------------------------------------------------------------------
    | Session Serialization
    |--------------------------------------------------------------------------
    |
    | Figé à "php" pour préserver les sessions actives lors de l'upgrade L13
    | (le défaut du skeleton 13 est passé à "json"). À passer à "json" en
    | phase 3/4, quand la bascule d'authentification invalidera de toute
    | façon les sessions.
    |
    */

    'serialization' => 'php',
```

- [ ] **Step 4 : Migrer `phpunit.xml` si le schéma est signalé obsolète**

Même procédure qu'en tâche 5 step 3 (`--migrate-configuration` uniquement si PHPUnit s'en plaint, en conservant les `<env>`).

- [ ] **Step 5 : Suite verte + version**

```bash
docker run --rm -u $(id -u):$(id -g) -v "$PWD":/app -w /app php:8.3-cli vendor/bin/phpunit
docker run --rm -u $(id -u):$(id -g) -v "$PWD":/app -w /app php:8.3-cli php artisan --version
docker run --rm -u $(id -u):$(id -g) -v "$PWD":/app -w /app php:8.3-cli php artisan route:list --except-vendor
```

Expected: 47 tests, 0 failure, 7 skipped ; `Laravel Framework 13.x` ; routes listées sans exception.

- [ ] **Step 6 : Commit**

```bash
git add composer.json composer.lock config/session.php phpunit.xml
git commit -m "chore: upgrade Laravel 13 + PHPUnit 12 (serialization session figée à php)"
```

(Ne committer `phpunit.xml` que s'il a été modifié au step 4.)

---

### Task 7 : Normalisation du disque `public`

Découverte de la phase 1 : `config/filesystems.php` route le disque `public` vers `public_path('files')` (hack « pour un test rapide » du code d'origine). Plus aucun consommateur depuis la phase 1 (vérifié : aucun `Storage::` dans `app/`, photos de profil Jetstream désactivées). On restaure la définition standard — la définition propre était d'ailleurs déjà présente en commentaire.

**Files:**
- Modify: `config/filesystems.php:31-52`

**Interfaces:**
- Consumes: rien (disque sans consommateur).
- Produces: disque `public` standard (`storage/app/public`, URL `/storage`) — prêt pour un éventuel usage en phase 3/4 via `php artisan storage:link`.

- [ ] **Step 1 : Remplacer la définition du disque `public`**

Dans `config/filesystems.php`, remplacer le bloc :

```php
        // 'public' => [
        //     'driver' => 'local',
        //     'root' => storage_path('app/public'),
        //     'url' => env('APP_URL').'/storage',
        //     'visibility' => 'public',
        //     'throw' => false,
        // ],

        'public' => [
            'driver' => 'local',
            'root' => public_path('files'),  // Utiliser public_path pour un test rapide
            'url' => env('APP_URL').'/files',
            'visibility' => 'public',
        ],
```

par :

```php
        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
            'throw' => false,
        ],
```

- [ ] **Step 2 : Vérifier l'absence de consommateur**

Run: `grep -rn "disk('public')\|disk(\"public\")\|Storage::" app resources routes --include='*.php' --include='*.blade.php'`
Expected: aucun résultat.

- [ ] **Step 3 : Suite verte**

Run: `docker run --rm -u $(id -u):$(id -g) -v "$PWD":/app -w /app php:8.3-cli vendor/bin/phpunit`
Expected: 47 tests, 0 failure, 7 skipped.

- [ ] **Step 4 : Commit**

```bash
git add config/filesystems.php
git commit -m "chore: disque public normalisé vers storage/app/public (fin du hack public/files)"
```

---

### Task 8 : Documentation de fin de phase

**Files:**
- Modify: `docs/superpowers/plans/2026-08-11-migration-roadmap.md` (section Phase 2)

**Interfaces:**
- Consumes: résultats des tâches 1-7.
- Produces: roadmap à jour — la phase 3 démarrera de là.

- [ ] **Step 1 : Mettre à jour la roadmap**

Dans la section « Phase 2 » de `docs/superpowers/plans/2026-08-11-migration-roadmap.md`, ajouter à la fin :

```markdown
**✅ Réalisée le 2026-08-12** (plan détaillé : `2026-08-12-phase-2-upgrade-laravel.md`). Le point de décision est tranché : Jetstream 5.5 supporte Laravel 13 — l'app est directement en **Laravel 13 / PHP ^8.3 / PHPUnit 12**, nouvelle structure `bootstrap/app.php` adoptée (kernels, Handler et middleware stock supprimés), CI GitHub Actions en place, disque `public` normalisé. **Actions de déploiement requises : PHP ≥ 8.3 sur le serveur AVANT déploiement**, puis `composer install --no-dev` et `php artisan optimize:clear`. `config/session.php` fige `serialization => php` (sessions préservées) — à passer à `json` en phase 3/4. La purge de `public/files/` en prod (phase 1) reste à faire si pas déjà faite.
```

Et dans la ligne du tableau des risques « Compat Jetstream ↔ Laravel 13 », remplacer la mitigation par : `Résolu le 2026-08-12 : Jetstream 5.5.3 supporte L13`.

- [ ] **Step 2 : Commit**

```bash
git add docs/superpowers/plans/2026-08-11-migration-roadmap.md docs/superpowers/plans/2026-08-12-phase-2-upgrade-laravel.md
git commit -m "docs: phase 2 réalisée (Laravel 13, structure bootstrap, CI)"
```

---

## Risques résiduels et hors-scope

- **Vues Jetstream publiées** (`resources/views/api`, `auth`, `profile`, …) : l'upgrade 5.1→5.5 ne les met pas à jour ; d'éventuels correctifs upstream ne sont pas repris. Acceptable — tout le frontend Blade disparaît en phase 4.
- **`cache.serializable_classes`** : durcissement du skeleton L13 non repris (clé absente = comportement historique). À traiter en phase 3 avec la revue config de l'API.
- **Sync complète des configs skeleton 13** (env vars renommées, nouveaux defaults) : non nécessaire au fonctionnement, volontairement hors-scope pour garder le diff lisible. La phase 3 retouchera `cors.php`, `sanctum.php` et `session.php` de toute façon.
- **CI GitLab vs GitHub** : la roadmap disait « GitLab CI » mais `origin` est GitHub → GitHub Actions. Si un miroir GitLab existe par ailleurs, un `.gitlab-ci.yml` équivalent pourra être ajouté plus tard.
