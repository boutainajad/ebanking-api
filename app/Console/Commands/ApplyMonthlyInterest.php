<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Console\Command;

class ApplyMonthlyInterest extends Command
{
    protected $signature = 'bank:apply-interest';
    protected $description = 'Appliquer les intérêts mensuels aux comptes épargne et mineur';

    public function handle()
    {
        $accounts = Account::whereIn('type', ['EPARGNE', 'MINEUR'])
            ->where('status', 'ACTIVE')
            ->get();

        foreach ($accounts as $account) {
            $rate = $account->interest_rate ?? ($account->type === 'EPARGNE' ? 3.5 : 2.0);
            $interest = $account->balance * ($rate / 100 / 12);

            if ($interest > 0) {
                $balanceBefore = $account->balance;
                $account->balance += $interest;
                $account->save();

                Transaction::create([
                    'account_id' => $account->id,
                    'type' => 'INTEREST',
                    'amount' => $interest,
                    'balance_before' => $balanceBefore,
                    'balance_after' => $account->balance,
                    'label' => 'Intérêts mensuels',
                ]);
            }
        }

        $this->info('Intérêts mensuels appliqués avec succès.');
    }
}