<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Mentor extends Model
{
    protected $connection = 'pjblNextgen';

    protected $collection = 'mentors';

    protected $fillable = [
        'nama_mentor',
        'pendidikan',
        'awardee',
        'rating',
    ];

    protected $casts = [
        'rating' => 'float',
    ];
}
