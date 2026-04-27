<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Checkpoint extends Model
{
    protected $connection = 'pjblNextgen';

    protected $collection = 'checkpoints';

    protected $fillable = [
        'class_id',
        'title',
        'schedule_date',
        'order_index',
    ];

    protected $casts = [
        'schedule_date' => 'date',
        'order_index'   => 'integer',
    ];
}
