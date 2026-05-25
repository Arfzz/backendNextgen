<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Notification extends Model
{
    protected $connection = 'pjblNextgen';
    protected $collection = 'notifications';

    protected $fillable = [
        'user_id',
        'title',
        'body',
        'is_read',
        'type',         // task_deadline | new_message | submission_graded | general
        'reference_id', // ID of the related entity (task_id, message_id, etc.)
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];
}
