<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class MentoringSession extends Model
{
    protected $connection = 'pjblNextgen';

    protected $collection = 'mentoring_sessions';

    protected $fillable = [
        'class_id',
        'paket_beasiswa',   // nama beasiswa — relasi utama
        'mentor_id',        // ID mentor dari collection mentors
        'title',
        'session_date',
        'link',
    ];

    // session_date stored as string — no cast to avoid Carbon parse errors
    // from non-ISO date formats sent by Flutter (e.g. '5 Oktober 2025')
    protected $casts = [];
}
