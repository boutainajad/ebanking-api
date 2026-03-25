<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $fillable = [
        'rbi', 'type', 'status', 'balance', 'overdraft_limit',
        'interest_rate', 'monthly_fee', 'block_reason'
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'overdraft_limit' => 'decimal:2',
        'interest_rate' => 'decimal:2',
        'monthly_fee' => 'decimal:2',
    ];

    public function accountUsers()
    {
        return $this->hasMany(AccountUser::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'account_user')
                    ->withPivot('accepted_closure', 'relation_type')
                    ->withTimestamps();
    }

    public function owners()
    {
        return $this->users()->wherePivot('relation_type', 'owner');
    }

    public function guardians()
    {
        return $this->users()->wherePivot('relation_type', 'guardian');
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
}