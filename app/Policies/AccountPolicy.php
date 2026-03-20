<?php

namespace App\Policies;

use App\Models\Account;
use App\Models\User;

class AccountPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Account $account): bool
    {
        return $user->family_id === $account->family_id;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'member']);
    }

    public function update(User $user, Account $account): bool
    {
        if ($user->family_id !== $account->family_id) return false;
        return $user->isAdmin() || $user->id === $account->user_id;
    }

    public function delete(User $user, Account $account): bool
    {
        if ($user->family_id !== $account->family_id) return false;
        return $user->isAdmin();
    }
}
