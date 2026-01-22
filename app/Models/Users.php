<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Users extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'first_name',
        'last_name',
        'meter_number',
        'phone_number',
        'email',
        'password',
        'last_login_at',
        'profile_image',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' =>'datetime',
        'otp_expires_at' => 'datetime'
    ];

    public function client()
    {
        return $this->hasOne(Clients::class, 'user_id'); 
    }

    public function reports()
    {
        return $this->hasMany(ProblemReport::class, 'client_id');
    }
    
    public function notifications()
    {
        return $this->hasMany(Notification::class)->latest();
    }
    public function billings()
    {
        return $this->hasMany(UserBilling::class, 'user_id');
    }




}
