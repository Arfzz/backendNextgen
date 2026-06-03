<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Enums\UserRole;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Get all users and make the password visible in the response
        $users = User::all()->makeVisible('password');
        
        return response()->json([
            'success' => true,
            'message' => 'Daftar data Users',
            'data'    => $users
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'email'      => [
                'required',
                'email',
                function ($attribute, $value, $fail) {
                    if (\App\Models\User::where('email', $value)->exists() || \App\Models\Mentor::where('email', $value)->exists()) {
                        $fail('Email sudah terdaftar. Silakan gunakan email lain.');
                    }
                },
            ],
            'password'   => 'required|string|min:8',
            'role'       => ['required', 'string', Rule::in(['student', 'admin', 'mentor'])],
            'university' => 'nullable|string|max:255',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Data User berhasil disimpan',
            'data'    => $user->makeVisible('password')
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail data User',
            'data'    => $user->makeVisible('password')
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        $validated = $request->validate([
            'name'       => 'sometimes|string|max:255',
            'email'      => [
                'sometimes',
                'email',
                function ($attribute, $value, $fail) use ($user) {
                    if (\App\Models\User::where('email', $value)->where('_id', '!=', $user->_id)->exists() || \App\Models\Mentor::where('email', $value)->exists()) {
                        $fail('Email sudah terdaftar. Silakan gunakan email lain.');
                    }
                },
            ],
            'password'   => 'sometimes|string|min:8',
            'role'       => ['sometimes', 'string', Rule::in(['student', 'admin', 'mentor'])],
            'university' => 'nullable|string|max:255',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Data User berhasil diubah',
            'data'    => $user->makeVisible('password')
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data User berhasil dihapus'
        ], 200);
    }
}
