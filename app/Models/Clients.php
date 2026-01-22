<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;


class Clients extends Authenticatable
{
    Use HasFactory;
    protected $fillable = [
        'group',
        'meter_no',
        'full_name',
        'barangay',
        'purok',
        'contact_number',
        'date_cut',
        'installation_date',
        'meter_series',
        'status',
        'user_id',
        'meter_status',
        'replacement_date',
    ];

    public function billings(){
        return $this->hasMany(Billings::class, 'client_id');
    }

     public function payments(){
        return $this->hasMany(Payments::class, 'client_id');
    }
    public function user()
    {
        return $this->belongsTo(Users::class, 'user_id');
    }

    

}
