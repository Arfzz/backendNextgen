<?php

namespace App\Models;

use App\Enums\ClassMemberStatus;
use MongoDB\Laravel\Eloquent\Model;

class ClassMember extends Model
{
    protected $connection = 'pjblNextgen';

    protected $collection = 'class_members';

    public $timestamps = false;

    protected $fillable = [
        'class_id',
        'student_id',
        'progress_percentage',
        'fase_passed',
        'status',
        'joined_at',
    ];

    protected $casts = [
        'progress_percentage' => 'integer',
        'fase_passed'         => 'integer',
        'status'              => ClassMemberStatus::class,
        'joined_at'           => 'datetime',
    ];
}
