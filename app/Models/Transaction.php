<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'account_id', 'type', 'amount', 'label',
        'balance_before', 'balance_after'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'type' => 'string',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}