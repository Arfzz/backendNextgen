<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(private readonly AuthService $authService) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register($request);

        return response()->json([
            'message' => 'Registration successful.',
            'token'   => $result['token'],
            'user'    => new UserResource($result['user']),
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login($request);

        $userResource = isset($result['is_mentor']) && $result['is_mentor']
            ? new \App\Http\Resources\MentorResource($result['user'])
            : new UserResource($result['user']);

        return response()->json([
            'message' => 'Login successful.',
            'token'   => $result['token'],
            'user'    => $userResource,
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user instanceof \App\Models\Mentor) {
            return response()->json(new \App\Http\Resources\MentorResource($user));
        }
        return response()->json(new UserResource($user));
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully.']);
    }
}
