<?php

namespace App\Repositories;

use App\Models\User;
use App\Enums\UserRole;

class UserRepository
{
    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function create(array $data): User
    {
        return User::create($data);
    }

    public function findById(string $id): ?User
    {
        return User::find($id);
    }

    public function getMentors(int $limit = 10): \Illuminate\Support\Collection
    {
        return User::where('role', UserRole::Mentor->value)
            ->orderByDesc('rating_score')
            ->limit($limit)
            ->get();
    }
}
