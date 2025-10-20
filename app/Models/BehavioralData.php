<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BehavioralData extends Model
{
    protected $table = 'behavioral_data';
    protected $fillable = ['metric_name', 'value', 'meta'];
    protected $casts = [
        'meta' => 'array'
    ];
}

