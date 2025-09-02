<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Users extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */

    protected $table = 'users';
    protected $fillable = [
        'first_name',
        'last_name',
        'meter_number',
        'phone_number',
        'email',
        'password',
    ];

     public function getAuthIdentifierName()
    {
        return 'meter_number';
    }

    public function getAuthIdentifier()
    {
        return $this->getKey();
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}
