<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Testimonial extends Model
{
    protected $connection = 'pjblNextgen';

    protected $collection = 'testimonials';

    protected $fillable = [
        'user_id',
        'mentor_id',
        'content',
        'rating',
        'show_mobile',
        'show_web',
        'status',
    ];

    protected $casts = [
        'rating'      => 'float',
        'show_mobile' => 'boolean',
        'show_web'    => 'boolean',
    ];

    protected $attributes = [
        'show_mobile' => false,
        'show_web'    => false,
        'status'      => 'pending',
    ];

    /**
     * Get the user (peserta) who wrote this testimonial.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the mentor being reviewed.
     */
    public function mentor()
    {
        return $this->belongsTo(Mentor::class, 'mentor_id');
    }

}

