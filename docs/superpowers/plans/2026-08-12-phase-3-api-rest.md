# Phase 3 — API REST + Fortify headless : plan d'implémentation

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Exposer une API REST complète (recettes, brassins, étapes, import BeerXML, dashboard) authentifiée en Sanctum SPA + Fortify headless, couvrant tous les cas d'usage des 12 routes web Livewire, testée endpoint par endpoint — les deux frontends coexistent jusqu'à la phase 4.

**Architecture:** Controllers API sous `app/Http/Controllers/Api/` + Form Requests + API Resources (enveloppe `data` par défaut de Laravel), autorisés exclusivement par les policies de la phase 1 (`RecipePolicy`, `BrewingPolicy`). L'import BeerXML est extrait dans un service `App\Services\BeerXmlImporter` partagé entre le contrôleur web existant et l'API. Sanctum passe en mode SPA (cookies de session via `statefulApi()` + CORS credentials), Fortify sert les flux auth en JSON (il le fait déjà nativement — on le verrouille par des tests de caractérisation).

**Tech Stack:** Laravel 13 (PHP ^8.5), Sanctum 4 (mode SPA), Fortify via Jetstream 5, PHPUnit 12, SQLite in-memory pour les tests.

## Global Constraints

- `php: ^8.5`, `laravel/framework: ^13.0`, `phpunit/phpunit: ^12.0` — ne toucher à aucun plancher.
- `livewire/livewire` figé `^3.0` (jamais 4.x) et Jetstream reste en place jusqu'à la phase 4.
- **Schéma de base de données inchangé** : aucune nouvelle migration dans cette phase.
- **Les routes web Livewire (`routes/web.php`) restent intactes** — coexistence des deux frontends.
- Autorisation : uniquement les policies phase 1 (`RecipePolicy`, `BrewingPolicy` — abilities `view`/`update`/`delete`, propriété via `recipe.user_id`).
- Pas de PHP local — tout via Docker :
  - Tests : `docker run --rm -u $(id -u):$(id -g) -v "$PWD":/app -w /app php:8.5-cli vendor/bin/phpunit`
  - Composer (si jamais nécessaire — aucune dépendance nouvelle prévue) : image `composer:2` + `--ignore-platform-req=ext-bcmath` + `-e COMPOSER_HOME=/tmp/composer`
- Conventions tests existantes : méthodes `test_en_snake_case()`, trait `RefreshDatabase`, `$this->actingAs($user)` (le guard `web` satisfait `auth:sanctum`), factories existantes (`User`, `Recipe`, `Brewing`, `BrewingStep`, `Step`).
- **Jamais `->for()` sur `BrewingStep`** tant que la tâche 1 n'est pas faite (relation `recipe()` cassée) ; après la tâche 1, la relation s'appelle `brewing()`.
- Commits fréquents, messages en français préfixés (`feat:`, `refactor:`, `test:`, `chore:`, `docs:`). **Ne jamais pousser** — le push de `main` est une décision du user.
- Suite de départ : 47 tests, 0 failure, 7 skips attendus (features Fortify désactivées). Chaque tâche se termine suite verte.

## Contrat API (vue d'ensemble)

Toutes les routes ci-dessous sont sous `auth:sanctum` + throttle `api` (60/min), réponses JSON enveloppées dans `data` :

| Méthode | URI | Rôle (équivalent Livewire) |
|---|---|---|
| GET | `/api/user` | utilisateur courant (navbar / nuxt-auth-sanctum) |
| GET | `/api/dashboard` | `HomeController` (recettes en fermentation + brassins en cours) |
| GET | `/api/recipes` | `RecipesController` |
| GET | `/api/recipes/{recipe}` | `RecipeDetailsController` (avec `steps` + `brewings_count`) |
| PATCH | `/api/recipes/{recipe}` | `RecipeDetailsController::edit` (`before_boil_density`) |
| DELETE | `/api/recipes/{recipe}` | `RecipeDetailsController::delete` |
| POST | `/api/recipes/import` | `FileController::store` (upload BeerXML) |
| GET | `/api/recipes/{recipe}/brewings` | `BrewingController` (liste) |
| POST | `/api/recipes/{recipe}/brewings` | `CreateBrewingController::store` |
| GET | `/api/brewings/{brewing}` | pages d'étapes (brassin + toutes ses `steps`) |
| PATCH | `/api/brewings/{brewing}` | `next()`/`modifyBoilTime()`/`NoteController::update` |
| POST | `/api/brewings/{brewing}/start-boil` | `BoilController::startChrono` |
| DELETE | `/api/brewings/{brewing}` | `BrewingController::delete` / `CompletedController::delete` |
| PATCH | `/api/brewing-steps/{brewingStep}` | `statusChange()` (cocher/décocher une étape) |

Sémantique de `PATCH /api/brewings/{brewing}` : champs optionnels `note`, `boil_time`, `current_step`. Les transitions posent les timestamps côté serveur (parité web) : passage à `ferment` → `ferment_start = now()` ; passage à `completed` → `ferment_end = now()`.

## Structure de fichiers

**Créés :**
- `app/Services/BeerXmlImporter.php` — parsing + import transactionnel BeerXML (unique implémentation)
- `app/Exceptions/InvalidBeerXmlException.php` — erreur métier de l'import
- `app/Http/Controllers/Api/RecipeController.php` — index, show, update, destroy
- `app/Http/Controllers/Api/RecipeImportController.php` — action unique `__invoke`
- `app/Http/Controllers/Api/BrewingController.php` — index, store, show, update, startBoil, destroy
- `app/Http/Controllers/Api/BrewingStepController.php` — update (statut)
- `app/Http/Controllers/Api/DashboardController.php` — action unique `__invoke`
- `app/Http/Requests/UpdateRecipeRequest.php`, `ImportRecipeRequest.php`, `StoreBrewingRequest.php`, `UpdateBrewingRequest.php`, `UpdateBrewingStepRequest.php`
- `app/Http/Resources/UserResource.php`, `RecipeResource.php`, `StepResource.php`, `BrewingResource.php`, `BrewingStepResource.php`
- `tests/Feature/BeerXmlImporterTest.php`, `tests/Feature/Api/SanctumSpaTest.php`, `UserEndpointTest.php`, `RecipeReadTest.php`, `RecipeWriteTest.php`, `RecipeImportApiTest.php`, `BrewingReadTest.php`, `BrewingCreateTest.php`, `BrewingUpdateTest.php`, `BrewingDeleteTest.php`, `BrewingStepStatusTest.php`, `DashboardTest.php`, `FortifyHeadlessTest.php`

**Modifiés :**
- `app/Models/BrewingStep.php` — relation cassée `recipe()` renommée `brewing()`
- `app/Http/Controllers/FileController.php` — délègue au service
- `bootstrap/app.php` — `$middleware->statefulApi()`
- `routes/api.php` — routes du contrat ci-dessus
- `config/cors.php` — origins par env + credentials
- `config/session.php` — `serialization => json` (report phase 2)
- `config/cache.php` — `serializable_classes => false` (report phase 2)
- `.env.example` — `FRONTEND_URL`, `SANCTUM_STATEFUL_DOMAINS`
- `tests/Feature/DomainFactoriesTest.php` — test de la relation corrigée
- `docs/superpowers/plans/2026-08-11-migration-roadmap.md` — clôture de phase

---

### Tâche 1 : Réparer la relation `BrewingStep::recipe()` → `brewing()`

La relation actuelle `BrewingStep::recipe()` fait `belongsTo(Brewing::class)` : Eloquent déduit la FK `recipe_id` du nom de méthode alors que la colonne réelle est `brewing_id` — elle est donc cassée depuis toujours (piège documenté en phase 1). L'API en a besoin pour autoriser `PATCH /api/brewing-steps/{brewingStep}` via `$brewingStep->brewing`. Un grep confirme : **aucun usage** de l'ancienne relation dans le code (seuls les docs la mentionnent).

**Files:**
- Modify: `app/Models/BrewingStep.php`
- Test: `tests/Feature/DomainFactoriesTest.php`

**Interfaces:**
- Consumes: modèles existants `BrewingStep`, `Brewing`.
- Produces: `BrewingStep::brewing(): BelongsTo` (vers `Brewing`, FK `brewing_id`) — utilisée par les tâches 12 (autorisation) et 8+ (chargements).

- [ ] **Step 1 : Écrire le test qui échoue**

Ajouter à la fin de la classe `tests/Feature/DomainFactoriesTest.php` :

```php
    public function test_brewing_step_belongs_to_its_brewing(): void
    {
        $brewingStep = BrewingStep::factory()->create();

        $this->assertInstanceOf(Brewing::class, $brewingStep->brewing);
        $this->assertSame($brewingStep->brewing_id, $brewingStep->brewing->id);
    }
```

(Les imports `App\Models\Brewing` et `App\Models\BrewingStep` sont déjà présents dans ce fichier.)

- [ ] **Step 2 : Vérifier qu'il échoue**

Run : `docker run --rm -u $(id -u):$(id -g) -v "$PWD":/app -w /app php:8.5-cli vendor/bin/phpunit --filter=test_brewing_step_belongs_to_its_brewing`
Attendu : FAIL — `brewing` n'existe pas (propriété null → `assertInstanceOf` échoue).

- [ ] **Step 3 : Corriger le modèle**

Dans `app/Models/BrewingStep.php`, remplacer :

```php
    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Brewing::class);
    }
```

par :

```php
    public function brewing(): BelongsTo
    {
        return $this->belongsTo(Brewing::class);
    }
```

(Le nom de méthode `brewing` fait maintenant déduire la FK `brewing_id` — correcte.)

- [ ] **Step 4 : Vérifier que le test passe**

Run : `docker run --rm -u $(id -u):$(id -g) -v "$PWD":/app -w /app php:8.5-cli vendor/bin/phpunit --filter=test_brewing_step_belongs_to_its_brewing`
Attendu : PASS.

