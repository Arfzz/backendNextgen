<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Article extends Model
{
    protected $connection = 'pjblNextgen';

    protected $collection = 'articles';

    protected $fillable = [
        'title',
        'slug',
        'image_url',
        'content',
        'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];
}
