<?php

namespace App\Services;

use App\Models\Account;
use App\Models\User;
use App\Models\AccountUser;
use Illuminate\Support\Facades\DB;
use Exception;

class AccountService
{
    public function createAccount(array $data, User $owner)
    {
        if ($data['type'] === 'MINEUR' && empty($data['guardian_id'])) {
            throw new Exception('Un tuteur est obligatoire pour un compte mineur.');
        }

        if ($data['type'] === 'MINEUR') {
            $guardian = User::find($data['guardian_id']);
            if (!$guardian || !$guardian->isAdult()) {
                throw new Exception('Le tuteur doit être majeur.');
            }
            if (!$owner->isMinor()) {
                throw new Exception('Le titulaire d\'un compte mineur doit être mineur.');
            }
        }

        DB::beginTransaction();
        try {
            $account = Account::create([
                'rib' => $this->generateRib(),
                'type' => $data['type'],
                'balance' => $data['initial_balance'] ?? 0,
                'overdraft_limit' => ($data['type'] === 'COURANT') ? 500 : 0,
                'interest_rate' => ($data['type'] === 'EPARGNE') ? 3.5 : (($data['type'] === 'MINEUR') ? 2.0 : null),
                'monthly_fee' => ($data['type'] === 'COURANT') ? 50 : null,
                'status' => 'ACTIVE',
            ]);

            $account->owners()->attach($owner->id, [
                'accepted_closure' => false,
                'relation_type' => 'owner'
            ]);

            if ($data['type'] === 'MINEUR') {
                $account->owners()->attach($data['guardian_id'], [
                    'accepted_closure' => false,
                    'relation_type' => 'guardian'
                ]);
            }

            DB::commit();
            return $account->load('owners');
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function addCoOwner(Account $account, User $newOwner, User $currentUser)
    {
        if ($account->status !== 'ACTIVE') {
            throw new Exception('Le compte n\'est pas actif.');
        }

        if ($account->type === 'MINEUR') {
            throw new Exception('Un compte mineur ne peut pas avoir de co-titulaire.');
        }

        if ($account->owners()->where('user_id', $newOwner->id)->exists()) {
            throw new Exception('Cet utilisateur est déjà propriétaire.');
        }

        $account->owners()->attach($newOwner->id, [
            'accepted_closure' => false,
            'relation_type' => 'owner'
        ]);

        return $account->fresh('owners');
    }

    public function removeCoOwner(Account $account, User $userToRemove, User $currentUser)
    {
        if ($account->status !== 'ACTIVE') {
            throw new Exception('Le compte n\'est pas actif.');
        }

        $owners = $account->owners;
        if ($owners->count() <= 1) {
            throw new Exception('Le compte doit avoir au moins un propriétaire.');
        }

        if (!$account->owners()->where('user_id', $userToRemove->id)->exists()) {
            throw new Exception('Cet utilisateur n\'est pas propriétaire.');
        }

        $account->owners()->detach($userToRemove->id);

        return $account->fresh('owners');
    }

    public function assignGuardian(Account $account, User $guardian)
    {
        if ($account->type !== 'MINEUR') {
            throw new Exception('Seul un compte mineur peut avoir un tuteur.');
        }

        if (!$guardian->isAdult()) {
            throw new Exception('Le tuteur doit être majeur.');
        }

        if ($account->guardians()->where('user_id', $guardian->id)->exists()) {
            throw new Exception('Cet utilisateur est déjà tuteur.');
        }

        $account->owners()->attach($guardian->id, [
            'accepted_closure' => false,
            'relation_type' => 'guardian'
        ]);

        return $account->fresh('owners');
    }

    public function convertToCourant(Account $account, User $user)
    {
        if ($account->type !== 'MINEUR') {
            throw new Exception('Seul un compte mineur peut être converti.');
        }

        $owners = $account->owners()->wherePivot('relation_type', 'owner')->get();
        $owner = $owners->first();

        if (!$owner || $owner->isMinor()) {
            throw new Exception('Le titulaire du compte doit être majeur pour convertir.');
        }

        $guardians = $account->guardians;
        if ($guardians->isEmpty()) {
            throw new Exception('Aucun tuteur trouvé pour ce compte.');
        }

        $account->type = 'COURANT';
        $account->interest_rate = null;
        $account->overdraft_limit = 500;
        $account->monthly_fee = 50;
        $account->save();

        return $account;
    }

    public function requestClosure(Account $account, User $user)
    {
        if ($account->type === 'MINEUR') {
            $guardian = $account->guardians->first();
            if (!$guardian || $guardian->id !== $user->id) {
                throw new Exception('Seul le tuteur peut demander la clôture d\'un compte mineur.');
            }
        }

        $pivot = AccountUser::where('account_id', $account->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$pivot) {
            throw new Exception('Vous n\'êtes pas propriétaire de ce compte.');
        }

        $pivot->accepted_closure = true;
        $pivot->save();

        $allAccepted = AccountUser::where('account_id', $account->id)
            ->where('accepted_closure', false)
            ->count() === 0;

        if ($allAccepted && $account->balance == 0) {
            $account->status = 'CLOSED';
            $account->save();
        }

        return $account;
    }

    private function generateRib()
    {
        return 'RIB-' . strtoupper(uniqid());
    }
}