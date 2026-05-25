<?php

namespace App\Http\Controllers;

use App\Models\PaketBeasiswa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');

        $query = User::query();
        if ($search) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
        }

        $users   = $query->get();
        $pakets  = PaketBeasiswa::orderBy('nama_beasiswa')->get();

        return view('users.index', compact('users', 'search', 'pakets'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'email'      => 'required|email|unique:pjblNextgen.users,email',
            'password'   => 'required|string|min:8',
            'role'       => ['required', 'string', Rule::in(['student', 'admin', 'mentor'])],
            'university' => 'nullable|string|max:255',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return redirect()->route('users.index')->with('success', 'User berhasil ditambahkan');
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'email'      => ['required', 'email', Rule::unique('pjblNextgen.users', 'email')->ignore($user->_id, '_id')],
            'password'   => 'nullable|string|min:8',
            'role'       => ['required', 'string', Rule::in(['student', 'admin', 'mentor'])],
            'university' => 'nullable|string|max:255',
        ]);

        if (! empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        // Only process beasiswa_diampu for student role
        $incomingRole = $validated['role'];
        if ($incomingRole === 'student') {
            $beasiswaDiampu = $request->input('beasiswa_diampu', []);
            // Filter out empty strings
            $validated['beasiswa_diampu'] = array_values(
                array_filter((array) $beasiswaDiampu, fn($v) => $v !== '')
            );
        } else {
            // Clear the field if role is not student
            $validated['beasiswa_diampu'] = [];
        }

        $user->update($validated);

        return redirect()->route('users.index')->with('success', 'User berhasil diperbarui');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('users.index')->with('success', 'User berhasil dihapus');
    }
}
