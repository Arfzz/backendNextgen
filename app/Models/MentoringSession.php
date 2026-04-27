<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class MentoringSession extends Model
{
    protected $connection = 'pjblNextgen';

    protected $collection = 'mentoring_sessions';

    protected $fillable = [
        'class_id',
        'title',
        'session_date',
        'link',
    ];

    protected $casts = [
        'session_date' => 'datetime',
    ];
}
