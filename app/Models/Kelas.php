<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Kelas extends Model
{
    protected $connection = 'pjblNextgen';

    protected $collection = 'classes';

    protected $fillable = [
        'mentor_id',
        'package_id',
        'name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
