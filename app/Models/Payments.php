<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payments extends Model
{
    Use HasFactory;
    protected $fillable =[
    'client_id',
    'billing_month',
    'current_bill',
    'arrears',
    'penalty',
    'total_amount',
    'status',
    'partial_payment_amount',
    'payment_type' 
    ];

    public function client(){
        return $this->belongsTo(Clients::class);
    }

    public function billing()
    {
        return $this->belongsTo(Billings::class);
    }
    public function getPaymentTypeLabelAttribute()
    {
        return match($this->payment_type ?? null) {
            'arrears_only' => 'Arrears Only',
            'full' => 'Full Payment',
            'partial' => 'Partial Payment',
            'partial_current' => 'Partial Current Bill',
            default => 'Unknown',
        };
    }


   
}
