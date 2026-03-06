<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IotDevice extends Model
{
    protected $fillable = [
        'client_id',
        'device_name',
        'ip_address',
        'port',
        'is_active',
    ];

    public function client()
    {
        return $this->belongsTo(Clients::class);
    }

    public function flowReadings()
    {
        return $this->hasMany(FlowReading::class);
    }

    // Helper to get WebSocket URL
    public function getWsUrlAttribute()
    {
        return "ws://{$this->ip_address}:{$this->port}";
    }
}