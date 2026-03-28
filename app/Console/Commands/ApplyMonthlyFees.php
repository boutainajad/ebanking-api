<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Console\Command;

class ApplyMonthlyFees extends Command
{
    protected $signature = 'bank:apply-fees';
    protected $description = 'Appliquer les frais mensuels aux comptes courants';

    public function handle()
    {
        $accounts = Account::where('type', 'COURANT')
            ->where('status', 'ACTIVE')
            ->get();

        foreach ($accounts as $account) {
            $fee = $account->monthly_fee ?? 50;

            if ($account->balance >= $fee) {
                $balanceBefore = $account->balance;
                $account->balance -= $fee;
                $account->save();

                Transaction::create([
                    'account_id' => $account->id,
                    'type' => 'FEE',
                    'amount' => $fee,
                    'balance_before' => $balanceBefore,
                    'balance_after' => $account->balance,
                    'label' => 'Frais de tenue de compte',
                ]);
            } else {
                $account->status = 'BLOCKED';
                $account->block_reason = 'Solde insuffisant pour frais mensuels';
                $account->save();

                Transaction::create([
                    'account_id' => $account->id,
                    'type' => 'FEE_FAILED',
                    'amount' => $fee,
                    'balance_before' => $account->balance,
                    'balance_after' => $account->balance,
                    'label' => 'Frais mensuels impayés - compte bloqué',
                ]);
            }
        }

        $this->info('Frais mensuels appliqués avec succès.');
    }
}