<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Transfer;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Exception;

class TransferService
{
    private $dailyLimit = 10000;

    public function initiateTransfer(array $data, $initiator)
    {
        $fromAccount = Account::findOrFail($data['from_account_id']);
        $toAccount = Account::findOrFail($data['to_account_id']);

        if (!$this->canTransfer($fromAccount, $initiator)) {
            throw new Exception('Vous n\'êtes pas autorisé à effectuer ce virement.');
        }

        if ($fromAccount->status !== 'ACTIVE') {
            throw new Exception('Le compte source est bloqué ou clôturé.');
        }

        if ($fromAccount->id === $toAccount->id) {
            throw new Exception('Virement vers le même compte interdit.');
        }

        $dailyTotal = Transfer::where('from_account_id', $fromAccount->id)
            ->whereDate('created_at', today())
            ->where('status', 'COMPLETED')
            ->sum('amount');

        if ($dailyTotal + $data['amount'] > $this->dailyLimit) {
            throw new Exception('Limite journalière dépassée (10 000 MAD).');
        }

        if (!$fromAccount->canWithdraw($data['amount'])) {
            throw new Exception('Solde insuffisant.');
        }

        if (in_array($fromAccount->type, ['EPARGNE', 'MINEUR'])) {
            $monthlyLimit = ($fromAccount->type === 'EPARGNE') ? 3 : 2;
            $monthlyCount = Transfer::where('from_account_id', $fromAccount->id)
                ->whereMonth('created_at', now()->month)
                ->where('status', 'COMPLETED')
                ->count();

            if ($monthlyCount >= $monthlyLimit) {
                throw new Exception('Nombre maximal de retraits mensuels atteint.');
            }
        }

        $transfer = Transfer::create([
            'from_account_id' => $fromAccount->id,
            'to_account_id' => $toAccount->id,
            'initiated_by' => $initiator->id,
            'amount' => $data['amount'],
            'status' => 'PENDING',
        ]);

        $this->executeTransfer($transfer);

        return $transfer;
    }

    private function executeTransfer(Transfer $transfer)
    {
        DB::beginTransaction();
        try {
            $fromAccount = $transfer->fromAccount;
            $toAccount = $transfer->toAccount;

            $balanceBeforeFrom = $fromAccount->balance;
            $fromAccount->balance -= $transfer->amount;
            $fromAccount->save();

            $balanceBeforeTo = $toAccount->balance;
            $toAccount->balance += $transfer->amount;
            $toAccount->save();

            Transaction::create([
                'account_id' => $fromAccount->id,
                'type' => 'DEBIT',
                'amount' => $transfer->amount,
                'balance_before' => $balanceBeforeFrom,
                'balance_after' => $fromAccount->balance,
                'label' => 'Virement vers compte ' . $toAccount->rib,
                'reference' => $transfer->id,
            ]);

            Transaction::create([
                'account_id' => $toAccount->id,
                'type' => 'CREDIT',
                'amount' => $transfer->amount,
                'balance_before' => $balanceBeforeTo,
                'balance_after' => $toAccount->balance,
                'label' => 'Virement depuis compte ' . $fromAccount->rib,
                'reference' => $transfer->id,
            ]);

            if (in_array($fromAccount->type, ['EPARGNE', 'MINEUR'])) {
                $fromAccount->incrementMonthlyWithdrawalCount();
            }

            $transfer->status = 'COMPLETED';
            $transfer->save();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $transfer->status = 'FAILED';
            $transfer->reason = $e->getMessage();
            $transfer->save();
            throw $e;
        }
    }

    private function canTransfer(Account $account, $user)
    {
        if ($account->type === 'MINEUR') {
            return $account->guardians()->where('user_id', $user->id)->exists();
        }
        return $account->owners()->where('user_id', $user->id)->exists();
    }
}