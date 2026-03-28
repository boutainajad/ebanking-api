<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'from_account_id',
        'to_account_id',
        'initiated_by',
        'amount',
        'status',
        'reason',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function fromAccount()
    {
        return $this->belongsTo(Account::class, 'from_account_id');
    }

    public function toAccount()
    {
        return $this->belongsTo(Account::class, 'to_account_id');
    }

    public function initiator()
    {
        return $this->belongsTo(User::class, 'initiated_by');
    }

    public function isPending()
    {
        return $this->status === 'PENDING';
    }

    public function isCompleted()
    {
        return $this->status === 'COMPLETED';
    }

    public function isFailed()
    {
        return $this->status === 'FAILED';
    }
}