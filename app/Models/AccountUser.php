<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountUser extends Model
{
    use HasFactory;

    protected $table = 'account_user';

    protected $fillable = [
        'user_id',
        'account_id',
        'accepted_closure',
        'relation_type',
    ];

    protected $casts = [
        'accepted_closure' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}