- [ ] **Step 5 : Suite complète (aucun usage de l'ancienne relation ne doit casser)**

Run : `docker run --rm -u $(id -u):$(id -g) -v "$PWD":/app -w /app php:8.5-cli vendor/bin/phpunit`
Attendu : 48 tests, 0 failure, 7 skips.

- [ ] **Step 6 : Commit**

```bash
git add app/Models/BrewingStep.php tests/Feature/DomainFactoriesTest.php
git commit -m "fix: relation BrewingStep::brewing() (la FK réelle est brewing_id, recipe() était cassée)"
```

---

### Tâche 2 : Extraire l'import BeerXML dans `App\Services\BeerXmlImporter`

L'import vit dans `FileController` (web). L'API doit le partager sans dupliquer. Le service encapsule parsing + transaction + logging et signale les échecs par une exception métier. Les 4 tests web existants (`BeerXmlImportTest`) servent de filet de non-régression.

**Files:**
- Create: `app/Exceptions/InvalidBeerXmlException.php`
- Create: `app/Services/BeerXmlImporter.php`
- Modify: `app/Http/Controllers/FileController.php`
- Test: `tests/Feature/BeerXmlImporterTest.php`

**Interfaces:**
- Consumes: modèles `Recipe`, `Step`, `User` ; logique actuelle de `FileController::importRecipe`/`createStep` (déplacée à l'identique).
- Produces: `BeerXmlImporter::import(string $xmlContent, User $user): Recipe` — lève `App\Exceptions\InvalidBeerXmlException` (XML illisible OU structure incomplète, rollback garanti). Consommée par la tâche 7.

- [ ] **Step 1 : Écrire les tests qui échouent**

Créer `tests/Feature/BeerXmlImporterTest.php` :

```php
<?php

namespace Tests\Feature;

use App\Exceptions\InvalidBeerXmlException;
use App\Models\Recipe;
use App\Models\User;
use App\Services\BeerXmlImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BeerXmlImporterTest extends TestCase
{
    use RefreshDatabase;

    private const VALID_XML = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<RECIPES>
  <RECIPE>
    <NAME>Test IPA</NAME>
    <TYPE>All Grain</TYPE>
    <STYLE><NAME>American IPA</NAME><TYPE>Ale</TYPE></STYLE>
    <BATCH_SIZE>20.0</BATCH_SIZE>
    <BOIL_SIZE>24.0</BOIL_SIZE>
    <EFFICIENCY>72.0</EFFICIENCY>
    <EST_COLOR>8.2</EST_COLOR>
    <IBU>45.3</IBU>
    <EST_ABV>5.6</EST_ABV>
    <EST_OG>1.052</EST_OG>
    <EST_FG>1.010</EST_FG>
    <BOIL_TIME>60</BOIL_TIME>
    <CARBONATION>2.4</CARBONATION>
    <FERMENTABLES>
      <FERMENTABLE><NAME>Pale Malt</NAME><AMOUNT>5.0</AMOUNT></FERMENTABLE>
      <FERMENTABLE><NAME>Munich</NAME><AMOUNT>0.5</AMOUNT></FERMENTABLE>
    </FERMENTABLES>
    <HOPS>
      <HOP><NAME>Cascade</NAME><AMOUNT>0.030</AMOUNT><TIME>60</TIME><USE>Boil</USE></HOP>
    </HOPS>
    <YEASTS>
      <YEAST><NAME>US-05</NAME><AMOUNT>0.011</AMOUNT></YEAST>
    </YEASTS>
    <MASH>
      <MASH_STEPS>
        <MASH_STEP>
          <NAME>Saccharification</NAME>
          <INFUSE_AMOUNT>15.0</INFUSE_AMOUNT>
          <INFUSE_TEMP>72.0</INFUSE_TEMP>
          <STEP_TEMP>67.0</STEP_TEMP>
          <STEP_TIME>60</STEP_TIME>
        </MASH_STEP>
      </MASH_STEPS>
      <SPARGE_TEMP>78.0</SPARGE_TEMP>
    </MASH>
    <PRIMARY_AGE>14</PRIMARY_AGE>
    <PRIMARY_TEMP>19.0</PRIMARY_TEMP>
  </RECIPE>
</RECIPES>
XML;

    public function test_import_creates_recipe_and_steps_for_the_given_user(): void
    {
        $user = User::factory()->create();

        $recipe = app(BeerXmlImporter::class)->import(self::VALID_XML, $user);

        $this->assertSame($user->id, $recipe->user_id);
        $this->assertSame('Test IPA', $recipe->name);
        // 2 fermentables + 1 hop + 1 yeast + infuse + mash step + sparge + primary
        $this->assertCount(8, $recipe->steps);
    }

    public function test_unreadable_xml_throws_and_persists_nothing(): void
    {
        $user = User::factory()->create();

        try {
            app(BeerXmlImporter::class)->import('<?xml version="1.0"?><RECIPES><RECIPE>', $user);
            $this->fail('InvalidBeerXmlException attendue');
        } catch (InvalidBeerXmlException) {
            $this->assertSame(0, Recipe::count());
        }
    }

    public function test_incomplete_structure_rolls_back_everything(): void
    {
        $user = User::factory()->create();
        // HOP sans USE : la création du step échoue APRÈS la création de la recipe
        $xml = str_replace('<USE>Boil</USE>', '', self::VALID_XML);

        try {
            app(BeerXmlImporter::class)->import($xml, $user);
            $this->fail('InvalidBeerXmlException attendue');
        } catch (InvalidBeerXmlException) {
            $this->assertSame(0, Recipe::count());
        }
    }
}
```

- [ ] **Step 2 : Vérifier qu'ils échouent**

Run : `docker run --rm -u $(id -u):$(id -g) -v "$PWD":/app -w /app php:8.5-cli vendor/bin/phpunit --filter=BeerXmlImporterTest`
Attendu : ERROR — `Class "App\Services\BeerXmlImporter" not found`.

- [ ] **Step 3 : Créer l'exception**

Créer `app/Exceptions/InvalidBeerXmlException.php` :

```php
<?php

namespace App\Exceptions;

use Exception;

class InvalidBeerXmlException extends Exception
{
}
```

- [ ] **Step 4 : Créer le service (logique déplacée à l'identique depuis FileController)**

Créer `app/Services/BeerXmlImporter.php` :

```php
<?php

namespace App\Services;

use App\Exceptions\InvalidBeerXmlException;
use App\Models\Recipe;
use App\Models\Step;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BeerXmlImporter
{
    /**
     * @throws InvalidBeerXmlException si le XML est illisible ou sa structure incomplète
     */
    public function import(string $xmlContent, User $user): Recipe
    {
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($xmlContent, 'SimpleXMLElement', LIBXML_NONET);
        libxml_clear_errors();

        if ($xml === false || ! isset($xml->RECIPE)) {
            throw new InvalidBeerXmlException('Contenu BeerXML illisible');
        }

        $arrayFile = json_decode(json_encode($xml), true);

        try {
            return DB::transaction(fn () => $this->importRecipe($arrayFile['RECIPE'], $user));
        } catch (\Throwable $e) {
            Log::warning('BeerXML import failed: '.$e->getMessage());

            throw new InvalidBeerXmlException('Structure BeerXML incomplète', previous: $e);
        }
    }

    private function importRecipe(array $recipe, User $user): Recipe
    {
        $imported_recipe = Recipe::create([
            'name' => $recipe['NAME'],
            'type' => $recipe['STYLE']['NAME'],
            'method' => $recipe['TYPE'],
            'ferment' => $recipe['STYLE']['TYPE'],
            'volume' => $recipe['BATCH_SIZE'],
            'efficiency' => $recipe['EFFICIENCY'],
            'color' => $recipe['EST_COLOR'],
            'bitterness' => $recipe['IBU'],
            'alcohol' => $recipe['EST_ABV'],
            'initial_density' => $recipe['EST_OG'],
            'final_density' => $recipe['EST_FG'],
            'boil_time' => $recipe['BOIL_TIME'],
            'carbonation' => $recipe['CARBONATION'],
            'user_id' => $user->id,
        ]);

        // Fermentables
        if (!empty($recipe['FERMENTABLES'])) {
            foreach ($recipe['FERMENTABLES']['FERMENTABLE'] as $fermentable) {
                $this->createStep($fermentable['AMOUNT'] * 1000, 'g', $fermentable['NAME'], null, 'Preparation', $imported_recipe->id);
            }
        }

        // Hops
        if (!empty($recipe['HOPS'])) {
            if (isset($recipe['HOPS']['HOP']['NAME'])) {
                $array_hop = $recipe['HOPS'];
            } else {
                $array_hop = $recipe['HOPS']['HOP'];
            }
            foreach ($array_hop as $hop) {
                $this->createStep($hop['AMOUNT'] * 1000, 'g', $hop['NAME'], $hop['TIME'], $hop['USE'], $imported_recipe->id);
            }
        }

        // Miscs
        if (!empty($recipe['MISCS'])) {
            if (isset($recipe['MISCS']['MISC']['NAME'])) {
                $array_misc = $recipe['MISCS'];
            } else {
                $array_misc = $recipe['MISCS']['MISC'];
            }
            foreach ($array_misc as $misc) {
                $this->createStep($misc['AMOUNT'] * 1000, 'g', $misc['NAME'], $misc['TIME'], $misc['USE'], $imported_recipe->id);
            }
        }

        // Yeasts
        if (!empty($recipe['YEASTS'])) {
            if (isset($recipe['YEASTS']['YEAST']['NAME'])) {
                $array_yeast = $recipe['YEASTS'];
            } else {
                $array_yeast = $recipe['YEASTS']['YEAST'];
            }
            foreach ($array_yeast as $yeast) {
                $this->createStep($yeast['AMOUNT'] * 1000, 'g', $yeast['NAME'], null, 'Yeast', $imported_recipe->id);
            }
        }

        // Mash
        $mash = $recipe['MASH']['MASH_STEPS']['MASH_STEP'];

        if (array_key_exists('INFUSE_AMOUNT', $mash)) {
            $this->createStep($mash['INFUSE_AMOUNT'], 'l', $mash['INFUSE_TEMP'] . ' °C', null, 'Preparation', $imported_recipe->id);
        }
        if (array_key_exists('STEP_TEMP', $mash)) {
            $this->createStep($mash['STEP_TEMP'], '°C', $mash['NAME'], $mash['STEP_TIME'], 'Mash', $imported_recipe->id);
        }
        if (array_key_exists('SPARGE_TEMP', $recipe['MASH'])) {
            $this->createStep($recipe['MASH']['SPARGE_TEMP'], '°C', 'Rincer les drêches jusqu\'à' . ' ' . $recipe['BOIL_SIZE'] . ' l', null, 'Mash', $imported_recipe->id);
        }

        // Fermentation
        if (array_key_exists('PRIMARY_AGE', $recipe)) {
            $this->createStep($recipe['PRIMARY_TEMP'], '°C', 'Primaire', $recipe['PRIMARY_AGE'] * 1440, 'Primary', $imported_recipe->id);
        }
        if (array_key_exists('SECONDARY_AGE', $recipe)) {
            $this->createStep($recipe['SECONDARY_TEMP'], '°C', 'Secondaire', $recipe['SECONDARY_AGE'] * 1440, 'Secondary', $imported_recipe->id);
        }
        if (array_key_exists('TERTIARY_AGE', $recipe)) {
            $this->createStep($recipe['TERTIARY_TEMP'], '°C', 'Tertiaire', $recipe['TERTIARY_AGE'] * 1440, 'Tertiary', $imported_recipe->id);
        }
        if (array_key_exists('AGE', $recipe)) {
            $this->createStep($recipe['AGE_TEMP'], '°C', 'Garde en bouteille', $recipe['AGE'] * 1440, 'Bottle', $imported_recipe->id);
        }

        return $imported_recipe;
    }

    private function createStep(float $quantity, string $unit, string $field, ?int $time, string $type, int $recipe_id): void
    {
        Step::create(
            compact('quantity', 'unit', 'field', 'time', 'type', 'recipe_id')
        );
    }
}
```

- [ ] **Step 5 : Vérifier que les tests du service passent**

Run : `docker run --rm -u $(id -u):$(id -g) -v "$PWD":/app -w /app php:8.5-cli vendor/bin/phpunit --filter=BeerXmlImporterTest`
Attendu : PASS (3 tests).

- [ ] **Step 6 : Faire déléguer FileController au service**

Remplacer intégralement le contenu de `app/Http/Controllers/FileController.php` par :

```php
<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidBeerXmlException;
use App\Services\BeerXmlImporter;
use Illuminate\Http\Request;

class FileController extends Controller
{
    public function index()
    {
        return view('recipes.uploadFile');
    }

    public function store(Request $request, BeerXmlImporter $importer)
    {
        if ($request->action == 'cancel') {
            return redirect()->route('recipes.index');
        }

        $request->validate([
            'file' => 'required|file|mimes:xml|max:2048',
        ]);

        try {
            $recipe = $importer->import($request->file('file')->get(), $request->user());
        } catch (InvalidBeerXmlException) {
            return back()->withErrors(['file' => __('Invalid BeerXML file')]);
        }

        return redirect()->route('recipes.show', ['recipe' => $recipe->id]);
    }
}
```

- [ ] **Step 7 : Vérifier la non-régression web (les 4 tests d'import existants)**

Run : `docker run --rm -u $(id -u):$(id -g) -v "$PWD":/app -w /app php:8.5-cli vendor/bin/phpunit --filter=BeerXmlImportTest`
Attendu : PASS (4 tests — comportement web identique).

- [ ] **Step 8 : Suite complète puis commit**

Run : `docker run --rm -u $(id -u):$(id -g) -v "$PWD":/app -w /app php:8.5-cli vendor/bin/phpunit`
Attendu : 51 tests, 0 failure, 7 skips.

```bash
git add app/Services/BeerXmlImporter.php app/Exceptions/InvalidBeerXmlException.php app/Http/Controllers/FileController.php tests/Feature/BeerXmlImporterTest.php
git commit -m "refactor: import BeerXML extrait dans le service BeerXmlImporter (partagé web/API)"
```

---

### Tâche 3 : Plomberie Sanctum SPA — `statefulApi()`, CORS credentials, env

Le mode SPA de Sanctum exige : le middleware `EnsureFrontendRequestsAreStateful` sur le groupe `api` (`statefulApi()`), un CORS qui renvoie l'origine exacte du front avec `credentials`, et les domaines stateful en env. `config/cors.php` expose déjà `sanctum/csrf-cookie` dans `paths`. **Ne pas « corriger » `config/sanctum.php`** : il référence `Illuminate\Foundation\Http\Middleware\ValidateCsrfToken`, qui existe toujours en L13 (alias de compatibilité vers `PreventRequestForgery` — vérifié dans vendor).

**Files:**
- Modify: `bootstrap/app.php`
- Modify: `config/cors.php`
- Modify: `.env.example`
- Test: `tests/Feature/Api/SanctumSpaTest.php`

**Interfaces:**
- Consumes: bootstrap L13 existant (`redirectUsersTo`, `throttleApi`).
- Produces: groupe `api` stateful — toutes les routes `/api/*` des tâches suivantes sont utilisables en session-cookie par le front. La variable d'env `FRONTEND_URL` (origine CORS) et `SANCTUM_STATEFUL_DOMAINS`.

- [ ] **Step 1 : Écrire les tests qui échouent**

Créer `tests/Feature/Api/SanctumSpaTest.php` :

```php
<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SanctumSpaTest extends TestCase
{
    use RefreshDatabase;

    public function test_csrf_cookie_endpoint_is_exposed(): void
    {
        $this->get('/sanctum/csrf-cookie')
            ->assertNoContent()
            ->assertCookie('XSRF-TOKEN');
    }

    public function test_a_session_authenticated_frontend_reaches_the_api(): void
    {
        $user = User::factory()->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'password']);

        // Referer stateful (localhost figure dans les domaines stateful par défaut)
        $this->withHeader('Referer', 'http://localhost')
            ->getJson('/api/user')
            ->assertOk();
    }

    public function test_cors_preflight_allows_the_frontend_origin_with_credentials(): void
    {
        $this->options('/api/user', [], [
            'Origin' => 'http://localhost:3000',
            'Access-Control-Request-Method' => 'GET',
        ])
            ->assertHeader('Access-Control-Allow-Origin', 'http://localhost:3000')
            ->assertHeader('Access-Control-Allow-Credentials', 'true');
    }
}
```

- [ ] **Step 2 : Vérifier qu'ils échouent**

Run : `docker run --rm -u $(id -u):$(id -g) -v "$PWD":/app -w /app php:8.5-cli vendor/bin/phpunit --filter=SanctumSpaTest`
Attendu : le test csrf-cookie passe déjà (Sanctum installé) ; `test_a_session_authenticated_frontend_reaches_the_api` échoue en 401 (pas de session sur le groupe `api`) ; le test CORS échoue (`Access-Control-Allow-Origin: *` et pas de header credentials).

- [ ] **Step 3 : Activer statefulApi dans bootstrap/app.php**

Dans `bootstrap/app.php`, compléter le bloc middleware :

```php
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->redirectUsersTo('/dashboard');

        // Parité avec l'ancien Kernel : throttle:api n'est pas dans le groupe
        // api par défaut de la nouvelle structure.
        $middleware->throttleApi();

        // Sanctum mode SPA : les requêtes du front (SANCTUM_STATEFUL_DOMAINS)
        // sont authentifiées par cookie de session.
        $middleware->statefulApi();
    })
```

- [ ] **Step 4 : CORS — origine du front + credentials**

Dans `config/cors.php`, remplacer :

```php
    'allowed_origins' => ['*'],
```

par :

```php
    'allowed_origins' => [env('FRONTEND_URL', 'http://localhost:3000')],
```

et :

```php
    'supports_credentials' => false,
```

par :

```php
    'supports_credentials' => true,
```

- [ ] **Step 5 : Compléter .env.example**

Dans `.env.example`, sous la ligne `APP_URL=http://localhost`, ajouter :

```
FRONTEND_URL=http://localhost:3000
SANCTUM_STATEFUL_DOMAINS=localhost:3000
```

- [ ] **Step 6 : Vérifier que les tests passent**

Run : `docker run --rm -u $(id -u):$(id -g) -v "$PWD":/app -w /app php:8.5-cli vendor/bin/phpunit --filter=SanctumSpaTest`
Attendu : PASS (3 tests).

- [ ] **Step 7 : Commit**

```bash
git add bootstrap/app.php config/cors.php .env.example tests/Feature/Api/SanctumSpaTest.php
git commit -m "feat: Sanctum mode SPA (statefulApi, CORS credentials, domaines stateful en env)"
```

---

### Tâche 4 : `GET /api/user` — contrat stable via `UserResource`

La route actuelle renvoie le modèle brut. Le front (nuxt-auth-sanctum) a besoin d'un contrat stable, incluant l'état 2FA.

**Files:**
- Create: `app/Http/Resources/UserResource.php`
- Modify: `routes/api.php`
- Test: `tests/Feature/Api/UserEndpointTest.php`

**Interfaces:**
- Consumes: `User` (colonnes Jetstream/Fortify : `two_factor_confirmed_at`, accessor `profile_photo_url`).
- Produces: `UserResource` — JSON `data: {id, name, email, profile_photo_url, two_factor_enabled}`. Réutilisée par la tâche 14.

- [ ] **Step 1 : Écrire les tests qui échouent**

Créer `tests/Feature/Api/UserEndpointTest.php` :

```php
<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_authenticated_user_is_returned_as_a_stable_resource(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->getJson('/api/user')
            ->assertOk()
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.email', $user->email)
            ->assertJsonPath('data.two_factor_enabled', false)
            ->assertJsonMissingPath('data.password');
    }

    public function test_guests_get_401(): void
    {
        $this->getJson('/api/user')->assertUnauthorized();
    }
}
```

- [ ] **Step 2 : Vérifier qu'ils échouent**

Run : `docker run --rm -u $(id -u):$(id -g) -v "$PWD":/app -w /app php:8.5-cli vendor/bin/phpunit --filter=UserEndpointTest`
Attendu : FAIL — la réponse actuelle n'a pas d'enveloppe `data`.

- [ ] **Step 3 : Créer la ressource**

Créer `app/Http/Resources/UserResource.php` :

```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'profile_photo_url' => $this->profile_photo_url,
            'two_factor_enabled' => $this->two_factor_confirmed_at !== null,
        ];
    }
}
```

- [ ] **Step 4 : Brancher la route**

Remplacer intégralement le contenu de `routes/api.php` par :

```php
<?php

use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', fn (Request $request) => new UserResource($request->user()));
});
```

- [ ] **Step 5 : Vérifier que les tests passent (y compris SanctumSpaTest qui consomme /api/user)**

Run : `docker run --rm -u $(id -u):$(id -g) -v "$PWD":/app -w /app php:8.5-cli vendor/bin/phpunit --filter="UserEndpointTest|SanctumSpaTest"`
Attendu : PASS (5 tests).

- [ ] **Step 6 : Commit**

```bash
git add app/Http/Resources/UserResource.php routes/api.php tests/Feature/Api/UserEndpointTest.php
git commit -m "feat: GET /api/user via UserResource (contrat stable, état 2FA)"
```

---

### Tâche 5 : API Recettes — lecture (`GET /api/recipes`, `GET /api/recipes/{recipe}`)

Équivalents API de `RecipesController` (liste scopée user) et `RecipeDetailsController` (détail + nombre de brassins), avec les `steps` de la recette dans le détail (nécessaires aux pages création de brassin et « completed » du front).

**Files:**
- Create: `app/Http/Resources/StepResource.php`
- Create: `app/Http/Resources/RecipeResource.php`
- Create: `app/Http/Controllers/Api/RecipeController.php`
- Modify: `routes/api.php`
- Test: `tests/Feature/Api/RecipeReadTest.php`

**Interfaces:**
- Consumes: `RecipePolicy::view`, relations `Recipe::steps()` (HasMany Step) et `Recipe::brewing()` (HasMany Brewing — nom singulier historique).
- Produces:
  - `StepResource` — `{id, quantity, unit, field, time, type}`.
  - `RecipeResource` — tous les champs domaine + `brewings_count` (si `withCount('brewing')`) + `steps` (si `load('steps')`). Réutilisée par les tâches 7, 8, 13.
  - `Api\RecipeController::index(Request)`, `show(Recipe)`.

- [ ] **Step 1 : Écrire les tests qui échouent**

Créer `tests/Feature/Api/RecipeReadTest.php` :

```php
<?php

namespace Tests\Feature\Api;

use App\Models\Recipe;
use App\Models\Step;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecipeReadTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_lists_only_the_users_recipes(): void
    {
        $user = User::factory()->create();
        Recipe::factory()->count(2)->for($user)->create();
        Recipe::factory()->create(); // autre user

        $this->actingAs($user)
            ->getJson('/api/recipes')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_show_returns_the_recipe_with_steps_and_brewings_count(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->for($user)->create(['name' => 'Ma Stout']);
        Step::factory()->count(3)->for($recipe)->create();

        $this->actingAs($user)
            ->getJson("/api/recipes/{$recipe->id}")
            ->assertOk()
            ->assertJsonPath('data.name', 'Ma Stout')
            ->assertJsonPath('data.brewings_count', 0)
            ->assertJsonCount(3, 'data.steps');
    }

    public function test_show_is_forbidden_on_someone_elses_recipe(): void
    {
        $recipe = Recipe::factory()->create();

        $this->actingAs(User::factory()->create())
            ->getJson("/api/recipes/{$recipe->id}")
            ->assertForbidden();
    }

    public function test_show_returns_404_for_a_missing_recipe(): void
    {
        $this->actingAs(User::factory()->create())
            ->getJson('/api/recipes/999999')
            ->assertNotFound();
    }

    public function test_guests_get_401(): void
    {
        $this->getJson('/api/recipes')->assertUnauthorized();
    }
}
```

- [ ] **Step 2 : Vérifier qu'ils échouent**

Run : `docker run --rm -u $(id -u):$(id -g) -v "$PWD":/app -w /app php:8.5-cli vendor/bin/phpunit --filter=RecipeReadTest`
Attendu : FAIL — 404 sur `/api/recipes` (route inexistante) ; seuls les tests 404/401 passent.

- [ ] **Step 3 : Créer StepResource**

Créer `app/Http/Resources/StepResource.php` :

```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StepResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'quantity' => $this->quantity,
            'unit' => $this->unit,
            'field' => $this->field,
            'time' => $this->time,
            'type' => $this->type,
        ];
    }
}
```

- [ ] **Step 4 : Créer RecipeResource**

Créer `app/Http/Resources/RecipeResource.php` :

```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecipeResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'method' => $this->method,
            'ferment' => $this->ferment,
            'volume' => $this->volume,
            'efficiency' => $this->efficiency,
            'color' => $this->color,
            'bitterness' => $this->bitterness,
            'alcohol' => $this->alcohol,
            'boil_time' => $this->boil_time,
            'initial_density' => $this->initial_density,
            'final_density' => $this->final_density,
            'before_boil_density' => $this->before_boil_density,
            'carbonation' => $this->carbonation,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            // La relation HasMany s'appelle historiquement brewing() (singulier)
            'brewings_count' => $this->whenCounted('brewing'),
            'steps' => StepResource::collection($this->whenLoaded('steps')),
        ];
    }
}
```

- [ ] **Step 5 : Créer le contrôleur**

Créer `app/Http/Controllers/Api/RecipeController.php` :

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RecipeResource;
use App\Models\Recipe;
use Illuminate\Http\Request;

class RecipeController extends Controller
{
    public function index(Request $request)
    {
        return RecipeResource::collection(
            Recipe::where('user_id', $request->user()->id)
                ->withCount('brewing')
                ->get()
        );
    }

    public function show(Recipe $recipe)
    {
        $this->authorize('view', $recipe);

        return new RecipeResource($recipe->load('steps')->loadCount('brewing'));
    }
}
```

- [ ] **Step 6 : Ajouter les routes**

Dans `routes/api.php`, ajouter l'import `use App\Http\Controllers\Api\RecipeController;` puis, dans le groupe `auth:sanctum`, après la route `/user` :

```php
    Route::get('/recipes', [RecipeController::class, 'index']);
    Route::get('/recipes/{recipe}', [RecipeController::class, 'show']);
```

- [ ] **Step 7 : Vérifier que les tests passent**

Run : `docker run --rm -u $(id -u):$(id -g) -v "$PWD":/app -w /app php:8.5-cli vendor/bin/phpunit --filter=RecipeReadTest`
Attendu : PASS (5 tests).

- [ ] **Step 8 : Commit**

```bash
git add app/Http/Resources/StepResource.php app/Http/Resources/RecipeResource.php app/Http/Controllers/Api/RecipeController.php routes/api.php tests/Feature/Api/RecipeReadTest.php
git commit -m "feat: API recettes en lecture (index scopé user, show avec steps et brewings_count)"
```

---

### Tâche 6 : API Recettes — écriture (`PATCH`, `DELETE /api/recipes/{recipe}`)

Équivalents de `RecipeDetailsController::edit` (seul champ éditable : `before_boil_density`) et `delete`. Comme dans le web, la suppression ne cascade pas sur brassins/steps (pas de FK contrainte en base — comportement conservé à l'identique, le schéma est gelé).

**Files:**
- Create: `app/Http/Requests/UpdateRecipeRequest.php`
- Modify: `app/Http/Controllers/Api/RecipeController.php`
- Modify: `routes/api.php`
- Test: `tests/Feature/Api/RecipeWriteTest.php`

**Interfaces:**
- Consumes: `RecipePolicy::update`/`delete`, `RecipeResource` (tâche 5).
- Produces: `Api\RecipeController::update(UpdateRecipeRequest, Recipe)`, `destroy(Recipe)` (204).

- [ ] **Step 1 : Écrire les tests qui échouent**

Créer `tests/Feature/Api/RecipeWriteTest.php` :

```php
<?php

namespace Tests\Feature\Api;

use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecipeWriteTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_update_before_boil_density(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->for($user)->create(['before_boil_density' => null]);

        $this->actingAs($user)
            ->patchJson("/api/recipes/{$recipe->id}", ['before_boil_density' => 1.048])
            ->assertOk()
            ->assertJsonPath('data.before_boil_density', 1.048);

        $this->assertEquals(1.048, $recipe->fresh()->before_boil_density);
    }

    public function test_update_rejects_a_non_numeric_density(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->for($user)->create();

        $this->actingAs($user)
            ->patchJson("/api/recipes/{$recipe->id}", ['before_boil_density' => 'abc'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('before_boil_density');
    }

    public function test_update_is_forbidden_for_non_owner(): void
    {
        $recipe = Recipe::factory()->create();

        $this->actingAs(User::factory()->create())
            ->patchJson("/api/recipes/{$recipe->id}", ['before_boil_density' => 1.048])
            ->assertForbidden();
    }

    public function test_owner_can_delete_a_recipe(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->for($user)->create();

        $this->actingAs($user)
            ->deleteJson("/api/recipes/{$recipe->id}")
            ->assertNoContent();

        $this->assertNull(Recipe::find($recipe->id));
    }

    public function test_delete_is_forbidden_for_non_owner(): void
    {
        $recipe = Recipe::factory()->create();

        $this->actingAs(User::factory()->create())
            ->deleteJson("/api/recipes/{$recipe->id}")
            ->assertForbidden();

        $this->assertNotNull(Recipe::find($recipe->id));
    }
}
```

- [ ] **Step 2 : Vérifier qu'ils échouent**

Run : `docker run --rm -u $(id -u):$(id -g) -v "$PWD":/app -w /app php:8.5-cli vendor/bin/phpunit --filter=RecipeWriteTest`
Attendu : FAIL — 405 (routes PATCH/DELETE inexistantes).

- [ ] **Step 3 : Créer la Form Request**

Créer `app/Http/Requests/UpdateRecipeRequest.php` :

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRecipeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('recipe'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'before_boil_density' => 'sometimes|nullable|numeric',
        ];
    }
}
```

- [ ] **Step 4 : Ajouter update/destroy au contrôleur**

Dans `app/Http/Controllers/Api/RecipeController.php`, ajouter l'import `use App\Http\Requests\UpdateRecipeRequest;` et les méthodes :

```php
    public function update(UpdateRecipeRequest $request, Recipe $recipe)
    {
        $recipe->update($request->validated());

        return new RecipeResource($recipe);
    }

    public function destroy(Recipe $recipe)
    {
        $this->authorize('delete', $recipe);

        $recipe->delete();

        return response()->noContent();
    }
```

- [ ] **Step 5 : Ajouter les routes**

Dans `routes/api.php`, sous les routes recettes existantes :

```php
    Route::patch('/recipes/{recipe}', [RecipeController::class, 'update']);
    Route::delete('/recipes/{recipe}', [RecipeController::class, 'destroy']);
```

- [ ] **Step 6 : Vérifier que les tests passent**

Run : `docker run --rm -u $(id -u):$(id -g) -v "$PWD":/app -w /app php:8.5-cli vendor/bin/phpunit --filter=RecipeWriteTest`
Attendu : PASS (5 tests).

- [ ] **Step 7 : Commit**

```bash
git add app/Http/Requests/UpdateRecipeRequest.php app/Http/Controllers/Api/RecipeController.php routes/api.php tests/Feature/Api/RecipeWriteTest.php
git commit -m "feat: API recettes en écriture (patch before_boil_density, delete)"
```

---

### Tâche 7 : Import BeerXML par l'API — `POST /api/recipes/import`

Même contrat de validation que le web, mais réponses JSON : 201 + recette importée (avec ses steps), 422 si le XML est invalide. Réutilise le service de la tâche 2.

**Files:**
- Create: `app/Http/Requests/ImportRecipeRequest.php`
- Create: `app/Http/Controllers/Api/RecipeImportController.php`
- Modify: `routes/api.php`
- Test: `tests/Feature/Api/RecipeImportApiTest.php`

**Interfaces:**
- Consumes: `BeerXmlImporter::import(string, User): Recipe` + `InvalidBeerXmlException` (tâche 2), `RecipeResource` (tâche 5).
- Produces: endpoint `POST /api/recipes/import` (multipart, champ `file`).

- [ ] **Step 1 : Écrire les tests qui échouent**

Créer `tests/Feature/Api/RecipeImportApiTest.php` (le XML valide est le même fixture que `BeerXmlImporterTest` — dupliqué volontairement, chaque fichier de test reste autonome) :

```php
<?php

namespace Tests\Feature\Api;

use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class RecipeImportApiTest extends TestCase
{
    use RefreshDatabase;

    private const VALID_XML = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<RECIPES>
  <RECIPE>
    <NAME>Test IPA</NAME>
    <TYPE>All Grain</TYPE>
    <STYLE><NAME>American IPA</NAME><TYPE>Ale</TYPE></STYLE>
    <BATCH_SIZE>20.0</BATCH_SIZE>
    <BOIL_SIZE>24.0</BOIL_SIZE>
    <EFFICIENCY>72.0</EFFICIENCY>
    <EST_COLOR>8.2</EST_COLOR>
    <IBU>45.3</IBU>
    <EST_ABV>5.6</EST_ABV>
    <EST_OG>1.052</EST_OG>
    <EST_FG>1.010</EST_FG>
    <BOIL_TIME>60</BOIL_TIME>
    <CARBONATION>2.4</CARBONATION>
    <FERMENTABLES>
      <FERMENTABLE><NAME>Pale Malt</NAME><AMOUNT>5.0</AMOUNT></FERMENTABLE>
      <FERMENTABLE><NAME>Munich</NAME><AMOUNT>0.5</AMOUNT></FERMENTABLE>
    </FERMENTABLES>
    <HOPS>
      <HOP><NAME>Cascade</NAME><AMOUNT>0.030</AMOUNT><TIME>60</TIME><USE>Boil</USE></HOP>
    </HOPS>
    <YEASTS>
      <YEAST><NAME>US-05</NAME><AMOUNT>0.011</AMOUNT></YEAST>
    </YEASTS>
    <MASH>
      <MASH_STEPS>
        <MASH_STEP>
          <NAME>Saccharification</NAME>
          <INFUSE_AMOUNT>15.0</INFUSE_AMOUNT>
          <INFUSE_TEMP>72.0</INFUSE_TEMP>
          <STEP_TEMP>67.0</STEP_TEMP>
          <STEP_TIME>60</STEP_TIME>
        </MASH_STEP>
      </MASH_STEPS>
      <SPARGE_TEMP>78.0</SPARGE_TEMP>
    </MASH>
    <PRIMARY_AGE>14</PRIMARY_AGE>
    <PRIMARY_TEMP>19.0</PRIMARY_TEMP>
  </RECIPE>
</RECIPES>
XML;

    private function importRequest(User $user, string $content)
    {
        return $this->actingAs($user)->postJson('/api/recipes/import', [
            'file' => UploadedFile::fake()->createWithContent('recipe.xml', $content),
        ]);
    }

    public function test_a_valid_beerxml_upload_returns_201_with_the_recipe(): void
    {
        $user = User::factory()->create();

        $this->importRequest($user, self::VALID_XML)
            ->assertCreated()
            ->assertJsonPath('data.name', 'Test IPA')
            ->assertJsonCount(8, 'data.steps');

        $recipe = Recipe::firstWhere('name', 'Test IPA');
        $this->assertNotNull($recipe);
        $this->assertSame($user->id, $recipe->user_id);
    }

    public function test_a_malformed_xml_returns_422_and_creates_nothing(): void
    {
        $user = User::factory()->create();

        $this->importRequest($user, '<?xml version="1.0"?><RECIPES><RECIPE>')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('file');

        $this->assertSame(0, Recipe::count());
    }

    public function test_an_incomplete_structure_returns_422_after_rollback(): void
    {
        $user = User::factory()->create();
        $xml = str_replace('<USE>Boil</USE>', '', self::VALID_XML);

        $this->importRequest($user, $xml)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('file');

        $this->assertSame(0, Recipe::count());
    }

    public function test_a_missing_file_returns_422(): void
    {
        $this->actingAs(User::factory()->create())
            ->postJson('/api/recipes/import', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('file');
    }

    public function test_guests_get_401(): void
    {
        $this->postJson('/api/recipes/import', [])->assertUnauthorized();
    }
}
```

- [ ] **Step 2 : Vérifier qu'ils échouent**

Run : `docker run --rm -u $(id -u):$(id -g) -v "$PWD":/app -w /app php:8.5-cli vendor/bin/phpunit --filter=RecipeImportApiTest`
Attendu : FAIL — 404 (route inexistante) sur les 4 premiers ; le test 401 passe.

- [ ] **Step 3 : Créer la Form Request**

Créer `app/Http/Requests/ImportRecipeRequest.php` :

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportRecipeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // toute personne authentifiée importe dans SON compte
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'file' => 'required|file|mimes:xml|max:2048',
        ];
    }
}
```

- [ ] **Step 4 : Créer le contrôleur**

Créer `app/Http/Controllers/Api/RecipeImportController.php` :

```php
<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\InvalidBeerXmlException;
use App\Http\Controllers\Controller;
use App\Http\Requests\ImportRecipeRequest;
use App\Http\Resources\RecipeResource;
use App\Services\BeerXmlImporter;
use Illuminate\Validation\ValidationException;

class RecipeImportController extends Controller
{
    public function __invoke(ImportRecipeRequest $request, BeerXmlImporter $importer)
    {
        try {
            $recipe = $importer->import($request->file('file')->get(), $request->user());
        } catch (InvalidBeerXmlException) {
            throw ValidationException::withMessages(['file' => __('Invalid BeerXML file')]);
        }

        return (new RecipeResource($recipe->load('steps')))
            ->response()
            ->setStatusCode(201);
    }
}
```

- [ ] **Step 5 : Ajouter la route**

Dans `routes/api.php`, ajouter l'import `use App\Http\Controllers\Api\RecipeImportController;` et, **avant** les routes `/recipes` existantes (hygiène : le littéral avant le paramètre `{recipe}`) :

```php
    Route::post('/recipes/import', RecipeImportController::class);
```

- [ ] **Step 6 : Vérifier que les tests passent**

Run : `docker run --rm -u $(id -u):$(id -g) -v "$PWD":/app -w /app php:8.5-cli vendor/bin/phpunit --filter=RecipeImportApiTest`
Attendu : PASS (5 tests).

- [ ] **Step 7 : Commit**

```bash
git add app/Http/Requests/ImportRecipeRequest.php app/Http/Controllers/Api/RecipeImportController.php routes/api.php tests/Feature/Api/RecipeImportApiTest.php
git commit -m "feat: import BeerXML par l'API (201 + recette, 422 si XML invalide)"
```

---

### Tâche 8 : API Brassins — lecture (`GET /api/recipes/{recipe}/brewings`, `GET /api/brewings/{brewing}`)

La liste est imbriquée sous la recette (comme le web) ; le détail est « shallow » (`/api/brewings/{brewing}`) — la `BrewingPolicy` remonte seule jusqu'au propriétaire, plus besoin du couple recipe/brewing dans l'URL. Le détail charge **toutes** les étapes du brassin : une seule requête front pour n'importe quelle page d'étape (le front filtre par `type`).

**Files:**
- Create: `app/Http/Resources/BrewingStepResource.php`
- Create: `app/Http/Resources/BrewingResource.php`
- Create: `app/Http/Controllers/Api/BrewingController.php`
- Modify: `routes/api.php`
- Test: `tests/Feature/Api/BrewingReadTest.php`

**Interfaces:**
- Consumes: `RecipePolicy::view`, `BrewingPolicy::view`, relations `Recipe::brewing()`, `Brewing::brewingSteps()`, `Brewing::recipe()`, `RecipeResource` (tâche 5).
- Produces:
  - `BrewingStepResource` — `{id, quantity, unit, field, date, time, type, status(bool)}`.
  - `BrewingResource` — champs domaine + `steps` (si `load('brewingSteps')`) + `recipe` (si `load('recipe')`). Réutilisée par les tâches 9, 10, 11, 13.
  - `Api\BrewingController::index(Recipe)`, `show(Brewing)`.

- [ ] **Step 1 : Écrire les tests qui échouent**

Créer `tests/Feature/Api/BrewingReadTest.php` :

```php
<?php

namespace Tests\Feature\Api;

use App\Models\Brewing;
use App\Models\BrewingStep;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BrewingReadTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_lists_the_brewings_of_a_recipe(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->for($user)->create();
        Brewing::factory()->count(2)->for($recipe)->create();

        $this->actingAs($user)
            ->getJson("/api/recipes/{$recipe->id}/brewings")
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_index_is_forbidden_on_someone_elses_recipe(): void
    {
        $recipe = Recipe::factory()->create();

        $this->actingAs(User::factory()->create())
            ->getJson("/api/recipes/{$recipe->id}/brewings")
            ->assertForbidden();
    }

    public function test_show_returns_the_brewing_with_all_its_steps(): void
    {
        $user = User::factory()->create();
        $brewing = Brewing::factory()
            ->for(Recipe::factory()->for($user))
            ->create(['current_step' => 'mash']);
        BrewingStep::factory()->count(3)->create(['brewing_id' => $brewing->id]);

        $this->actingAs($user)
            ->getJson("/api/brewings/{$brewing->id}")
            ->assertOk()
            ->assertJsonPath('data.current_step', 'mash')
            ->assertJsonCount(3, 'data.steps');
    }

    public function test_show_is_forbidden_on_someone_elses_brewing(): void
    {
        $brewing = Brewing::factory()->for(Recipe::factory())->create();

        $this->actingAs(User::factory()->create())
            ->getJson("/api/brewings/{$brewing->id}")
            ->assertForbidden();
    }
}
```

- [ ] **Step 2 : Vérifier qu'ils échouent**

Run : `docker run --rm -u $(id -u):$(id -g) -v "$PWD":/app -w /app php:8.5-cli vendor/bin/phpunit --filter=BrewingReadTest`
Attendu : FAIL — 404 (routes inexistantes).

- [ ] **Step 3 : Créer BrewingStepResource**

Créer `app/Http/Resources/BrewingStepResource.php` :

```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BrewingStepResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'quantity' => $this->quantity,
            'unit' => $this->unit,
            'field' => $this->field,
            'date' => $this->date,
            'time' => $this->time,
            'type' => $this->type,
            'status' => (bool) $this->status,
        ];
    }
}
```

- [ ] **Step 4 : Créer BrewingResource**

Créer `app/Http/Resources/BrewingResource.php` :

```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BrewingResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'volume' => $this->volume,
            'alcohol' => $this->alcohol,
            'boil_time' => $this->boil_time,
            'boil_start' => $this->boil_start,
            'ferment_start' => $this->ferment_start,
            'ferment_end' => $this->ferment_end,
            'initial_density' => $this->initial_density,
            'final_density' => $this->final_density,
            'before_boil_density' => $this->before_boil_density,
            'carbonation' => $this->carbonation,
            'date_start' => $this->date_start,
            'current_step' => $this->current_step,
            'note' => $this->note,
            'recipe_id' => $this->recipe_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'steps' => BrewingStepResource::collection($this->whenLoaded('brewingSteps')),
            'recipe' => new RecipeResource($this->whenLoaded('recipe')),
        ];
    }
}
```

- [ ] **Step 5 : Créer le contrôleur**

Créer `app/Http/Controllers/Api/BrewingController.php` :

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BrewingResource;
use App\Models\Brewing;
use App\Models\Recipe;

class BrewingController extends Controller
{
    public function index(Recipe $recipe)
    {
        $this->authorize('view', $recipe);

        return BrewingResource::collection($recipe->brewing()->get());
    }

    public function show(Brewing $brewing)
    {
        $this->authorize('view', $brewing);

        return new BrewingResource($brewing->load('brewingSteps'));
    }
}
```

