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
            'email'       => [
                'required',
                'email',
                function ($attribute, $value, $fail) {
                    if (\App\Models\User::where('email', $value)->exists() || \App\Models\Mentor::where('email', $value)->exists()) {
                        $fail('Email sudah terdaftar. Silakan gunakan email lain.');
                    }
                },
            ],
            'password'    => 'required|string|min:8',
        ]);

        $validated['password'] = \Illuminate\Support\Facades\Hash::make($validated['password']);

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

        // Include rating info from approved testimonials
        $testimonials = \App\Models\Testimonial::where('mentor_id', (string) $mentor->_id)
            ->where('status', 'is_approved')
            ->orderBy('created_at', 'desc')
            ->get();

        $totalReviews = $testimonials->count();
        $avgRating    = $totalReviews > 0 ? round($testimonials->avg('rating'), 1) : ($mentor->rating ?? 5.0);

        $testimonials->each(function ($t) {
            $t->user_name = optional($t->user)->name ?? 'Anonymous';
        });

        $mentorData = $mentor->toArray();
        $mentorData['average_rating'] = $avgRating;
        $mentorData['total_reviews']  = $totalReviews;
        $mentorData['testimonials']   = $testimonials;

        return response()->json([
            'success' => true,
            'message' => 'Detail data Mentor',
            'data'    => $mentorData
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
            'email'       => [
                'sometimes',
                'email',
                function ($attribute, $value, $fail) use ($mentor) {
                    if (\App\Models\User::where('email', $value)->exists() || \App\Models\Mentor::where('email', $value)->where('_id', '!=', $mentor->_id)->exists()) {
                        $fail('Email sudah terdaftar. Silakan gunakan email lain.');
                    }
                },
            ],
            'password'    => 'sometimes|string|min:8',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = \Illuminate\Support\Facades\Hash::make($validated['password']);
        }

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
