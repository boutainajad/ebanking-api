<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'nom',
        'prenom',
        'date_naissance',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'date_naissance' => 'date',
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

    public function ownedAccounts()
    {
        return $this->belongsToMany(Account::class, 'account_user')
                    ->wherePivot('relation_type', 'owner')
                    ->withPivot('accepted_closure')
                    ->withTimestamps();
    }

    public function guardianAccounts()
    {
        return $this->belongsToMany(Account::class, 'account_user')
                    ->wherePivot('relation_type', 'guardian')
                    ->withPivot('accepted_closure')
                    ->withTimestamps();
    }

    public function initiatedTransfers()
    {
        return $this->hasMany(Transfer::class, 'initiated_by');
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isAdult()
    {
        return $this->date_naissance->age >= 18;
    }

    public function isMinor()
    {
        return $this->date_naissance->age < 18;
    }
}