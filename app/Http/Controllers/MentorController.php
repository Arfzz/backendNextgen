<?php

namespace App\Http\Controllers;

use App\Models\Mentor;
use App\Models\PaketBeasiswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class MentorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Mentor::query();

        if ($request->has('search') && $request->search != '') {
            $query->where('nama_mentor', 'regexp', '/' . preg_quote($request->search) . '/i');
        }

        $mentors = $query->get();
        $search = $request->search;
        $paketBeasiswa = PaketBeasiswa::orderBy('nama_beasiswa')->get(['nama_beasiswa']);

        return view('mentor.index', compact('mentors', 'search', 'paketBeasiswa'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_mentor' => 'required|string|max:255',
            'pendidikan' => 'required|string|max:255',
            'awardee' => 'required|array|min:1',
            'awardee.*' => 'required|string|max:255',
            'username' => 'required|string|max:100|unique:pjblNextgen.mentors,username',
            'email' => 'required|email|max:255|unique:pjblNextgen.mentors,email',
            'password' => 'required|string|min:8',
            'beasiswa_diampu' => 'nullable|array',
            'beasiswa_diampu.*' => 'nullable|string|max:255',
            'profile_picture' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        // Hash password
        $validated['password'] = Hash::make($validated['password']);

        // Handle profile picture upload
        if ($request->hasFile('profile_picture')) {
            $path = $request->file('profile_picture')->store('mentors', 'public');
            $validated['profile_picture'] = $path;
        }

        // Filter out empty beasiswa_diampu values
        if (isset($validated['beasiswa_diampu'])) {
            $validated['beasiswa_diampu'] = array_values(array_filter($validated['beasiswa_diampu']));
        }

        Mentor::create($validated);

        return redirect()->route('mentor.index')->with('success', 'Data Mentor berhasil ditambahkan!');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $mentor = Mentor::findOrFail($id);

        $validated = $request->validate([
            'nama_mentor' => 'required|string|max:255',
            'pendidikan' => 'required|string|max:255',
            'awardee' => 'required|array|min:1',
            'awardee.*' => 'required|string|max:255',
            'username' => 'required|string|max:100|unique:pjblNextgen.mentors,username,' . $id . ',_id',
            'email' => 'required|email|max:255|unique:pjblNextgen.mentors,email,' . $id . ',_id',
            'password' => 'nullable|string|min:8',
            'beasiswa_diampu' => 'nullable|array',
            'beasiswa_diampu.*' => 'nullable|string|max:255',
            'profile_picture' => 'nullable|image|mimes:jpg,jpeg,png,webp',
        ]);

        // Only update password if a new one is provided
        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        // Handle profile picture upload
        if ($request->hasFile('profile_picture')) {
            // Delete old picture if exists
            if ($mentor->profile_picture) {
                Storage::disk('public')->delete($mentor->profile_picture);
            }
            $path = $request->file('profile_picture')->store('mentors', 'public');
            $validated['profile_picture'] = $path;
        }

        // Filter out empty beasiswa_diampu values
        if (isset($validated['beasiswa_diampu'])) {
            $validated['beasiswa_diampu'] = array_values(array_filter($validated['beasiswa_diampu']));
        }

        $mentor->update($validated);

        return redirect()->route('mentor.index')->with('success', 'Data Mentor berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $mentor = Mentor::findOrFail($id);

        // Delete profile picture if exists
        if ($mentor->profile_picture) {
            Storage::disk('public')->delete($mentor->profile_picture);
        }

        $mentor->delete();

        return redirect()->route('mentor.index')->with('success', 'Data Mentor berhasil dihapus!');
    }
}
