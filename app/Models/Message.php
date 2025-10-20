<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = [
        'client_id',    // allow personal messages to be assigned
        'type',         // general / personal
        'title',
        'body',
        'status',       // if you’re updating this after SMS send
        'sms_api_message_id', // optional if you store it
    ];
}

