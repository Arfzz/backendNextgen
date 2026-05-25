<?php

namespace App\Models;

use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Auth\Authenticatable;
use App\Traits\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use MongoDB\Laravel\Eloquent\Model;

class Mentor extends Model implements AuthenticatableContract
{
    use Authenticatable, HasApiTokens, Notifiable;
    protected $connection = 'pjblNextgen';

    protected $collection = 'mentors';

    protected $fillable = [
        'nama_mentor',
        'pendidikan',
        'awardee',
        'profile_picture',
        'username',
        'email',
        'password',
        'beasiswa_diampu',
        'rating',
    ];

    /**
     * Virtual attribute so that EnsureRole middleware works correctly.
     */
    public function getRoleAttribute()
    {
        return 'mentor';
    }

    protected $hidden = [
        'password',
    ];

    protected $attributes = [
        'rating' => 5.0,
    ];

    protected $casts = [
        'awardee'         => 'array',
        'beasiswa_diampu' => 'array',
        'rating'          => 'float',
    ];

    /**
     * Get all testimonials for this mentor.
     */
    public function testimonials()
    {
        return $this->hasMany(\App\Models\Testimonial::class, 'mentor_id');
    }
}
