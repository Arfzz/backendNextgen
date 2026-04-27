<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Package extends Model
{
    protected $connection = 'pjblNextgen';

    protected $collection = 'packages';

    protected $fillable = [
        'title',
        'deadline_date',
        'price',
        'old_price',
        'features',
        'cover_image',
    ];

    protected $casts = [
        'deadline_date' => 'date',
        'price'         => 'float',
        'old_price'     => 'float',
        'features'      => 'array',
    ];
}
