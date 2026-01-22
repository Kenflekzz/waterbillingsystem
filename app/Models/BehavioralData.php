<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BehavioralData extends Model
{
    protected $table = 'behavioral_data';

    // Allow all fields you want to mass-assign, including the timestamp
    protected $fillable = [
        'user_id',
        'metric_name',
        'barangay',
        'value',
        'meta',
        'created_at',   // <-- add this
        'updated_at'
    ];

    protected $casts = [
        'meta' => 'array',
        'created_at' => 'datetime', // keep casting as datetime
    ];
}