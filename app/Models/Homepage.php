<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Homepage extends Model
{
    use HasFactory;

    protected $fillable = [
        'hero_title',
        'hero_subtitle',
        'announcement_image',
        'announcement_heading',
        'announcement_text',
        'announcement_items',
        'advisories',
        'connect_title',
        'connect_images',
        'footer_address',
        'footer_contact',
        'footer_email',
    ];

    protected $casts = [
        'announcement_items' => 'array',
        'advisories' => 'array',
        'connect_images' => 'array',
    ];
}

