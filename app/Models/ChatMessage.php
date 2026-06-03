<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class ChatMessage extends Model
{
    protected $connection = 'pjblNextgen';
    protected $collection = 'chat_messages';

    protected $fillable = [
        'room_id',
        'sender_id',
        'content',
        'is_read_by', // stored as real BSON array, NOT cast to 'array' (would JSON-encode)
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];
}
