<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FlowReading extends Model
{
    protected $fillable = [
        'client_id',
        'iot_device_id',
        'flow_rate',
        'total_volume',
        'cubic_meter',
    ];

    public function client()
    {
        return $this->belongsTo(Clients::class);
    }

    public function iotDevice()
    {
        return $this->belongsTo(IotDevice::class);
    }
}