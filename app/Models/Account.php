<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'rib',
        'type',
        'status',
        'balance',
        'overdraft_limit',
        'interest_rate',
        'monthly_fee',
        'block_reason',
        'monthly_withdrawal_count',
        'last_withdrawal_reset',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'overdraft_limit' => 'decimal:2',
        'interest_rate' => 'decimal:2',
        'monthly_fee' => 'decimal:2',
        'last_withdrawal_reset' => 'date',
    ];

    public function accountUsers()
    {
        return $this->hasMany(AccountUser::class);
    }

    public function owners()
    {
        return $this->belongsToMany(User::class, 'account_user')
                    ->wherePivot('relation_type', 'owner')
                    ->withPivot('accepted_closure')
                    ->withTimestamps();
    }

    public function guardians()
    {
        return $this->belongsToMany(User::class, 'account_user')
                    ->wherePivot('relation_type', 'guardian')
                    ->withPivot('accepted_closure')
                    ->withTimestamps();
    }

    public function transfersFrom()
    {
        return $this->hasMany(Transfer::class, 'from_account_id');
    }

    public function transfersTo()
    {
        return $this->hasMany(Transfer::class, 'to_account_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function isActive()
    {
        return $this->status === 'ACTIVE';
    }

    public function isBlocked()
    {
        return $this->status === 'BLOCKED';
    }

    public function isClosed()
    {
        return $this->status === 'CLOSED';
    }

    public function canWithdraw($amount)
    {
        $availableBalance = $this->balance;
        if ($this->type === 'COURANT') {
            $availableBalance += $this->overdraft_limit;
        }
        return $availableBalance >= $amount;
    }

    public function incrementMonthlyWithdrawalCount()
    {
        $this->monthly_withdrawal_count++;
        $this->save();
    }

    public function resetMonthlyWithdrawalCount()
    {
        $this->monthly_withdrawal_count = 0;
        $this->last_withdrawal_reset = now();
        $this->save();
    }
}