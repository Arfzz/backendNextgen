<?php

namespace App\Traits;

use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens as SanctumHasApiTokens;
use App\Models\NewAccessToken;

trait HasApiTokens
{
    use SanctumHasApiTokens;

    /**
     * Override createToken to use our custom NewAccessToken
     * which accepts App\Models\PersonalAccessToken (MongoDB)
     * instead of Laravel\Sanctum\PersonalAccessToken (SQL).
     */
    public function createToken(string $name, array $abilities = ['*'], \DateTimeInterface $expiresAt = null)
    {
        $plainTextToken = Str::random(40);

        $token = $this->tokens()->create([
            'name' => $name,
            'token' => hash('sha256', $plainTextToken),
            'abilities' => $abilities,
            'expires_at' => $expiresAt,
        ]);

        return new NewAccessToken($token, $token->getKey().'|'.$plainTextToken);
    }
}
