<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\FileUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function __construct(private readonly FileUploadService $fileService) {}

    /**
     * GET /api/v1/student/profile
     * Return the authenticated student's own profile.
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        // Normalize beasiswa_diampu — MongoDB may store it as a JSON-encoded string
        $rawBeasiswa = $user->getRawOriginal('beasiswa_diampu') ?? $user->beasiswa_diampu ?? [];
        if (is_string($rawBeasiswa) && str_starts_with(trim($rawBeasiswa), '[')) {
            $decoded = json_decode($rawBeasiswa, true);
            $rawBeasiswa = is_array($decoded) ? $decoded : [];
        }
        if (! is_array($rawBeasiswa)) {
            $rawBeasiswa = $rawBeasiswa ? [$rawBeasiswa] : [];
        }

        return response()->json([
            'id'              => (string) $user->_id,
            'name'            => $user->name,
            'email'           => $user->email,
            'university'      => $user->university ?? '',
            'profile_picture' => \App\Http\Resources\UserResource::resolveUrl($user->profile_picture),
            'beasiswa_diampu' => array_values($rawBeasiswa),
            'progress_percentage' => $user->progress_percentage ?? 0,
        ]);
    }

    /**
     * PUT /api/v1/student/profile
     * Update student profile fields: name, university, password, profile_picture.
     */
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'             => ['sometimes', 'string', 'max:255'],
            'university'       => ['sometimes', 'nullable', 'string', 'max:255'],
            'beasiswa_diampu'  => ['sometimes', 'array'],
            'beasiswa_diampu.*'=> ['string'],
            'password'         => ['sometimes', 'string', 'min:8', 'confirmed'],
            'profile_picture'  => ['sometimes', 'image', 'max:2048'], // Max 2MB
        ]);

        $user = $request->user();

        $updateData = [];

        if (isset($validated['name'])) {
            $updateData['name'] = $validated['name'];
        }
        if (array_key_exists('university', $validated)) {
            $updateData['university'] = $validated['university'];
        }
        if (isset($validated['beasiswa_diampu'])) {
            $updateData['beasiswa_diampu'] = $validated['beasiswa_diampu'];
        }
        if (isset($validated['password'])) {
            $updateData['password'] = \Illuminate\Support\Facades\Hash::make($validated['password']);
        }
        if ($request->hasFile('profile_picture')) {
            $updateData['profile_picture'] = $this->fileService->upload($request->file('profile_picture'), 'avatars');
        }

        if (!empty($updateData)) {
            $user->update($updateData);
        }

        return response()->json([
            'message'         => 'Profile updated successfully.',
            'id'              => (string) $user->_id,
            'name'            => $user->name,
            'university'      => $user->university ?? '',
            'beasiswa_diampu' => $user->fresh()->beasiswa_diampu ?? [],
            'profile_picture' => \App\Http\Resources\UserResource::resolveUrl($user->profile_picture),
        ]);
    }
}