- [ ] **Step 6 : Ajouter les routes**

Dans `routes/api.php`, ajouter l'import `use App\Http\Controllers\Api\BrewingController;` puis, sous les routes recettes :

```php
    Route::get('/recipes/{recipe}/brewings', [BrewingController::class, 'index']);
    Route::get('/brewings/{brewing}', [BrewingController::class, 'show']);
```

- [ ] **Step 7 : Vérifier que les tests passent**

Run : `docker run --rm -u $(id -u):$(id -g) -v "$PWD":/app -w /app php:8.5-cli vendor/bin/phpunit --filter=BrewingReadTest`
Attendu : PASS (4 tests).

- [ ] **Step 8 : Commit**

```bash
git add app/Http/Resources/BrewingStepResource.php app/Http/Resources/BrewingResource.php app/Http/Controllers/Api/BrewingController.php routes/api.php tests/Feature/Api/BrewingReadTest.php
git commit -m "feat: API brassins en lecture (liste par recette, détail shallow avec étapes)"
```

---

### Tâche 9 : API Brassins — création (`POST /api/recipes/{recipe}/brewings`)

Équivalent de `CreateBrewingController::store` : copie les caractéristiques de la recette dans le brassin, duplique les `steps` de la recette en `brewing_steps`, `current_step = 'preparation'`. Le tout sous transaction (le web ne le faisait pas — durcissement sans changement de contrat).

