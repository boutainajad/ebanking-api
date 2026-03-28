<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create([
            'nom' => 'Admin',
            'prenom' => 'System',
            'date_naissance' => '1980-01-01',
            'email' => 'admin@bank.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        $tuteur = User::create([
            'nom' => 'Tuteur',
            'prenom' => 'Parent',
            'date_naissance' => '1985-05-10',
            'email' => 'tuteur@example.com',
            'password' => Hash::make('password'),
            'role' => 'customer',
        ]);

        $mineur = User::create([
            'nom' => 'Mineur',
            'prenom' => 'Enfant',
            'date_naissance' => now()->subYears(15)->toDateString(),
            'email' => 'mineur@example.com',
            'password' => Hash::make('password'),
            'role' => 'customer',
        ]);

        $client = User::create([
            'nom' => 'Client',
            'prenom' => 'Normal',
            'date_naissance' => '1990-03-20',
            'email' => 'client@example.com',
            'password' => Hash::make('password'),
            'role' => 'customer',
        ]);

        $compteCourant = Account::create([
            'rib' => 'RIB-001',
            'type' => 'COURANT',
            'balance' => 5000,
            'overdraft_limit' => 500,
            'monthly_fee' => 50,
            'status' => 'ACTIVE',
        ]);
        $compteCourant->owners()->attach($client->id, ['accepted_closure' => false, 'relation_type' => 'owner']);

        $compteEpargne = Account::create([
            'rib' => 'RIB-002',
            'type' => 'EPARGNE',
            'balance' => 10000,
            'interest_rate' => 3.5,
            'status' => 'ACTIVE',
        ]);
        $compteEpargne->owners()->attach($client->id, ['accepted_closure' => false, 'relation_type' => 'owner']);

        $compteMineur = Account::create([
            'rib' => 'RIB-003',
            'type' => 'MINEUR',
            'balance' => 500,
            'interest_rate' => 2.0,
            'status' => 'ACTIVE',
        ]);
        $compteMineur->owners()->attach($mineur->id, ['accepted_closure' => false, 'relation_type' => 'owner']);
        $compteMineur->owners()->attach($tuteur->id, ['accepted_closure' => false, 'relation_type' => 'guardian']);

        Transaction::create([
            'account_id' => $compteCourant->id,
            'type' => 'CREDIT',
            'amount' => 5000,
            'balance_before' => 0,
            'balance_after' => 5000,
            'label' => 'Dépôt initial',
        ]);

        Transaction::create([
            'account_id' => $compteEpargne->id,
            'type' => 'CREDIT',
            'amount' => 10000,
            'balance_before' => 0,
            'balance_after' => 10000,
            'label' => 'Dépôt initial',
        ]);

        Transaction::create([
            'account_id' => $compteMineur->id,
            'type' => 'CREDIT',
            'amount' => 500,
            'balance_before' => 0,
            'balance_after' => 500,
            'label' => 'Dépôt initial',
        ]);
    }
}