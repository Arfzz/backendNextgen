<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Task extends Model
{
    protected $connection = 'pjblNextgen';

    protected $collection = 'tasks';

    protected $fillable = [
        'class_id',
        'mentor_id',
        'title',
        'description',
        'deadline_date',
    ];

    protected $casts = [
        'deadline_date' => 'date',
    ];
}