**Files:**
- Create: `app/Http/Requests/StoreBrewingRequest.php`
- Modify: `app/Http/Controllers/Api/BrewingController.php`
- Modify: `routes/api.php`
- Test: `tests/Feature/Api/BrewingCreateTest.php`

**Interfaces:**
- Consumes: `RecipePolicy::view` (parité web : créer un brassin requiert `view` sur la recette), `BrewingResource` (tâche 8).
- Produces: `Api\BrewingController::store(StoreBrewingRequest, Recipe)` — 201 + brassin avec `steps`.

- [ ] **Step 1 : Écrire les tests qui échouent**

Créer `tests/Feature/Api/BrewingCreateTest.php` :

```php
<?php

namespace Tests\Feature\Api;

use App\Models\BrewingStep;
use App\Models\Recipe;
use App\Models\Step;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BrewingCreateTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_creates_a_brewing_with_copied_recipe_steps(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->for($user)->create(['volume' => 20, 'boil_time' => 60]);
        Step::factory()->count(3)->for($recipe)->create(['type' => 'Preparation']);

        $response = $this->actingAs($user)
            ->postJson("/api/recipes/{$recipe->id}/brewings", [
                'name' => 'Brassin nº1',
                'note' => null,
                'date_start' => '2026-08-12',
            ])
            ->assertCreated()
            ->assertJsonPath('data.name', 'Brassin nº1')
            ->assertJsonPath('data.current_step', 'preparation')
            ->assertJsonPath('data.boil_time', 60)
            ->assertJsonCount(3, 'data.steps');

        // assertEquals (lâche) : volume est une colonne float, SQLite peut renvoyer 20.0
        $this->assertEquals(20, $response->json('data.volume'));

        $brewingId = $response->json('data.id');
        $this->assertSame(3, BrewingStep::where('brewing_id', $brewingId)->count());
        $this->assertSame(0, (int) BrewingStep::where('brewing_id', $brewingId)->where('status', true)->count());
    }

    public function test_store_requires_a_name_and_a_start_date(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->for($user)->create();

        $this->actingAs($user)
            ->postJson("/api/recipes/{$recipe->id}/brewings", [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'date_start']);
    }

    public function test_store_is_forbidden_on_someone_elses_recipe(): void
    {
        $recipe = Recipe::factory()->create();

        $this->actingAs(User::factory()->create())
            ->postJson("/api/recipes/{$recipe->id}/brewings", [
                'name' => 'Intrusion',
                'date_start' => '2026-08-12',
            ])
            ->assertForbidden();
    }
}
```

