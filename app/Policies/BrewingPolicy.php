<?php

namespace App\Policies;

use App\Models\Brewing;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class BrewingPolicy
{
    public function view(User $user, Brewing $brewing): Response
    {
        return $user->id === $brewing->recipe?->user_id
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    public function update(User $user, Brewing $brewing): Response
    {
        return $user->id === $brewing->recipe?->user_id
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    public function delete(User $user, Brewing $brewing): Response
    {
        return $user->id === $brewing->recipe?->user_id
            ? Response::allow()
            : Response::denyAsNotFound();
    }
}
