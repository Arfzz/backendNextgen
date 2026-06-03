<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Services\FileUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function __construct(private readonly FileUploadService $fileService) {}

    /**
     * GET /api/v1/mentor/profile
     * Return the authenticated mentor's own profile.
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'id'              => (string) $user->_id,
            'name'            => $user->nama_mentor ?? $user->name,
            'email'           => $user->email,
            'university'      => $user->pendidikan ?? '',
            'profile_picture' => \App\Http\Resources\UserResource::resolveUrl($user->profile_picture),
            'beasiswa_diampu' => $user->beasiswa_diampu ?? [],
        ]);
    }

    /**
     * PUT /api/v1/mentor/profile
     * Update mentor profile fields: nama_mentor, pendidikan, password, profile_picture.
     */
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'             => ['sometimes', 'string', 'max:255'],
            'university'       => ['sometimes', 'nullable', 'string', 'max:255'],
            'password'         => ['sometimes', 'string', 'min:8', 'confirmed'],
            'profile_picture'  => ['sometimes', 'image', 'max:2048'], // Max 2MB
        ]);

        $user = $request->user();

        $updateData = [];

        if (isset($validated['name'])) {
            $updateData['nama_mentor'] = $validated['name'];
            $updateData['name'] = $validated['name']; // Keep both in sync if they exist
        }
        if (array_key_exists('university', $validated)) {
            $updateData['pendidikan'] = $validated['university'];
            $updateData['university'] = $validated['university'];
        }
        if (isset($validated['password'])) {
            $updateData['password'] = \Illuminate\Support\Facades\Hash::make($validated['password']);
        }
        if ($request->hasFile('profile_picture')) {
            $updateData['profile_picture'] = $this->fileService->upload($request->file('profile_picture'), 'mentors');
        }

        if (!empty($updateData)) {
            $user->update($updateData);
        }

        return response()->json([
            'message'         => 'Profile updated successfully.',
            'id'              => (string) $user->_id,
            'name'            => $user->nama_mentor ?? $user->name,
            'university'      => $user->pendidikan ?? '',
            'profile_picture' => \App\Http\Resources\UserResource::resolveUrl($user->profile_picture),
        ]);
    }
}