- [ ] **Step 2 : Vérifier qu'ils échouent**

Run : `docker run --rm -u $(id -u):$(id -g) -v "$PWD":/app -w /app php:8.5-cli vendor/bin/phpunit --filter=BrewingCreateTest`
Attendu : FAIL — 405 (pas de POST sur cette URI).

- [ ] **Step 3 : Créer la Form Request**

Créer `app/Http/Requests/StoreBrewingRequest.php` :

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBrewingRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Parité web (CreateBrewingController) : ability "view" sur la recette
        return $this->user()->can('view', $this->route('recipe'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'note' => 'nullable|string',
            'date_start' => 'required|date',
        ];
    }
}
```

- [ ] **Step 4 : Ajouter store au contrôleur**

Dans `app/Http/Controllers/Api/BrewingController.php`, ajouter les imports :

```php
use App\Http\Requests\StoreBrewingRequest;
use App\Models\BrewingStep;
use Illuminate\Support\Facades\DB;
```

et la méthode :

```php
    public function store(StoreBrewingRequest $request, Recipe $recipe)
    {
        $brewing = DB::transaction(function () use ($request, $recipe) {
            $brewing = Brewing::create([
                'name' => $request->validated('name'),
                'note' => $request->validated('note'),
                'date_start' => $request->validated('date_start'),
                'volume' => $recipe->volume,
                'alcohol' => $recipe->alcohol,
                'initial_density' => $recipe->initial_density,
                'final_density' => $recipe->final_density,
                'boil_time' => $recipe->boil_time,
                'before_boil_density' => $recipe->before_boil_density,
                'carbonation' => $recipe->carbonation,
                'recipe_id' => $recipe->id,
                'current_step' => 'preparation',
            ]);

            foreach ($recipe->steps as $step) {
                BrewingStep::create([
                    'quantity' => $step->quantity,
                    'unit' => $step->unit,
                    'field' => $step->field,
                    'date' => null, // parité web : Step n'a pas de colonne date
                    'time' => $step->time,
                    'type' => $step->type,
                    'brewing_id' => $brewing->id,
                ]);
            }

            return $brewing;
        });

        return (new BrewingResource($brewing->load('brewingSteps')))
            ->response()
            ->setStatusCode(201);
    }
