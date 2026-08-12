<?php

namespace App\Policies;

use App\Models\Recipe;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class RecipePolicy
{
    public function view(User $user, Recipe $recipe): Response
    {
        return $user->id === $recipe->user_id
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    public function update(User $user, Recipe $recipe): Response
    {
        return $user->id === $recipe->user_id
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    public function delete(User $user, Recipe $recipe): Response
    {
        return $user->id === $recipe->user_id
            ? Response::allow()
            : Response::denyAsNotFound();
    }
}
