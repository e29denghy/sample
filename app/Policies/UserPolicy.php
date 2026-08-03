<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function update(User $currentUser, User $user): bool
    {
        return $currentUser->is($user);
    }

    public function destroy(User $currentUser, User $user): bool
    {
        return (bool) $currentUser->is_admin && ! $currentUser->is($user);
    }
}