```

- [ ] **Step 5 : Ajouter la route**

Dans `routes/api.php`, sous la route index des brassins :

```php
    Route::post('/recipes/{recipe}/brewings', [BrewingController::class, 'store']);
```

- [ ] **Step 6 : Vérifier que les tests passent**

Run : `docker run --rm -u $(id -u):$(id -g) -v "$PWD":/app -w /app php:8.5-cli vendor/bin/phpunit --filter=BrewingCreateTest`
Attendu : PASS (3 tests).

- [ ] **Step 7 : Commit**

```bash
git add app/Http/Requests/StoreBrewingRequest.php app/Http/Controllers/Api/BrewingController.php routes/api.php tests/Feature/Api/BrewingCreateTest.php
git commit -m "feat: création de brassin par l'API (copie des steps de la recette, transaction)"
```

---

### Tâche 10 : API Brassins — mise à jour (`PATCH /api/brewings/{brewing}`) + `POST /api/brewings/{brewing}/start-boil`

Un seul endpoint PATCH couvre `next()` (changement `current_step`), `modifyBoilTime()` et `NoteController::update`. Les timestamps de transition restent côté serveur (parité web : `YeastController::next` pose `ferment_start`, `FermentController::next` pose `ferment_end`). Le chrono d'ébullition (`startChrono`) devient une action dédiée — c'est le serveur qui pose `boil_start = now()`.

**Files:**
- Create: `app/Http/Requests/UpdateBrewingRequest.php`
- Modify: `app/Http/Controllers/Api/BrewingController.php`
- Modify: `routes/api.php`
- Test: `tests/Feature/Api/BrewingUpdateTest.php`

**Interfaces:**
- Consumes: `BrewingPolicy::update`, `BrewingResource` (tâche 8).
- Produces: `Api\BrewingController::update(UpdateBrewingRequest, Brewing)`, `startBoil(Brewing)`.

- [ ] **Step 1 : Écrire les tests qui échouent**

Créer `tests/Feature/Api/BrewingUpdateTest.php` :

```php
<?php

namespace Tests\Feature\Api;

use App\Models\Brewing;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BrewingUpdateTest extends TestCase
{
    use RefreshDatabase;

    private function ownedBrewing(User $user, array $attributes = []): Brewing
    {
        return Brewing::factory()
            ->for(Recipe::factory()->for($user))
            ->create($attributes);
    }

    public function test_note_and_boil_time_can_be_updated(): void
    {
        $user = User::factory()->create();
        $brewing = $this->ownedBrewing($user, ['boil_time' => 60]);

        $this->actingAs($user)
            ->patchJson("/api/brewings/{$brewing->id}", ['note' => 'Belle mousse', 'boil_time' => 75])
            ->assertOk()
            ->assertJsonPath('data.note', 'Belle mousse')
            ->assertJsonPath('data.boil_time', 75);
    }

    public function test_moving_to_ferment_sets_ferment_start(): void
    {
        $user = User::factory()->create();
        $brewing = $this->ownedBrewing($user, ['current_step' => 'yeast', 'ferment_start' => null]);

        $this->actingAs($user)
            ->patchJson("/api/brewings/{$brewing->id}", ['current_step' => 'ferment'])
            ->assertOk()
            ->assertJsonPath('data.current_step', 'ferment');

        $this->assertNotNull($brewing->fresh()->ferment_start);
    }

    public function test_moving_to_completed_sets_ferment_end(): void
    {
        $user = User::factory()->create();
        $brewing = $this->ownedBrewing($user, ['current_step' => 'ferment', 'ferment_end' => null]);

        $this->actingAs($user)
            ->patchJson("/api/brewings/{$brewing->id}", ['current_step' => 'completed'])
            ->assertOk();

        $this->assertNotNull($brewing->fresh()->ferment_end);
    }

    public function test_an_unknown_step_is_rejected(): void
    {
        $user = User::factory()->create();
        $brewing = $this->ownedBrewing($user);

        $this->actingAs($user)
            ->patchJson("/api/brewings/{$brewing->id}", ['current_step' => 'teleportation'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('current_step');
    }

    public function test_update_is_forbidden_for_non_owner(): void
    {
        $brewing = Brewing::factory()->for(Recipe::factory())->create();

        $this->actingAs(User::factory()->create())
            ->patchJson("/api/brewings/{$brewing->id}", ['note' => 'intrusion'])
            ->assertForbidden();
    }

    public function test_start_boil_stamps_boil_start_server_side(): void
    {
        $user = User::factory()->create();
        $brewing = $this->ownedBrewing($user, ['boil_start' => null]);

        $this->actingAs($user)
            ->postJson("/api/brewings/{$brewing->id}/start-boil")
            ->assertOk();

        $this->assertNotNull($brewing->fresh()->boil_start);
    }

    public function test_start_boil_is_forbidden_for_non_owner(): void
    {
        $brewing = Brewing::factory()->for(Recipe::factory())->create();

        $this->actingAs(User::factory()->create())
            ->postJson("/api/brewings/{$brewing->id}/start-boil")
            ->assertForbidden();
    }
}
```

- [ ] **Step 2 : Vérifier qu'ils échouent**

Run : `docker run --rm -u $(id -u):$(id -g) -v "$PWD":/app -w /app php:8.5-cli vendor/bin/phpunit --filter=BrewingUpdateTest`
Attendu : FAIL — 405/404 (routes inexistantes).

- [ ] **Step 3 : Créer la Form Request**

Créer `app/Http/Requests/UpdateBrewingRequest.php` :

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBrewingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('brewing'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'note' => 'sometimes|nullable|string',
            'boil_time' => 'sometimes|integer|min:1',
            'current_step' => 'sometimes|in:preparation,mash,boil,yeast,ferment,completed',
        ];
    }
}
```

- [ ] **Step 4 : Ajouter update/startBoil au contrôleur**

Dans `app/Http/Controllers/Api/BrewingController.php`, ajouter l'import `use App\Http\Requests\UpdateBrewingRequest;` et les méthodes :

```php
    public function update(UpdateBrewingRequest $request, Brewing $brewing)
    {
        $validated = $request->validated();

        // Parité web : les timestamps de transition sont posés côté serveur
        // (YeastController::next → ferment_start, FermentController::next → ferment_end).
        if (($validated['current_step'] ?? null) === 'ferment' && $brewing->current_step !== 'ferment') {
            $brewing->ferment_start = now();
        }
        if (($validated['current_step'] ?? null) === 'completed' && $brewing->current_step !== 'completed') {
            $brewing->ferment_end = now();
        }

        $brewing->fill($validated)->save();

        return new BrewingResource($brewing);
    }

    public function startBoil(Brewing $brewing)
    {
        $this->authorize('update', $brewing);

        $brewing->boil_start = now();
        $brewing->save();

        return new BrewingResource($brewing);
    }
```

- [ ] **Step 5 : Ajouter les routes**

Dans `routes/api.php`, sous la route show des brassins :

```php
    Route::patch('/brewings/{brewing}', [BrewingController::class, 'update']);
    Route::post('/brewings/{brewing}/start-boil', [BrewingController::class, 'startBoil']);
```

- [ ] **Step 6 : Vérifier que les tests passent**

Run : `docker run --rm -u $(id -u):$(id -g) -v "$PWD":/app -w /app php:8.5-cli vendor/bin/phpunit --filter=BrewingUpdateTest`
Attendu : PASS (7 tests).

- [ ] **Step 7 : Commit**

```bash
git add app/Http/Requests/UpdateBrewingRequest.php app/Http/Controllers/Api/BrewingController.php routes/api.php tests/Feature/Api/BrewingUpdateTest.php
git commit -m "feat: mise à jour de brassin par l'API (note, boil_time, transitions d'étape, start-boil)"
```

---

### Tâche 11 : API Brassins — suppression (`DELETE /api/brewings/{brewing}`)

Équivalent de `BrewingController::delete` (web) et `CompletedController::delete` : les `brewing_steps` sont supprimés explicitement (pas de cascade en base), puis le brassin — sous transaction.

**Files:**
- Modify: `app/Http/Controllers/Api/BrewingController.php`
- Modify: `routes/api.php`
- Test: `tests/Feature/Api/BrewingDeleteTest.php`

**Interfaces:**
- Consumes: `BrewingPolicy::delete`, relation `Brewing::brewingSteps()`.
- Produces: `Api\BrewingController::destroy(Brewing)` — 204.

- [ ] **Step 1 : Écrire les tests qui échouent**

Créer `tests/Feature/Api/BrewingDeleteTest.php` :

```php
<?php

namespace Tests\Feature\Api;

use App\Models\Brewing;
use App\Models\BrewingStep;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BrewingDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_deletes_the_brewing_and_its_steps(): void
    {
        $user = User::factory()->create();
        $brewing = Brewing::factory()->for(Recipe::factory()->for($user))->create();
        BrewingStep::factory()->count(2)->create(['brewing_id' => $brewing->id]);

        $this->actingAs($user)
            ->deleteJson("/api/brewings/{$brewing->id}")
            ->assertNoContent();

        $this->assertNull(Brewing::find($brewing->id));
        $this->assertSame(0, BrewingStep::where('brewing_id', $brewing->id)->count());
    }

    public function test_delete_is_forbidden_for_non_owner(): void
    {
        $brewing = Brewing::factory()->for(Recipe::factory())->create();

        $this->actingAs(User::factory()->create())
            ->deleteJson("/api/brewings/{$brewing->id}")
            ->assertForbidden();

        $this->assertNotNull(Brewing::find($brewing->id));
    }
}
```

- [ ] **Step 2 : Vérifier qu'ils échouent**

Run : `docker run --rm -u $(id -u):$(id -g) -v "$PWD":/app -w /app php:8.5-cli vendor/bin/phpunit --filter=BrewingDeleteTest`
Attendu : FAIL — 405 (pas de DELETE sur cette URI).

- [ ] **Step 3 : Ajouter destroy au contrôleur**

Dans `app/Http/Controllers/Api/BrewingController.php` (l'import `DB` existe depuis la tâche 9) :

```php
    public function destroy(Brewing $brewing)
    {
        $this->authorize('delete', $brewing);

        DB::transaction(function () use ($brewing) {
            $brewing->brewingSteps()->delete();
            $brewing->delete();
        });

        return response()->noContent();
    }
```

- [ ] **Step 4 : Ajouter la route**

Dans `routes/api.php`, sous les routes brassins :

```php
    Route::delete('/brewings/{brewing}', [BrewingController::class, 'destroy']);
```

- [ ] **Step 5 : Vérifier que les tests passent**

Run : `docker run --rm -u $(id -u):$(id -g) -v "$PWD":/app -w /app php:8.5-cli vendor/bin/phpunit --filter=BrewingDeleteTest`
Attendu : PASS (2 tests).

- [ ] **Step 6 : Commit**

```bash
git add app/Http/Controllers/Api/BrewingController.php routes/api.php tests/Feature/Api/BrewingDeleteTest.php
git commit -m "feat: suppression de brassin par l'API (steps inclus, transaction)"
```

---

### Tâche 12 : API Étapes — cocher/décocher (`PATCH /api/brewing-steps/{brewingStep}`)

Équivalent de `statusChange()` présent dans les 5 contrôleurs d'étapes Livewire — mais idempotent : le client envoie l'état cible (`status: true|false`) au lieu d'un toggle. L'autorisation remonte par la relation `brewing()` corrigée en tâche 1 (`BrewingPolicy::update`).

**Files:**
- Create: `app/Http/Requests/UpdateBrewingStepRequest.php`
- Create: `app/Http/Controllers/Api/BrewingStepController.php`
- Modify: `routes/api.php`
- Test: `tests/Feature/Api/BrewingStepStatusTest.php`

**Interfaces:**
- Consumes: `BrewingStep::brewing()` (tâche 1), `BrewingPolicy::update`, `BrewingStepResource` (tâche 8).
- Produces: `Api\BrewingStepController::update(UpdateBrewingStepRequest, BrewingStep)`.

- [ ] **Step 1 : Écrire les tests qui échouent**

Créer `tests/Feature/Api/BrewingStepStatusTest.php` :

```php
<?php

namespace Tests\Feature\Api;

use App\Models\Brewing;
use App\Models\BrewingStep;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BrewingStepStatusTest extends TestCase
{
    use RefreshDatabase;

    private function ownedStep(User $user, array $attributes = []): BrewingStep
    {
        $brewing = Brewing::factory()->for(Recipe::factory()->for($user))->create();

        return BrewingStep::factory()->create(['brewing_id' => $brewing->id] + $attributes);
    }

    public function test_owner_checks_and_unchecks_a_step(): void
    {
        $user = User::factory()->create();
        $step = $this->ownedStep($user, ['status' => false]);

        $this->actingAs($user)
            ->patchJson("/api/brewing-steps/{$step->id}", ['status' => true])
            ->assertOk()
            ->assertJsonPath('data.status', true);

        $this->assertTrue((bool) $step->fresh()->status);

        $this->actingAs($user)
            ->patchJson("/api/brewing-steps/{$step->id}", ['status' => false])
            ->assertOk()
            ->assertJsonPath('data.status', false);

        $this->assertFalse((bool) $step->fresh()->status);
    }

    public function test_status_is_required_and_boolean(): void
    {
        $user = User::factory()->create();
        $step = $this->ownedStep($user);

        $this->actingAs($user)
            ->patchJson("/api/brewing-steps/{$step->id}", ['status' => 'maybe'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('status');
    }

    public function test_update_is_forbidden_for_non_owner(): void
    {
        $owner = User::factory()->create();
        $step = $this->ownedStep($owner, ['status' => false]);

        $this->actingAs(User::factory()->create())
            ->patchJson("/api/brewing-steps/{$step->id}", ['status' => true])
            ->assertForbidden();

        $this->assertFalse((bool) $step->fresh()->status);
    }
}
```

- [ ] **Step 2 : Vérifier qu'ils échouent**

Run : `docker run --rm -u $(id -u):$(id -g) -v "$PWD":/app -w /app php:8.5-cli vendor/bin/phpunit --filter=BrewingStepStatusTest`
Attendu : FAIL — 404 (route inexistante).

- [ ] **Step 3 : Créer la Form Request**

Créer `app/Http/Requests/UpdateBrewingStepRequest.php` :

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBrewingStepRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Remonte au propriétaire via brewing → recipe (BrewingPolicy::update)
        return $this->user()->can('update', $this->route('brewingStep')->brewing);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'status' => 'required|boolean',
        ];
    }
}
```

- [ ] **Step 4 : Créer le contrôleur**

Créer `app/Http/Controllers/Api/BrewingStepController.php` :

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateBrewingStepRequest;
use App\Http\Resources\BrewingStepResource;
use App\Models\BrewingStep;

class BrewingStepController extends Controller
{
    public function update(UpdateBrewingStepRequest $request, BrewingStep $brewingStep)
    {
        $brewingStep->update($request->validated());

        return new BrewingStepResource($brewingStep);
    }
}
```

