<?php

namespace App\Models;

use App\Enums\SubmissionStatus;
use MongoDB\Laravel\Eloquent\Model;

class TaskSubmission extends Model
{
    protected $connection = 'pjblNextgen';

    protected $collection = 'task_submissions';

    public $timestamps = false;

    protected $fillable = [
        'task_id',
        'student_id',
        'file_url',
        'status',
        'score',
        'feedback',
        'submitted_at',
        'revision_history',  // array of {file_url, submitted_at, feedback}
        'is_completed',      // true = graded/done, false = can still revise
    ];

    protected $casts = [
        'status'           => SubmissionStatus::class,
        'score'            => 'integer',
        'submitted_at'     => 'datetime',
        'revision_history' => 'array',
        'is_completed'     => 'boolean',
    ];
}
