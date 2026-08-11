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
