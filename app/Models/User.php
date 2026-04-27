<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
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
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'role'              => UserRole::class,
            'rating_score'      => 'float',
            'students_passed'   => 'integer',
        ];
    }
}
