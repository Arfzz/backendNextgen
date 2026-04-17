<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Mentor;
use Illuminate\Http\Request;

class MentorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $mentors = Mentor::all();
        
        return response()->json([
            'success' => true,
            'message' => 'Daftar data Mentor',
            'data'    => $mentors
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_mentor' => 'required|string|max:255',
            'pendidikan'  => 'required|string|max:255',
            'awardee'     => 'required|string|max:255',
            'rating'      => 'required|numeric|min:0|max:5',
        ]);

        $mentor = Mentor::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Data Mentor berhasil disimpan',
            'data'    => $mentor
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $mentor = Mentor::find($id);

        if (!$mentor) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail data Mentor',
            'data'    => $mentor
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $mentor = Mentor::find($id);

        if (!$mentor) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        $validated = $request->validate([
            'nama_mentor' => 'required|string|max:255',
            'pendidikan'  => 'required|string|max:255',
            'awardee'     => 'required|string|max:255',
            'rating'      => 'required|numeric|min:0|max:5',
        ]);

        $mentor->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Data Mentor berhasil diubah',
            'data'    => $mentor
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $mentor = Mentor::find($id);

        if (!$mentor) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        $mentor->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data Mentor berhasil dihapus'
        ], 200);
    }
}
