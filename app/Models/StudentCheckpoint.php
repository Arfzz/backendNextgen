<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class StudentCheckpoint extends Model
{
    protected $connection = 'pjblNextgen';

    protected $collection = 'student_checkpoints';

    public $timestamps = false;

    protected $fillable = [
        'student_id',
        'checkpoint_id',
        'is_completed',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
    ];
}