- [ ] **Step 5 : Ajouter la route**

Dans `routes/api.php`, ajouter l'import `use App\Http\Controllers\Api\BrewingStepController;` et, en fin de groupe :

```php
    Route::patch('/brewing-steps/{brewingStep}', [BrewingStepController::class, 'update']);
```

- [ ] **Step 6 : Vérifier que les tests passent**

Run : `docker run --rm -u $(id -u):$(id -g) -v "$PWD":/app -w /app php:8.5-cli vendor/bin/phpunit --filter=BrewingStepStatusTest`
Attendu : PASS (3 tests).

- [ ] **Step 7 : Commit**

```bash
git add app/Http/Requests/UpdateBrewingStepRequest.php app/Http/Controllers/Api/BrewingStepController.php routes/api.php tests/Feature/Api/BrewingStepStatusTest.php
git commit -m "feat: statut d'étape de brassin par l'API (PATCH idempotent, autorisation via brewing)"
```

---

### Tâche 13 : Dashboard — `GET /api/dashboard`

Équivalent de `HomeController` (Livewire) : recettes ayant un brassin en fermentation, et brassins « en cours » (ni `ferment` ni `completed`), du plus anciennement mis à jour au plus récent, avec leur recette embarquée (le front affiche le nom de la recette).

**Files:**
- Create: `app/Http/Controllers/Api/DashboardController.php`
- Modify: `routes/api.php`
- Test: `tests/Feature/Api/DashboardTest.php`

**Interfaces:**
- Consumes: `RecipeResource`, `BrewingResource` (avec `recipe` chargée), scoping par `user_id` (parité `HomeController::mount`).
- Produces: `GET /api/dashboard` → `data: {ferment_recipes: [...], in_progress_brewings: [...]}`.

- [ ] **Step 1 : Écrire les tests qui échouent**

Créer `tests/Feature/Api/DashboardTest.php` :

```php
<?php

namespace Tests\Feature\Api;

use App\Models\Brewing;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_splits_ferment_recipes_and_in_progress_brewings(): void
    {
        $user = User::factory()->create();

        $fermenting = Recipe::factory()->for($user)->create(['name' => 'En fermentation']);
        Brewing::factory()->for($fermenting)->create(['current_step' => 'ferment']);

        $inProgress = Recipe::factory()->for($user)->create();
        Brewing::factory()->for($inProgress)->create(['current_step' => 'mash', 'name' => 'Brassin actif']);

        $done = Recipe::factory()->for($user)->create();
        Brewing::factory()->for($done)->create(['current_step' => 'completed']);

        $this->actingAs($user)
            ->getJson('/api/dashboard')
            ->assertOk()
            ->assertJsonCount(1, 'data.ferment_recipes')
            ->assertJsonPath('data.ferment_recipes.0.name', 'En fermentation')
            ->assertJsonCount(1, 'data.in_progress_brewings')
            ->assertJsonPath('data.in_progress_brewings.0.name', 'Brassin actif')
            ->assertJsonPath('data.in_progress_brewings.0.recipe.id', $inProgress->id);
    }

    public function test_dashboard_never_leaks_someone_elses_data(): void
    {
        $other = Recipe::factory()->create();
        Brewing::factory()->for($other)->create(['current_step' => 'ferment']);
        Brewing::factory()->for($other)->create(['current_step' => 'mash']);

        $this->actingAs(User::factory()->create())
            ->getJson('/api/dashboard')
            ->assertOk()
            ->assertJsonCount(0, 'data.ferment_recipes')
            ->assertJsonCount(0, 'data.in_progress_brewings');
    }
}
```

- [ ] **Step 2 : Vérifier qu'ils échouent**

Run : `docker run --rm -u $(id -u):$(id -g) -v "$PWD":/app -w /app php:8.5-cli vendor/bin/phpunit --filter=DashboardTest`
Attendu : FAIL — 404 (route inexistante).

- [ ] **Step 3 : Créer le contrôleur**

Créer `app/Http/Controllers/Api/DashboardController.php` :

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BrewingResource;
use App\Http\Resources\RecipeResource;
use App\Models\Brewing;
use App\Models\Recipe;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $recipeIds = Recipe::where('user_id', $request->user()->id)->pluck('id');

        return response()->json([
            'data' => [
                'ferment_recipes' => RecipeResource::collection(
                    Recipe::where('user_id', $request->user()->id)
                        ->whereHas('brewing', fn ($query) => $query->where('current_step', 'ferment'))
                        ->get()
                ),
                'in_progress_brewings' => BrewingResource::collection(
                    Brewing::whereIn('recipe_id', $recipeIds)
                        ->whereNotIn('current_step', ['ferment', 'completed'])
                        ->oldest('updated_at')
                        ->with('recipe')
                        ->get()
                ),
            ],
        ]);
    }
}
```

- [ ] **Step 4 : Ajouter la route**

Dans `routes/api.php`, ajouter l'import `use App\Http\Controllers\Api\DashboardController;` et, sous la route `/user` :

```php
    Route::get('/dashboard', DashboardController::class);
