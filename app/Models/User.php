<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    protected $fillable = ['nom', 'prenom', 'email', 'password', 'date_naissance', 'role'];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'date_naissance' => 'date',
        'role' => 'string',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function accountUsers()
    {
        return $this->hasMany(AccountUser::class);
    }

    public function accounts()
    {
        return $this->belongsToMany(Account::class, 'account_user')
                    ->withPivot('accepted_closure', 'relation_type')
                    ->withTimestamps();
    }

    public function transfersInitiated()
    {
        return $this->hasMany(Transfer::class, 'initiated_by');
    }
}