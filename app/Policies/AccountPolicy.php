<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Account;

class AccountPolicy
{
    public function view(User $user, Account $account)
    {
        return $account->owners()->where('user_id', $user->id)->exists();
    }

    public function update(User $user, Account $account)
    {
        return $account->owners()->where('user_id', $user->id)->exists();
    }

    public function delete(User $user, Account $account)
    {
        return $account->owners()->where('user_id', $user->id)->exists();
    }
}