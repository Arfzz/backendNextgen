<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function __construct(
        private readonly UserRepository $userRepository
    ) {}

    public function register(RegisterRequest $request): array
    {
        $user = $this->userRepository->create([
            'name'       => $request->name,
            'email'      => $request->email,
            'password'   => $request->password, // casted as 'hashed' in model
            'role'       => UserRole::Student->value,
            'university' => $request->university,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return ['user' => $user, 'token' => $token];
    }

    public function login(LoginRequest $request): array
    {
        $user = $this->userRepository->findByEmail($request->email);

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Revoke all old tokens for single-session login
        $user->tokens()->delete();
        $token = $user->createToken('auth_token')->plainTextToken;

        return ['user' => $user, 'token' => $token];
    }
}
