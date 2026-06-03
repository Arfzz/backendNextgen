<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Task extends Model
{
    protected $connection = 'pjblNextgen';

    protected $collection = 'tasks';

    protected $fillable = [
        'class_id',
        'paket_beasiswa',
        'mentor_id',
        'title',
        'description',
        'deadline_date',
        'file_url',         // optional attachment (guidebook, example essay, etc.)
    ];

    // deadline_date stored as string — no cast to avoid Carbon parse errors
    // from non-ISO date formats sent by Flutter
    protected $casts = [];
}
