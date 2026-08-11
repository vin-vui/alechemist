# Phase 1 — Sécurisation : plan d'implémentation

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Éliminer l'IDOR généralisé (policies + autorisation), sécuriser l'import BeerXML (pas de persistance publique, transaction, gestion d'erreur) et corriger le scoping utilisateur — sans changer le comportement pour un utilisateur légitime.

**Architecture:** On ajoute des policies `RecipePolicy`/`BrewingPolicy`, on autorise dans le `mount()` de chaque composant Livewire full-page (les propriétés Livewire 3 sont snapshotées et checksummées : une fois le mount autorisé, `$this->brewing`/`$this->recipe` ne sont pas falsifiables), et on garde-fou toute action Livewire qui reçoit un ID/modèle du client. L'import XML n'écrit plus jamais sur disque et s'exécute dans une transaction.

**Tech Stack:** Laravel 11.7, Livewire 3.4, PHPUnit 10.5, SQLite in-memory pour les tests. Pas de PHP local : tout tourne via Docker (`php:8.3-cli`, `composer:2`).

## Global Constraints

- **Aucun PHP local** — toutes les commandes passent par Docker :
  - Composer : `docker run --rm -u $(id -u):$(id -g) -e COMPOSER_HOME=/tmp -v "$PWD":/app -w /app composer:2 <cmd>`
  - Artisan/PHPUnit : `docker run --rm -u $(id -u):$(id -g) -v "$PWD":/app -w /app php:8.3-cli <cmd>`
  - Abréviations utilisées dans ce plan : `DCOMPOSER` et `DPHP` pour ces deux préfixes.
- Tests sur SQLite in-memory (jamais sur la base MySQL de dev).
- Aucun changement de schéma DB, aucun changement de comportement visible pour le propriétaire des données.
- Ne PAS toucher : duplication des step-controllers, `wire:poll.s`, naming `*Controller` — traités en phase 4 (les fichiers disparaissent).
- Un commit par tâche, messages `feat:`/`fix:`/`test:` en français.
- Travail sur la branche `phase-1-securisation` (créée en Task 1).

## Contexte codebase pour l'exécutant

- Les composants de `app/Livewire/` sont des composants **full-page** Livewire 3 routés dans `routes/web.php:38-58`. Ils s'appuient aujourd'hui sur un mécanisme implicite : le paramètre de route `{recipe}` est auto-assigné à la propriété publique non typée `$recipe` (ID brut, string). Ce plan rend le binding **explicite** via `mount(Recipe $recipe, Brewing $brewing)`.
- `Brewing.recipe_id` → `Recipe.user_id` porte la propriété. Il n'y a pas de colonne `user_id` sur `brewings`.
- Attention : `BrewingStep::recipe()` (app/Models/BrewingStep.php) est une relation **cassée** (belongsTo(Brewing::class) avec FK déduite `recipe_id` alors que la colonne est `brewing_id`). Ne pas l'utiliser dans les tests ni les factories ; passer par `brewing_id` explicitement. Ne pas la corriger dans cette phase.
- `resources/views/steps/_header.blade.php:1-3` re-résout l'ID en modèle via `Recipe::find($recipe)` — supprimé en Task 4 quand `$recipe` devient un modèle.

---

### Task 1: Bootstrap environnement + suite de tests verte sur SQLite

**Files:**
- Modify: `phpunit.xml:24-25`
- Modify: `tests/TestCase.php`
- Create: `.env` (depuis `.env.example`, non commité)

**Interfaces:**
- Produces: environnement exécutable (`vendor/` installé, `.env` avec clé), suite PHPUnit verte sur SQLite in-memory — prérequis de toutes les tâches suivantes.

- [ ] **Step 1: Créer la branche**

```bash
git checkout -b phase-1-securisation
```

- [ ] **Step 2: Installer les dépendances et initialiser .env**

```bash
docker run --rm -u $(id -u):$(id -g) -e COMPOSER_HOME=/tmp -v "$PWD":/app -w /app composer:2 install --no-interaction
cp .env.example .env
docker run --rm -u $(id -u):$(id -g) -v "$PWD":/app -w /app php:8.3-cli php artisan key:generate
```

Attendu : `vendor/` créé, `APP_KEY` renseignée dans `.env`.

- [ ] **Step 3: Activer SQLite in-memory pour les tests**

Dans `phpunit.xml`, décommenter les deux lignes (24-25) :

```xml
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
```

- [ ] **Step 4: Désactiver Vite dans les tests**

Les vues utilisent `@vite` et aucun build front n'existe dans cet environnement. Modifier `tests/TestCase.php` :

```php
<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }
}
```

- [ ] **Step 5: Lancer la suite existante**

```bash
docker run --rm -u $(id -u):$(id -g) -v "$PWD":/app -w /app php:8.3-cli vendor/bin/phpunit
```

Attendu : PASS (tests stock Jetstream, ~30 tests). Si un test échoue, diagnostiquer avant de continuer — la baseline doit être verte.

- [ ] **Step 6: Commit**

```bash
git add phpunit.xml tests/TestCase.php
git commit -m "test: suite sur SQLite in-memory + withoutVite"
```

---

### Task 2: Factories du domaine

**Files:**
- Create: `database/factories/RecipeFactory.php`
- Create: `database/factories/BrewingFactory.php`
- Create: `database/factories/StepFactory.php`
- Create: `database/factories/BrewingStepFactory.php`
- Test: `tests/Feature/DomainFactoriesTest.php`

**Interfaces:**
- Consumes: modèles existants (`App\Models\{Recipe,Brewing,Step,BrewingStep,User}`), tous déjà `HasFactory`.
- Produces: `Recipe::factory()`, `Brewing::factory()`, `Step::factory()`, `BrewingStep::factory()` — utilisées par toutes les tâches suivantes. Conventions : `Recipe::factory()->for($user)` (relation `user`), `Brewing::factory()->for($recipe)` (relation `recipe`), `BrewingStep::factory()->create(['brewing_id' => $brewing->id])` (**jamais** `->for()` sur BrewingStep, relation cassée).

- [ ] **Step 1: Écrire le test qui échoue**

`tests/Feature/DomainFactoriesTest.php` :

```php
<?php

namespace Tests\Feature;

use App\Models\Brewing;
use App\Models\BrewingStep;
use App\Models\Recipe;
use App\Models\Step;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DomainFactoriesTest extends TestCase
{
    use RefreshDatabase;

    public function test_brewing_step_factory_creates_full_chain(): void
    {
        $brewingStep = BrewingStep::factory()->create();

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('recipes', 1);
        $this->assertDatabaseCount('brewings', 1);
        $this->assertDatabaseCount('brewing_steps', 1);
        $this->assertNotNull(Brewing::find($brewingStep->brewing_id));
    }

    public function test_step_factory_belongs_to_a_recipe(): void
    {
        $step = Step::factory()->create();

        $this->assertInstanceOf(Recipe::class, $step->recipe);
        $this->assertNotNull($step->recipe->user_id);
    }
}
```

- [ ] **Step 2: Vérifier que le test échoue**

```bash
DPHP vendor/bin/phpunit tests/Feature/DomainFactoriesTest.php
```

Attendu : FAIL — `Call to undefined method ... factory()` ou classe factory introuvable.

- [ ] **Step 3: Créer les quatre factories**

`database/factories/RecipeFactory.php` :

```php
<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Recipe>
 */
class RecipeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'type' => 'American IPA',
            'method' => 'All Grain',
            'ferment' => 'Ale',
            'volume' => 20,
            'efficiency' => '72.0',
            'color' => '8.2',
            'bitterness' => '45.3',
            'alcohol' => '5.6',
            'boil_time' => 60,
            'initial_density' => 1.052,
            'final_density' => 1.010,
            'before_boil_density' => null,
            'carbonation' => 2.4,
            'user_id' => User::factory(),
        ];
    }
}
```

`database/factories/BrewingFactory.php` :

```php
<?php

namespace Database\Factories;

use App\Models\Recipe;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Brewing>
 */
class BrewingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'volume' => 20,
            'alcohol' => '5.6',
            'initial_density' => 1.052,
            'final_density' => 1.010,
            'boil_time' => 60,
            'boil_start' => null,
            'ferment_start' => null,
            'ferment_end' => null,
            'before_boil_density' => null,
            'carbonation' => 2.4,
            'date_start' => now()->toDateString(),
            'current_step' => 'preparation',
            'note' => null,
            'recipe_id' => Recipe::factory(),
        ];
    }
}
```

`database/factories/StepFactory.php` :

```php
<?php

namespace Database\Factories;

use App\Models\Recipe;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Step>
 */
class StepFactory extends Factory
{
    public function definition(): array
    {
        return [
            'quantity' => 5000,
            'unit' => 'g',
            'field' => 'Pale Ale Malt',
            'time' => null,
            'type' => 'Preparation',
            'recipe_id' => Recipe::factory(),
        ];
    }
}
```

`database/factories/BrewingStepFactory.php` :

```php
<?php

namespace Database\Factories;

use App\Models\Brewing;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\BrewingStep>
 */
class BrewingStepFactory extends Factory
{
    public function definition(): array
    {
        return [
            'quantity' => 5000,
            'unit' => 'g',
            'field' => 'Pale Ale Malt',
            'date' => null,
            'time' => null,
            'type' => 'Preparation',
            'status' => false,
            'brewing_id' => Brewing::factory(),
        ];
    }
}
```

- [ ] **Step 4: Vérifier que le test passe**

```bash
DPHP vendor/bin/phpunit tests/Feature/DomainFactoriesTest.php
```

Attendu : PASS (2 tests).

- [ ] **Step 5: Commit**

```bash
git add database/factories tests/Feature/DomainFactoriesTest.php
git commit -m "test: factories Recipe/Brewing/Step/BrewingStep"
```

---

### Task 3: Policies Recipe et Brewing

**Files:**
- Create: `app/Policies/RecipePolicy.php`
- Create: `app/Policies/BrewingPolicy.php`
- Modify: `app/Providers/AuthServiceProvider.php:15-17`
- Test: `tests/Feature/PolicyTest.php`

**Interfaces:**
- Consumes: factories de Task 2.
- Produces: abilities `view`, `update`, `delete` sur `Recipe` et `Brewing` — consommées par `$this->authorize(...)` en Task 4 et par l'API en phase 3. Signature : `view(User $user, Recipe $recipe): bool`, idem `update`/`delete` ; `BrewingPolicy` idem avec `Brewing`.

- [ ] **Step 1: Écrire le test qui échoue**

`tests/Feature/PolicyTest.php` :

```php
<?php

namespace Tests\Feature;

use App\Models\Brewing;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class PolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_owner_is_authorized_on_recipe(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $recipe = Recipe::factory()->for($owner)->create();

        foreach (['view', 'update', 'delete'] as $ability) {
            $this->assertTrue(Gate::forUser($owner)->allows($ability, $recipe), "owner should {$ability}");
            $this->assertFalse(Gate::forUser($other)->allows($ability, $recipe), "other should not {$ability}");
        }
    }

    public function test_brewing_authorization_follows_recipe_owner(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $brewing = Brewing::factory()->for(Recipe::factory()->for($owner))->create();

        foreach (['view', 'update', 'delete'] as $ability) {
            $this->assertTrue(Gate::forUser($owner)->allows($ability, $brewing), "owner should {$ability}");
            $this->assertFalse(Gate::forUser($other)->allows($ability, $brewing), "other should not {$ability}");
        }
    }
}
```

- [ ] **Step 2: Vérifier que le test échoue**

```bash
DPHP vendor/bin/phpunit tests/Feature/PolicyTest.php
```

Attendu : FAIL — les `assertTrue` échouent (aucune policy → Gate refuse tout pour un modèle sans policy).

- [ ] **Step 3: Créer les policies et les enregistrer**

`app/Policies/RecipePolicy.php` :

```php
<?php

namespace App\Policies;

use App\Models\Recipe;
use App\Models\User;

class RecipePolicy
{
    public function view(User $user, Recipe $recipe): bool
    {
        return $user->id === $recipe->user_id;
    }

    public function update(User $user, Recipe $recipe): bool
    {
        return $user->id === $recipe->user_id;
    }

    public function delete(User $user, Recipe $recipe): bool
    {
        return $user->id === $recipe->user_id;
    }
}
```

`app/Policies/BrewingPolicy.php` :

```php
<?php

namespace App\Policies;

use App\Models\Brewing;
use App\Models\User;

class BrewingPolicy
{
    public function view(User $user, Brewing $brewing): bool
    {
        return $user->id === $brewing->recipe?->user_id;
    }

    public function update(User $user, Brewing $brewing): bool
    {
        return $user->id === $brewing->recipe?->user_id;
    }

    public function delete(User $user, Brewing $brewing): bool
    {
        return $user->id === $brewing->recipe?->user_id;
    }
}
```

Dans `app/Providers/AuthServiceProvider.php`, remplacer le tableau vide `$policies` :

```php
use App\Models\Brewing;
use App\Models\Recipe;
use App\Policies\BrewingPolicy;
use App\Policies\RecipePolicy;

protected $policies = [
    Recipe::class => RecipePolicy::class,
    Brewing::class => BrewingPolicy::class,
];
```

- [ ] **Step 4: Vérifier que le test passe**

```bash
DPHP vendor/bin/phpunit tests/Feature/PolicyTest.php
```

Attendu : PASS (2 tests).

- [ ] **Step 5: Commit**

```bash
git add app/Policies app/Providers/AuthServiceProvider.php tests/Feature/PolicyTest.php
git commit -m "feat: policies Recipe/Brewing (propriété via recipe.user_id)"
```

---

### Task 4: Autorisation des pages — mount() explicite sur tous les composants

**Files:**
- Modify: `app/Livewire/RecipeDetailsController.php` (mount)
- Modify: `app/Livewire/BrewingController.php` (mount)
- Modify: `app/Livewire/CreateBrewingController.php` (mount)
- Modify: `app/Livewire/PreparationController.php` (mount)
- Modify: `app/Livewire/MashController.php` (mount)
- Modify: `app/Livewire/BoilController.php` (mount)
- Modify: `app/Livewire/YeastController.php` (mount)
- Modify: `app/Livewire/FermentController.php` (mount)
- Modify: `app/Livewire/CompletedController.php` (mount + propriété `$recipeSteps`)
- Modify: `app/Livewire/NoteController.php` (mount)
- Modify: `resources/views/steps/_header.blade.php:1-3`
- Test: `tests/Feature/PageAuthorizationTest.php`

**Interfaces:**
- Consumes: policies de Task 3 (`authorize('view', $recipe)`), factories de Task 2.
- Produces: `$this->recipe` est désormais un **modèle `Recipe`** (plus un ID brut) dans tous les composants — Task 5 s'appuie dessus. Chaque composant utilise le trait `Illuminate\Foundation\Auth\Access\AuthorizesRequests`.

- [ ] **Step 1: Écrire le test qui échoue**

`tests/Feature/PageAuthorizationTest.php` :

```php
<?php

namespace Tests\Feature;

use App\Models\Brewing;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private function pageUrls(Recipe $recipe, Brewing $brewing): array
    {
        return [
            route('recipes.show', $recipe),
            route('brewing.index', $recipe),
            route('brewing.create', $recipe),
            route('preparation', [$recipe, $brewing]),
            route('mash', [$recipe, $brewing]),
            route('boil', [$recipe, $brewing]),
            route('yeast', [$recipe, $brewing]),
            route('ferment', [$recipe, $brewing]),
            route('completed', [$recipe, $brewing]),
            route('note', [$recipe, $brewing]),
        ];
    }

    public function test_all_pages_are_forbidden_for_non_owner(): void
    {
        $owner = User::factory()->withPersonalTeam()->create();
        $other = User::factory()->withPersonalTeam()->create();
        $recipe = Recipe::factory()->for($owner)->create();
        $brewing = Brewing::factory()->for($recipe)->create();

        foreach ($this->pageUrls($recipe, $brewing) as $url) {
            $this->actingAs($other)->get($url)->assertForbidden();
        }
    }

    public function test_all_pages_are_accessible_for_owner(): void
    {
        $owner = User::factory()->withPersonalTeam()->create();
        $recipe = Recipe::factory()->for($owner)->create();
        $brewing = Brewing::factory()->for($recipe)->create();

        foreach ($this->pageUrls($recipe, $brewing) as $url) {
            $this->actingAs($owner)->get($url)->assertOk();
        }
    }

    public function test_brewing_not_belonging_to_route_recipe_is_not_found(): void
    {
        $owner = User::factory()->withPersonalTeam()->create();
        $recipe = Recipe::factory()->for($owner)->create();
        $otherRecipe = Recipe::factory()->for($owner)->create();
        $brewing = Brewing::factory()->for($recipe)->create();

        $this->actingAs($owner)
            ->get(route('mash', [$otherRecipe, $brewing]))
            ->assertNotFound();
    }
}
```

- [ ] **Step 2: Vérifier que le test échoue**

```bash
DPHP vendor/bin/phpunit tests/Feature/PageAuthorizationTest.php
```

Attendu : FAIL — `test_all_pages_are_forbidden_for_non_owner` reçoit des 200 au lieu de 403 (c'est l'IDOR), et le test de mismatch reçoit 200 au lieu de 404. Si `test_all_pages_are_accessible_for_owner` échoue sur une vue (variable indéfinie), corriger le setup du test (pas la vue) avant de continuer.

- [ ] **Step 3: Sécuriser les mounts**

Motif commun — pour chaque composant : ajouter les imports, le trait `AuthorizesRequests`, et le nouveau `mount()`. Le reste du fichier ne change pas, sauf mention explicite.

`app/Livewire/RecipeDetailsController.php` — ajouter `use Illuminate\Foundation\Auth\Access\AuthorizesRequests;` en import et `use AuthorizesRequests;` dans la classe, puis :

```php
public function mount(Recipe $recipe)
{
    $this->authorize('view', $recipe);

    $this->recipe = $recipe;
    $this->inProgressCount = Brewing::where('recipe_id', $this->recipe->id)->count();
}
```

`app/Livewire/BrewingController.php` — idem trait + import, puis :

```php
public function mount(Recipe $recipe)
{
    $this->authorize('view', $recipe);

    $this->recipe = $recipe;
    $this->brewings = Brewing::where('recipe_id', $this->recipe->id)->get();
}
```

`app/Livewire/CreateBrewingController.php` — idem trait + import, puis :

```php
public function mount(Recipe $recipe)
{
    $this->authorize('view', $recipe);

    $this->recipe = $recipe;
    $this->steps = $recipe->steps;
    $this->date_start = Carbon::today()->format('Y-m-d');
}
```

`app/Livewire/PreparationController.php` — idem trait + import (ajouter aussi `use App\Models\Recipe;`), puis :

```php
public function mount(Recipe $recipe, Brewing $brewing)
{
    abort_unless($brewing->recipe_id === $recipe->id, 404);
    $this->authorize('view', $recipe);

    $this->recipe = $recipe;
    $this->brewing = $brewing;
    $this->stepsCount = BrewingStep::where('type', 'Preparation')->where('brewing_id', $this->brewing->id)->count();
    $this->checkStep = BrewingStep::where('type', 'Preparation')->where('brewing_id', $this->brewing->id)->where('status', true)->count();
    $this->allChecked();
}
```

`app/Livewire/MashController.php` — même motif exactement (trait, imports `Recipe`), avec le corps existant qui filtre sur `'Mash'` :

```php
public function mount(Recipe $recipe, Brewing $brewing)
{
    abort_unless($brewing->recipe_id === $recipe->id, 404);
    $this->authorize('view', $recipe);

    $this->recipe = $recipe;
    $this->brewing = $brewing;
    $this->stepsCount = BrewingStep::where('type', 'Mash')->where('brewing_id', $this->brewing->id)->count();
    $this->checkStep = BrewingStep::where('type', 'Mash')->where('brewing_id', $this->brewing->id)->where('status', true)->count();
    $this->allChecked();
}
```

`app/Livewire/BoilController.php` — même motif (trait, imports `Recipe`), corps existant sur `['Boil', 'Aroma']` :

```php
public function mount(Recipe $recipe, Brewing $brewing)
{
    abort_unless($brewing->recipe_id === $recipe->id, 404);
    $this->authorize('view', $recipe);

    $this->recipe = $recipe;
    $this->brewing = $brewing;
    $this->stepsCount = BrewingStep::whereIn('type', ['Boil', 'Aroma'])->where('brewing_id', $this->brewing->id)->count();
    $this->checkStep = BrewingStep::whereIn('type', ['Boil', 'Aroma'])->where('brewing_id', $this->brewing->id)->where('status', true)->count();
    $this->steps = BrewingStep::whereIn('type', ['Boil', 'Aroma'])->where('brewing_id', $this->brewing->id)->get();
    $this->allChecked();
}
```

`app/Livewire/YeastController.php` — même motif, corps existant sur `'Yeast'` :

```php
public function mount(Recipe $recipe, Brewing $brewing)
{
    abort_unless($brewing->recipe_id === $recipe->id, 404);
    $this->authorize('view', $recipe);

    $this->recipe = $recipe;
    $this->brewing = $brewing;
    $this->stepsCount = BrewingStep::where('type', 'Yeast')->where('brewing_id', $this->brewing->id)->count();
    $this->checkStep = BrewingStep::where('type', 'Yeast')->where('brewing_id', $this->brewing->id)->where('status', true)->count();
    $this->allChecked();
}
```

`app/Livewire/FermentController.php` — même motif, corps existant sur `['Primary', 'Secondary', 'Tertiary', 'Bottle']` :

```php
public function mount(Recipe $recipe, Brewing $brewing)
{
    abort_unless($brewing->recipe_id === $recipe->id, 404);
    $this->authorize('view', $recipe);

    $this->recipe = $recipe;
    $this->brewing = $brewing;
    $this->stepsCount = BrewingStep::whereIn('type', ['Primary', 'Secondary', 'Tertiary', 'Bottle'])->where('brewing_id', $this->brewing->id)->count();
    $this->checkStep = BrewingStep::whereIn('type', ['Primary', 'Secondary', 'Tertiary', 'Bottle'])->where('brewing_id', $this->brewing->id)->where('status', true)->count();
    $this->fermentTime = BrewingStep::whereIn('type', ['Primary', 'Secondary', 'Tertiary', 'Bottle'])->where('brewing_id', $this->brewing->id)->sum('time');
    $this->allChecked();
}
```

`app/Livewire/CompletedController.php` — même motif, PLUS : renommer la propriété déclarée `$recipeStep` en `$recipeSteps` (la vue `brewings/completed.blade.php:44` utilise `$this->recipeSteps`, aujourd'hui une propriété dynamique non déclarée) :

```php
public $recipe, $brewing, $recipeSteps;

public function mount(Recipe $recipe, Brewing $brewing)
{
    abort_unless($brewing->recipe_id === $recipe->id, 404);
    $this->authorize('view', $recipe);

    $this->recipe = $recipe;
    $this->brewing = $brewing;
    $this->recipeSteps = Step::where('recipe_id', $recipe->id)->get();
}
```

`app/Livewire/NoteController.php` — même motif (le `Recipe::find` disparaît) :

```php
public function mount(Recipe $recipe, Brewing $brewing)
{
    abort_unless($brewing->recipe_id === $recipe->id, 404);
    $this->authorize('view', $recipe);

    $this->recipe = $recipe;
    $this->brewing = $brewing;
    $this->note = $brewing->note;
}
```

`resources/views/steps/_header.blade.php` — supprimer les lignes 1-3 (`@php $recipe = App\Models\Recipe::find($recipe); @endphp`) : `$recipe` reçu est désormais un modèle.

- [ ] **Step 4: Vérifier que le test passe et lancer toute la suite**

```bash
DPHP vendor/bin/phpunit tests/Feature/PageAuthorizationTest.php
DPHP vendor/bin/phpunit
```

Attendu : PASS partout (aucune régression Jetstream).

- [ ] **Step 5: Commit**

```bash
git add app/Livewire resources/views/steps/_header.blade.php tests/Feature/PageAuthorizationTest.php
git commit -m "fix: autorisation dans mount() de tous les composants (IDOR pages)"
```

---

### Task 5: Garde-fous sur les actions Livewire à paramètre client

**Files:**
- Modify: `app/Livewire/PreparationController.php` (statusChange)
- Modify: `app/Livewire/MashController.php` (statusChange)
- Modify: `app/Livewire/BoilController.php` (statusChange, modifyBoilTime)
- Modify: `app/Livewire/YeastController.php` (statusChange)
- Modify: `app/Livewire/FermentController.php` (statusChange)
- Modify: `app/Livewire/BrewingController.php` (delete)
- Modify: `app/Livewire/RecipeDetailsController.php` (delete)
- Modify: `app/Livewire/CompletedController.php` (delete)
- Modify: `app/Livewire/NoteController.php` (update — paramètre inutilisé)
- Modify: `resources/views/steps/boil.blade.php:54`
- Modify: `resources/views/recipes/recipe.blade.php:274`
- Modify: `resources/views/brewings/completed.blade.php:135`
- Test: `tests/Feature/LivewireActionGuardTest.php`

**Interfaces:**
- Consumes: `$this->recipe`/`$this->brewing` (modèles, Task 4) — non falsifiables car snapshotés/checksummés par Livewire.
- Produces: aucune action Livewire n'accepte plus un modèle hors du périmètre du composant. Signatures modifiées : `modifyBoilTime()` (sans paramètre), `RecipeDetailsController::delete()` (sans paramètre), `CompletedController::delete()` (sans paramètre), `NoteController::update()` (sans paramètre).

- [ ] **Step 1: Écrire le test qui échoue**

`tests/Feature/LivewireActionGuardTest.php` :

```php
<?php

namespace Tests\Feature;

use App\Livewire\BrewingController;
use App\Livewire\PreparationController;
use App\Models\Brewing;
use App\Models\BrewingStep;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class LivewireActionGuardTest extends TestCase
{
    use RefreshDatabase;

    public function test_status_change_rejects_step_of_another_brewing(): void
    {
        $user = User::factory()->withPersonalTeam()->create();
        $recipe = Recipe::factory()->for($user)->create();
        $brewing = Brewing::factory()->for($recipe)->create();
        $foreignStep = BrewingStep::factory()->create(); // autre brewing, autre user

        try {
            Livewire::actingAs($user)
                ->test(PreparationController::class, ['recipe' => $recipe, 'brewing' => $brewing])
                ->call('statusChange', $foreignStep->id);
            $this->fail('Expected 403');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }

        $this->assertFalse((bool) $foreignStep->fresh()->status);
    }

    public function test_status_change_toggles_own_step(): void
    {
        $user = User::factory()->withPersonalTeam()->create();
        $recipe = Recipe::factory()->for($user)->create();
        $brewing = Brewing::factory()->for($recipe)->create();
        $step = BrewingStep::factory()->create(['brewing_id' => $brewing->id]);

        Livewire::actingAs($user)
            ->test(PreparationController::class, ['recipe' => $recipe, 'brewing' => $brewing])
            ->call('statusChange', $step->id);

        $this->assertTrue((bool) $step->fresh()->status);
    }

    public function test_brewing_delete_rejects_brewing_of_another_recipe(): void
    {
        $user = User::factory()->withPersonalTeam()->create();
        $recipe = Recipe::factory()->for($user)->create();
        $foreignBrewing = Brewing::factory()->create(); // autre recipe, autre user

        try {
            Livewire::actingAs($user)
                ->test(BrewingController::class, ['recipe' => $recipe])
                ->call('delete', $foreignBrewing->id);
            $this->fail('Expected 403');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }

        $this->assertNotNull($foreignBrewing->fresh());
    }
}
```

- [ ] **Step 2: Vérifier que le test échoue**

```bash
DPHP vendor/bin/phpunit tests/Feature/LivewireActionGuardTest.php
```

Attendu : FAIL — les deux tests « rejects » échouent avec `Expected 403` (l'action passe), le step étranger est togglé, le brewing étranger supprimé.

- [ ] **Step 3: Ajouter les garde-fous**

Dans **chacun** de `PreparationController`, `MashController`, `BoilController`, `YeastController`, `FermentController`, la méthode `statusChange` reçoit en première ligne le garde :

```php
public function statusChange(BrewingStep $step)
{
    abort_unless($step->brewing_id === $this->brewing->id, 403);

    $step->status = !$step->status;
    $step->save();

    if($step->status) {
        $this->checkStep += 1;
    } else {
        $this->checkStep -= 1;
    }

    $this->allChecked();
}
```

`app/Livewire/BoilController.php` — `modifyBoilTime` n'accepte plus de paramètre :

```php
public function modifyBoilTime()
{
    if ($this->newBoilTime > 0) {
        $this->brewing->boil_time = intval($this->newBoilTime);
        $this->brewing->save();
    } else {
        $this->newBoilTime = $this->brewing->boil_time;
    }

    $this->closeModal();
}
```

`resources/views/steps/boil.blade.php:54` : `wire:click="modifyBoilTime({{ $brewing->id }})"` → `wire:click="modifyBoilTime"`.

`app/Livewire/BrewingController.php` :

```php
public function delete(Brewing $brewing)
{
    abort_unless($brewing->recipe_id === $this->recipe->id, 403);

    BrewingStep::where('brewing_id', $brewing->id)->delete();
    $brewing->delete();
}
```

`app/Livewire/RecipeDetailsController.php` :

```php
public function delete()
{
    $this->recipe->delete();
    return redirect()->route('recipes.index');
}
```

`resources/views/recipes/recipe.blade.php:274` : `wire:click="delete({{ $recipe }})"` → `wire:click="delete"`.

`app/Livewire/CompletedController.php` :

```php
public function delete()
{
    BrewingStep::where('brewing_id', $this->brewing->id)->delete();
    $this->brewing->delete();

    return redirect()->route('brewing.index', $this->recipe);
}
```

`resources/views/brewings/completed.blade.php:135` : `wire:click="delete({{ $brewing }})"` → `wire:click="delete"`.

`app/Livewire/NoteController.php` — `update(Brewing $brewing)` → `update()` (le paramètre était inutilisé ; la méthode travaille déjà sur `$this->brewing`).

- [ ] **Step 4: Vérifier que le test passe et lancer toute la suite**

```bash
DPHP vendor/bin/phpunit tests/Feature/LivewireActionGuardTest.php
DPHP vendor/bin/phpunit
```

Attendu : PASS partout.

- [ ] **Step 5: Commit**

```bash
git add app/Livewire resources/views tests/Feature/LivewireActionGuardTest.php
git commit -m "fix: garde-fous sur les actions Livewire à paramètre client (IDOR actions)"
```

---

### Task 6: Import BeerXML — pas de persistance fichier, transaction, erreurs gérées

**Files:**
- Modify: `app/Http/Controllers/FileController.php`
- Test: `tests/Feature/BeerXmlImportTest.php`

**Interfaces:**
- Consumes: factories (Task 2) pour les assertions.
- Produces: `FileController::store()` conserve sa route et sa signature HTTP (`POST /uploadFile`, champ `file`). `deleteFile()` et toute écriture disque disparaissent. Nouvelle méthode privée `importRecipe(array $recipe): Recipe`.

- [ ] **Step 1: Écrire le test qui échoue**

`tests/Feature/BeerXmlImportTest.php` :

```php
<?php

namespace Tests\Feature;

use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class BeerXmlImportTest extends TestCase
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

    private function upload(User $user, string $content, string $name = 'recipe.xml')
    {
        return $this->actingAs($user)->post(route('upload.store'), [
            'file' => UploadedFile::fake()->createWithContent($name, $content),
        ]);
    }

    public function test_valid_beerxml_creates_recipe_and_steps(): void
    {
        $user = User::factory()->withPersonalTeam()->create();

        $response = $this->upload($user, self::VALID_XML);

        $recipe = Recipe::firstWhere('name', 'Test IPA');
        $this->assertNotNull($recipe);
        $this->assertSame($user->id, $recipe->user_id);
        // 2 fermentables + 1 hop + 1 yeast + infuse + mash step + sparge + primary
        $this->assertCount(8, $recipe->steps);
        $response->assertRedirect(route('recipes.show', $recipe));
    }

    public function test_malformed_xml_is_rejected_without_creating_anything(): void
    {
        $user = User::factory()->withPersonalTeam()->create();

        $response = $this->upload($user, '<?xml version="1.0"?><RECIPES><RECIPE>');

        $response->assertSessionHasErrors('file');
        $this->assertSame(0, Recipe::count());
    }

    public function test_import_failure_mid_parse_rolls_back_the_recipe(): void
    {
        $user = User::factory()->withPersonalTeam()->create();
        // HOP sans USE : la création du step échoue APRÈS la création de la recipe
        $xml = str_replace('<USE>Boil</USE>', '', self::VALID_XML);

        $response = $this->upload($user, $xml);

        $response->assertSessionHasErrors('file');
        $this->assertSame(0, Recipe::count());
    }

    public function test_uploaded_file_is_not_persisted_to_public_files(): void
    {
        $user = User::factory()->withPersonalTeam()->create();

        $this->upload($user, self::VALID_XML, 'persisted-check.xml');

        $this->assertFileDoesNotExist(public_path('files/persisted-check.xml'));
    }
}
```

- [ ] **Step 2: Vérifier que le test échoue**

```bash
DPHP vendor/bin/phpunit tests/Feature/BeerXmlImportTest.php
```

Attendu : FAIL — `test_malformed_xml...` et `test_import_failure...` explosent en erreur 500 (pas d'erreur de session), `test_uploaded_file_is_not_persisted...` trouve le fichier dans `public/files/`. `test_valid_beerxml...` peut passer (comportement existant conservé).

- [ ] **Step 3: Réécrire FileController::store**

`app/Http/Controllers/FileController.php` — remplacer intégralement le contenu par :

```php
<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use App\Models\Step;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FileController extends Controller
{
    public function index()
    {
        return view('recipes.uploadFile');
    }

    public function store(Request $request)
    {
        if ($request->action == 'cancel') {
            return redirect()->route('recipes.index');
        }

        $request->validate([
            'file' => 'required|file|mimes:xml|max:2048',
        ]);

        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($request->file('file')->get(), 'SimpleXMLElement', LIBXML_NONET);
        libxml_clear_errors();

        if ($xml === false || ! isset($xml->RECIPE)) {
            return back()->withErrors(['file' => __('Invalid BeerXML file')]);
        }

        $arrayFile = json_decode(json_encode($xml), true);

        try {
            $recipe = DB::transaction(fn () => $this->importRecipe($arrayFile['RECIPE']));
        } catch (\Throwable $e) {
            Log::warning('BeerXML import failed: '.$e->getMessage());

            return back()->withErrors(['file' => __('Invalid BeerXML file')]);
        }

        return redirect()->route('recipes.show', ['recipe' => $recipe->id]);
    }

    private function importRecipe(array $recipe): Recipe
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
            'user_id' => Auth::user()->id,
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

Points d'attention :
- `move()`/`deleteFile()`/`Storage` disparaissent — le contenu est lu depuis le fichier temporaire d'upload (`->get()`), rien n'est écrit sur disque.
- `LIBXML_NONET` bloque tout accès réseau du parseur ; ne PAS ajouter `LIBXML_NOENT` (ça activerait la substitution d'entités).
- La signature `createStep` corrige les deux dépréciations PHP (`?int $time` explicite, plus de paramètre optionnel avant des requis).
- La logique d'import (structure BeerXML, cas single/multi) est **conservée à l'identique** — le durcissement fonctionnel viendra avec le service en phase 3.

- [ ] **Step 4: Vérifier que le test passe et lancer toute la suite**

```bash
DPHP vendor/bin/phpunit tests/Feature/BeerXmlImportTest.php
DPHP vendor/bin/phpunit
```

Attendu : PASS (4 tests) et suite complète verte.

- [ ] **Step 5: Nettoyer les fichiers déjà accumulés (déploiement)**

Ajouter une note au README ou au ticket de déploiement : sur le serveur de prod, vider `public/files/` (fichiers XML uploadés jamais supprimés par l'ancien code). Pas d'action locale ici — le dossier n'existe pas dans le dépôt.

- [ ] **Step 6: Commit**

```bash
git add app/Http/Controllers/FileController.php tests/Feature/BeerXmlImportTest.php
git commit -m "fix: import BeerXML sans persistance disque, transactionnel, erreurs gérées"
```

---

### Task 7: HomeController — scoping et propriété déclarée

**Files:**
- Modify: `app/Livewire/HomeController.php`
- Test: `tests/Feature/HomePageTest.php`

**Interfaces:**
- Consumes: factories (Task 2).
- Produces: dashboard scoped à l'utilisateur, plus aucune propriété dynamique dans `app/Livewire/`.

- [ ] **Step 1: Écrire le test qui échoue**

`tests/Feature/HomePageTest.php` :

```php
<?php

namespace Tests\Feature;

use App\Models\Brewing;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_own_in_progress_brewings_only(): void
    {
        $user = User::factory()->withPersonalTeam()->create();
        $ownBrewing = Brewing::factory()
            ->for(Recipe::factory()->for($user))
            ->create(['name' => 'MON-BRASSIN-EN-COURS', 'current_step' => 'mash']);
        $foreignBrewing = Brewing::factory()
            ->create(['name' => 'BRASSIN-ETRANGER', 'current_step' => 'mash']);

        $this->actingAs($user)
            ->get(route('alechemist.home'))
            ->assertOk()
            ->assertSee('MON-BRASSIN-EN-COURS')
            ->assertDontSee('BRASSIN-ETRANGER');
    }
}
```

- [ ] **Step 2: Vérifier le comportement du test**

```bash
DPHP vendor/bin/phpunit tests/Feature/HomePageTest.php
```

Attendu : PASS ou FAIL selon le rendu (le scoping actuel par `whereIn('recipe_id', ...)` est fonctionnellement correct ; le problème est le `Recipe::all()` en mémoire et la propriété dynamique). Si PASS : c'est un test de non-régression, continuer.

- [ ] **Step 3: Corriger HomeController**

`app/Livewire/HomeController.php` — déclarer `$progressBrewings` et scoper les requêtes en SQL :

```php
public $recipe_count, $recipes, $fermentRecipes, $fermentTime, $progressBrewings;

public function mount(): void
{
    $this->recipes = Recipe::where('user_id', Auth::id())->pluck('id');
    $this->fermentRecipes = Recipe::where('user_id', Auth::id())
        ->whereHas('brewing', function ($query) {
            $query->where('current_step', 'ferment');
        })->get();
    $this->progressBrewings = Brewing::whereIn('recipe_id', $this->recipes)
        ->where('current_step', '<>', 'ferment')
        ->where('current_step', '<>', 'completed')
        ->oldest('updated_at')
        ->get();
}
```

- [ ] **Step 4: Vérifier que le test passe et lancer toute la suite**

```bash
DPHP vendor/bin/phpunit tests/Feature/HomePageTest.php
DPHP vendor/bin/phpunit
```

Attendu : PASS partout.

- [ ] **Step 5: Commit**

```bash
git add app/Livewire/HomeController.php tests/Feature/HomePageTest.php
git commit -m "fix: dashboard scoped en SQL + propriété progressBrewings déclarée"
```

---

### Task 8: Suppression du code mort BrewingDetailsController

**Files:**
- Delete: `app/Livewire/BrewingDetailsController.php`

**Interfaces:**
- Consumes: rien — le composant n'est référencé dans aucune route (`routes/web.php`) ni aucune vue.

- [ ] **Step 1: Vérifier l'absence de référence**

```bash
grep -rn 'BrewingDetailsController' routes/ resources/ app/ --include='*.php' --include='*.blade.php' | grep -v 'app/Livewire/BrewingDetailsController.php'
```

Attendu : aucune sortie.

- [ ] **Step 2: Supprimer le fichier**

```bash
git rm app/Livewire/BrewingDetailsController.php
```

- [ ] **Step 3: Lancer toute la suite**

```bash
DPHP vendor/bin/phpunit
```

Attendu : PASS.

- [ ] **Step 4: Commit**

```bash
git commit -m "chore: suppression du composant mort BrewingDetailsController"
```

---

## Critères de sortie de phase

- Suite PHPUnit complète verte (stock Jetstream + 5 nouveaux fichiers de tests).
- Un utilisateur authentifié ne peut ni voir (403), ni modifier (403), ni supprimer (403) les recettes/brassins/étapes d'un autre.
- Un XML malformé ou incomplet produit une erreur de validation, jamais un 500 ni une recette partielle.
- Aucun fichier uploadé n'est écrit dans `public/`.
- Plus aucune propriété dynamique ni signature dépréciée dans `app/` (prérequis phase 2).
