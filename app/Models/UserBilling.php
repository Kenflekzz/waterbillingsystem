<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserBilling extends Model
{
    use HasFactory;

    protected $table = 'user_billing';

    protected $fillable = [
        'user_id',
        'bill_number',
        'amount_due',
        'amount_paid',
        'due_date',
        'payment_date',
        'status',
        'payment_method',
        'arrears',
        'current_bill',
        'penalty',
        'consumed',
        'total_amount',
    ];

    public function user()
    {
        return $this->belongsTo(Users::class, 'user_id');
    }
    public function payment()
    {
        return $this->hasOne(Payments::class, 'user_billing_id', 'id');
    }


}
