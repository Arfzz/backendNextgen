<?php

namespace Database\Factories;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name'              => fake()->name(),
            'email'             => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password'          => static::$password ??= Hash::make('password'),
            'role'              => UserRole::Student->value,
            'university'        => fake()->randomElement(['UI', 'ITB', 'IPB', 'UGM', 'ITS']),
            'profile_picture'   => null,
            'device_token'      => null,
            'rating_score'      => null,
            'students_passed'   => null,
        ];
    }

    public function mentor(): static
    {
        return $this->state(fn (array $attributes) => [
            'role'            => UserRole::Mentor->value,
            'rating_score'    => round(fake()->randomFloat(1, 3.5, 5.0), 1),
            'students_passed' => fake()->numberBetween(10, 200),
        ]);
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::Admin->value,
        ]);
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
