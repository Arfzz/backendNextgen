<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

/**
 * Custom PersonalAccessToken that stores tokens in MongoDB
 * instead of SQL. Configured in AppServiceProvider via Sanctum::usePersonalAccessTokenModel().
 */
class PersonalAccessToken extends Model
{
    protected $connection = 'pjblNextgen';

    protected $collection = 'personal_access_tokens';

    protected $guarded = [];

    protected $hidden = ['token'];

    protected $casts = [
        'abilities'    => 'json',
        'last_used_at' => 'datetime',
        'expires_at'   => 'datetime',
    ];

    /**
     * Determine if the token has a given ability.
     */
    public function can(string $ability): bool
    {
        $abilities = $this->abilities ?? ['*'];

        return in_array('*', $abilities) || in_array($ability, $abilities);
    }

    /**
     * Determine if the token is missing a given ability.
     */
    public function cant(string $ability): bool
    {
        return ! $this->can($ability);
    }

    /**
     * Find a token instance by the raw token string.
     */
    public static function findToken(string $token): ?static
    {
        if (! str_contains($token, '|')) {
            return static::where('token', hash('sha256', $token))->first();
        }

        [$id, $tokenString] = explode('|', $token, 2);

        $instance = static::find($id);

        if ($instance && hash_equals($instance->token, hash('sha256', $tokenString))) {
            return $instance;
        }

        return null;
    }

    /**
     * Get the tokenable poly-morph owner model.
     */
    public function tokenable()
    {
        return $this->morphTo('tokenable');
    }
}