```

- [ ] **Step 5 : Vérifier que les tests passent**

Run : `docker run --rm -u $(id -u):$(id -g) -v "$PWD":/app -w /app php:8.5-cli vendor/bin/phpunit --filter=DashboardTest`
Attendu : PASS (2 tests).

- [ ] **Step 6 : Commit**

```bash
git add app/Http/Controllers/Api/DashboardController.php routes/api.php tests/Feature/Api/DashboardTest.php
git commit -m "feat: dashboard API (recettes en fermentation + brassins en cours)"
```

---

### Tâche 14 : Fortify headless — verrouiller les flux JSON par des tests de caractérisation

Fortify répond déjà en JSON quand la requête l'exige (`postJson`) : login (200 + `two_factor`), logout (204), register (201), 2FA challenge (204), reset password (200), profil/mot de passe (200). Ces tests **caractérisent** le comportement dont Nuxt dépendra en phase 4 — la plupart doivent passer du premier coup ; si l'un échoue, c'est une vraie découverte de config à corriger avant d'aller plus loin (ne pas modifier le test pour le faire passer sans comprendre).

**Files:**
- Test: `tests/Feature/Api/FortifyHeadlessTest.php`

**Interfaces:**
- Consumes: endpoints Fortify existants (`/login`, `/logout`, `/register`, `/two-factor-challenge`, `/forgot-password`, `/reset-password`, `/user/profile-information`, `/user/password`), `GET /api/user` (tâche 4), plomberie stateful (tâche 3).
- Produces: contrat auth verrouillé pour la phase 4 (nuxt-auth-sanctum).

- [ ] **Step 1 : Écrire les tests**

Créer `tests/Feature/Api/FortifyHeadlessTest.php` :

```php
<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class FortifyHeadlessTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_returns_json_and_opens_an_api_session(): void
    {
        $user = User::factory()->create();

        $this->postJson('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertOk()
            ->assertJson(['two_factor' => false]);

        $this->withHeader('Referer', 'http://localhost')
            ->getJson('/api/user')
            ->assertOk()
            ->assertJsonPath('data.id', $user->id);
    }

    public function test_invalid_credentials_return_422(): void
    {
        $user = User::factory()->create();

        $this->postJson('/login', ['email' => $user->email, 'password' => 'wrong'])
            ->assertUnprocessable();
    }

    public function test_logout_returns_204_and_closes_the_session(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->postJson('/logout')->assertNoContent();
    }

    public function test_registration_works_headlessly(): void
    {
        $this->postJson('/register', [
            'name' => 'Brewer',
            'email' => 'brewer@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'terms' => true,
        ])->assertCreated();

        $this->assertNotNull(User::firstWhere('email', 'brewer@example.com'));
    }

    public function test_two_factor_login_challenge_accepts_a_recovery_code(): void
    {
        $user = User::factory()->create([
            'two_factor_secret' => encrypt('base32secret'),
            'two_factor_recovery_codes' => encrypt(json_encode(['valid-recovery-code'])),
            'two_factor_confirmed_at' => now(),
        ]);

        $this->postJson('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertOk()
            ->assertJson(['two_factor' => true]);

        $this->postJson('/two-factor-challenge', ['recovery_code' => 'valid-recovery-code'])
            ->assertNoContent();

        $this->withHeader('Referer', 'http://localhost')
            ->getJson('/api/user')
            ->assertOk()
            ->assertJsonPath('data.two_factor_enabled', true);
    }

    public function test_password_reset_flow_works_in_json(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->postJson('/forgot-password', ['email' => $user->email])->assertOk();
        Notification::assertSentTo($user, ResetPassword::class);

        $token = Password::broker()->createToken($user);

        $this->postJson('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertOk();

        $this->postJson('/login', ['email' => $user->email, 'password' => 'new-password'])
            ->assertOk();
    }

    public function test_profile_information_updates_headlessly(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->putJson('/user/profile-information', [
                'name' => 'Nouveau Nom',
                'email' => $user->email,
            ])
            ->assertOk();

        $this->assertSame('Nouveau Nom', $user->fresh()->name);
    }

    public function test_password_updates_headlessly(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->putJson('/user/password', [
                'current_password' => 'password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ])
            ->assertOk();
    }
}
```

- [ ] **Step 2 : Lancer et analyser**

Run : `docker run --rm -u $(id -u):$(id -g) -v "$PWD":/app -w /app php:8.5-cli vendor/bin/phpunit --filter=FortifyHeadlessTest`
Attendu : PASS (8 tests). **Si un test échoue** : c'est un écart réel de config Fortify/Jetstream — diagnostiquer (`config/fortify.php` features, réponses Fortify) et corriger la config, pas le test, sauf si le comportement constaté est documenté comme équivalent (ex. code HTTP différent mais sémantique identique — dans ce cas ajuster l'assertion ET le noter dans le message de commit).

- [ ] **Step 3 : Commit**

```bash
git add tests/Feature/Api/FortifyHeadlessTest.php
git commit -m "test: caractérisation des flux Fortify headless (login, 2FA, reset, profil) pour la phase 4"
```

---

### Tâche 15 : Reports de la phase 2 — `session.serialization => json` et `cache.serializable_classes => false`

Deux durcissements différés (voir `2026-08-12-phase-2-upgrade-laravel.md`, « Risques résiduels ») à faire maintenant que la config est retouchée de toute façon. **Impact déploiement : le passage à `json` invalide les sessions actives en prod → tous les utilisateurs seront déconnectés une fois** (c'était la raison du gel en phase 2 ; on l'assume maintenant, avant la bascule Nuxt).

**Files:**
- Modify: `config/session.php`
- Modify: `config/cache.php`

**Interfaces:**
- Consumes: décision de la phase 2 (gel temporaire documenté).
- Produces: sessions sérialisées en JSON (plus d'objets PHP en session), cache refusant de désérialiser des classes PHP (durcissement anti gadget-chain du skeleton L13).

- [ ] **Step 1 : Passer la sérialisation des sessions en json**

Dans `config/session.php`, remplacer :

```php
    'serialization' => 'php',
```

par :

```php
    'serialization' => 'json',
```

- [ ] **Step 2 : Ajouter serializable_classes au cache**

Dans `config/cache.php`, ajouter à la fin du tableau retourné (après la clé `prefix`), avec le commentaire du skeleton L13 :

```php
    /*
    |--------------------------------------------------------------------------
    | Serializable Classes
    |--------------------------------------------------------------------------
    |
    | This value determines the classes that can be unserialized from cache
    | storage. By default, no PHP classes will be unserialized from your
    | cache to prevent gadget chain attacks if your APP_KEY is leaked.
    |
    */

    'serializable_classes' => false,
```

- [ ] **Step 3 : Suite complète**

Run : `docker run --rm -u $(id -u):$(id -g) -v "$PWD":/app -w /app php:8.5-cli vendor/bin/phpunit`
Attendu : 0 failure, 7 skips. (L'app ne met en cache que des scalaires — rate limiter — et ne stocke pas d'objets en session ; si un test casse ici, c'est précisément ce que ce step devait détecter.)

- [ ] **Step 4 : Commit**

```bash
git add config/session.php config/cache.php
git commit -m "chore: sessions en json et cache sans désérialisation de classes (reports phase 2, déconnexion prod au déploiement)"
```

---

### Tâche 16 : Vérification finale des routes + clôture documentaire

**Files:**
- Verify: `routes/api.php`
- Modify: `docs/superpowers/plans/2026-08-11-migration-roadmap.md`

**Interfaces:**
- Consumes: tout ce qui précède.
- Produces: roadmap à jour — la phase 4 démarrera de là.

- [ ] **Step 1 : Vérifier l'état final de routes/api.php**

Après les tâches 4-13, `routes/api.php` doit être exactement :

```php
<?php

use App\Http\Controllers\Api\BrewingController;
use App\Http\Controllers\Api\BrewingStepController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\RecipeController;
use App\Http\Controllers\Api\RecipeImportController;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', fn (Request $request) => new UserResource($request->user()));

    Route::get('/dashboard', DashboardController::class);

    Route::post('/recipes/import', RecipeImportController::class);
    Route::get('/recipes', [RecipeController::class, 'index']);
    Route::get('/recipes/{recipe}', [RecipeController::class, 'show']);
    Route::patch('/recipes/{recipe}', [RecipeController::class, 'update']);
    Route::delete('/recipes/{recipe}', [RecipeController::class, 'destroy']);

    Route::get('/recipes/{recipe}/brewings', [BrewingController::class, 'index']);
    Route::post('/recipes/{recipe}/brewings', [BrewingController::class, 'store']);
    Route::get('/brewings/{brewing}', [BrewingController::class, 'show']);
    Route::patch('/brewings/{brewing}', [BrewingController::class, 'update']);
    Route::post('/brewings/{brewing}/start-boil', [BrewingController::class, 'startBoil']);
    Route::delete('/brewings/{brewing}', [BrewingController::class, 'destroy']);

    Route::patch('/brewing-steps/{brewingStep}', [BrewingStepController::class, 'update']);
});
```

Corriger toute divergence (ordre, imports, oublis), puis relancer la suite complète :

Run : `docker run --rm -u $(id -u):$(id -g) -v "$PWD":/app -w /app php:8.5-cli vendor/bin/phpunit`
Attendu : ~100 tests, 0 failure, 7 skips.

- [ ] **Step 2 : Mettre à jour la roadmap**

Dans `docs/superpowers/plans/2026-08-11-migration-roadmap.md`, à la fin de la section « Phase 3 — API REST + Fortify headless », ajouter :

```markdown
**✅ Réalisée le 2026-08-XX** (plan détaillé : `2026-08-12-phase-3-api-rest.md`). API complète sous `/api` (user, dashboard, recettes CRUD + import BeerXML, brassins CRUD + start-boil + transitions d'étape serveur, statut d'étapes), autorisée par les policies phase 1, testée endpoint par endpoint. Import BeerXML extrait dans `App\Services\BeerXmlImporter` (partagé web/API). Sanctum en mode SPA (`statefulApi()`, CORS credentials sur `FRONTEND_URL`, `SANCTUM_STATEFUL_DOMAINS`), flux Fortify headless verrouillés par tests de caractérisation (login, 2FA, reset, profil). Reports phase 2 soldés : `session.serialization => json`, `cache.serializable_classes => false`. **Actions de déploiement : renseigner `FRONTEND_URL` et `SANCTUM_STATEFUL_DOMAINS` dans le `.env` de prod ; le passage des sessions en json déconnecte tous les utilisateurs une fois.** La relation cassée `BrewingStep::recipe()` a été réparée en `brewing()`.
```

(Remplacer `2026-08-XX` par la date réelle d'exécution.)

- [ ] **Step 3 : Commit**

```bash
git add docs/superpowers/plans/2026-08-11-migration-roadmap.md docs/superpowers/plans/2026-08-12-phase-3-api-rest.md
git commit -m "docs: phase 3 réalisée (API REST complète, Sanctum SPA, Fortify headless)"
```

---

## Notes de déploiement cumulées (à l'attention du user)

Au premier déploiement incluant les phases 1-3 :
1. PHP ≥ 8.5 sur le serveur **avant** tout (phase 2).
2. `composer install --no-dev`, `php artisan optimize:clear` (phase 2).
3. Purger `public/files/` (reliquat phase 1, si pas déjà fait).
4. Renseigner `FRONTEND_URL` et `SANCTUM_STATEFUL_DOMAINS` dans le `.env` de prod (phase 3 — dès que le front Nuxt existe ; sans front, les valeurs par défaut sont inertes).
5. Sessions : tous les utilisateurs seront déconnectés une fois (`serialization => json`).

## Hors-scope assumé (phase 4)

- Consommation de l'API par Nuxt (`nuxt-auth-sanctum`), pages, PWA, i18n, chrono client.
- Retrait de Jetstream/Livewire, des vues Blade, des routes web — et de `FileController` (qui ne sera alors plus qu'un doublon web de l'import).
- `SESSION_DOMAIN`/sous-domaines : à trancher en phase 4 selon l'architecture de déploiement (même domaine vs `api.` + `app.`).
