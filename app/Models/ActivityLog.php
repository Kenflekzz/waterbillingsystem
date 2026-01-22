<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Clients;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'activity',
        'details',
        'seen',
    ];

    public function client()
    {
        return $this->belongsTo(Clients::class, 'user_id' , 'user_id');
    }
}
