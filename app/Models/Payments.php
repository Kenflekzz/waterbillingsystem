<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payments extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'billing_month',
        'current_bill',
        'arrears',
        'penalty',
        'total_amount',
        'status',
        'partial_payment_amount',
        'payment_type',
        'user_billing_id',
        'reconnection_fee',
    ];

    public function client()
    {
        return $this->belongsTo(Clients::class);
    }

    public function billing()
    {
        return $this->belongsTo(Billings::class);
    }

    protected $appends = ['payment_type_label'];
    
    public function getPaymentTypeLabelAttribute()
    {
        return match($this->payment_type ?? null) {
            'arrears_only'   => 'Arrears Only',
            'full'           => 'Full Payment',
            'partial'        => 'Partial Payment',
            'partial_current'=> 'Partial Current Bill',
            'gcash'          => 'GCash',
            'N/A', null      => 'N/A',
            default          => ucfirst($this->payment_type),
        };
    }

    // 🔹 Auto-set payment_type if marked as "paid  via gcash"
    protected static function booted()
    {
        static::saving(function ($payment) {
            if ($payment->status === 'paid via gcash' && $payment->payment_type !== 'gcash') {
                $payment->payment_type = 'gcash';
            }

            // Optionally, if payment_type is gcash but status isn't set yet
            if ($payment->payment_type === 'gcash' && $payment->status !== 'paid via gcash') {
                $payment->status = 'paid via gcash';
            }
        });
    }
    public function userBilling()
    {
        return $this->belongsTo(UserBilling::class, 'user_billing_id', 'id');
    }

   public function getClientId(){

        return $this->belongsTo(Clients::class , 'client_id' , 'id');

     }



}
