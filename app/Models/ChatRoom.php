<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class ChatRoom extends Model
{
    protected $connection = 'pjblNextgen';
    protected $collection = 'chat_rooms';

    protected $fillable = [
        'type',
        'beasiswa_name',
        'name',
        'participants',
        'last_message',
        'last_message_at',
    ];

    // ⚠️  Do NOT cast 'participants' to 'array' — Laravel-MongoDB would
    //     JSON-encode it to a string ("[]") instead of a BSON array.
    //     We handle the conversion manually in ChatRoomService.
    protected $casts = [
        'last_message_at' => 'datetime',
    ];
}
