<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use App\Models\Brewing;
use App\Models\Recipe;
use App\Policies\BrewingPolicy;
use App\Policies\RecipePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Recipe::class => RecipePolicy::class,
        Brewing::class => BrewingPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
