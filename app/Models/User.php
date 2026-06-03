<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use App\Traits\HasApiTokens;
use MongoDB\Laravel\Eloquent\Model;

class User extends Model implements \Illuminate\Contracts\Auth\Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;
    use \Illuminate\Auth\Authenticatable;

    /** @use HasFactory<UserFactory> */
    protected static string $factory = UserFactory::class;

    protected $connection = 'pjblNextgen';

    protected $collection = 'users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'university',
        'profile_picture',
        'device_token',
        'rating_score',
        'students_passed',
        'beasiswa_diampu',
        'progress_percentage',
        // Graduation flow
        'graduation_proof_url',
        'graduation_status',      // null | 'pending' | 'lulus' | 'gagal'
        'graduation_notified',    // bool: false = belum popup
        'graduated_beasiswa',     // nama beasiswa yg sudah lulus (untuk popup)
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at'    => 'datetime',
            'password'             => 'hashed',
            'role'                 => UserRole::class,
            'rating_score'         => 'float',
            'students_passed'      => 'integer',
            'beasiswa_diampu'      => 'array',
            'progress_percentage'  => 'integer',
        ];
    }
}
