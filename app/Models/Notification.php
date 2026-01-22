<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',          // the user who will receive the notification
        'type',             // "billing_issued" or "problem_resolved"
        'title',            // short title
        'message',          // body/details
        'data',             // extra JSON data (billing id, problem id, etc.)
        'is_read',          // 0 unread, 1 read
    ];

    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
    ];

    // Relationship: notification belongs to user
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
