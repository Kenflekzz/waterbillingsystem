<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAnnouncement extends Model
{
    protected $table = 'userannouncements';

    protected $fillable = [
        'title',
        'content',
        'scheduled_date',
        'announcement_image',
    ];
}
