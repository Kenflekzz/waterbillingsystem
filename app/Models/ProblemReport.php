<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProblemReport extends Model
{
    protected $fillable = [
        'client_id',
        'subject',
        'description',
        'image',
        'status',
        'is_read',
    ];

    public function client()
    {
        return $this->belongsTo(Clients::class, 'client_id');
    }
}
