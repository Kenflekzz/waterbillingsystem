<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Billings extends Model
{
    Use HasFactory;
    protected $fillable = [
        'client_id',
        'billing_id',
        'billing_date',
        'due_date',
        'reading_date',
        'previous_reading',
        'present_reading',
        'consumed',
        'current_bill',
        'total_penalty',
        'maintenance_cost',
        'total_amount',
        'installation_fee',
    ];

    public function client(){
        return $this->belongsTo(Clients::class);   
    }

    public function payment()
{
    return $this->hasOne(Payments::class);
}

}
