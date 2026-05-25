<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Mentor;
use App\Models\Testimonial;
use Illuminate\Http\Request;

class TestimonialController extends Controller
{
    /**
     * Get approved testimonials (optionally filter by mentor_id).
     */
    public function index(Request $request)
    {
        $query = Testimonial::where('status', 'is_approved');

        if ($request->has('mentor_id') && $request->mentor_id) {
            $query->where('mentor_id', $request->mentor_id);
        }

        if ($request->has('show_mobile') && $request->show_mobile) {
            $query->where('show_mobile', true);
        }

        if ($request->has('show_web') && $request->show_web) {
            $query->where('show_web', true);
        }

        $testimonials = $query->orderBy('created_at', 'desc')->get();

        // Attach related data
        $testimonials->each(function ($testimonial) {
            $testimonial->user_name     = optional($testimonial->user)->name ?? 'Anonymous';
            $testimonial->mentor_name   = optional($testimonial->mentor)->nama_mentor ?? '-';
            $testimonial->paket_name    = optional($testimonial->paketBeasiswa)->nama_beasiswa ?? '-';
        });

        return response()->json([
            'success' => true,
            'message' => 'Daftar testimoni',
            'data'    => $testimonials,
        ], 200);
    }

    /**
     * Submit a new testimonial (peserta).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id'           => 'required|string',
            'paket_beasiswa_id' => 'required|string',
            'mentor_id'         => 'required|string',
            'content'           => 'required|string|min:10|max:1000',
            'rating'            => 'required|numeric|min:1|max:5',
        ]);

        $validated['status']      = 'pending';
        $validated['show_mobile'] = false;
        $validated['show_web']    = false;

        $testimonial = Testimonial::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Testimoni berhasil dikirim! Terima kasih atas ulasan Anda.',
            'data'    => $testimonial,
        ], 201);
    }

    /**
     * Get mentor detail with rating and testimonials.
     */
    public function mentorRating($mentorId)
    {
        $mentor = Mentor::find($mentorId);

        if (!$mentor) {
            return response()->json([
                'success' => false,
                'message' => 'Mentor tidak ditemukan',
            ], 404);
        }

        $testimonials = Testimonial::where('mentor_id', $mentorId)
            ->where('status', 'is_approved')
            ->orderBy('created_at', 'desc')
            ->get();

        $testimonials->each(function ($t) {
            $t->user_name = optional($t->user)->name ?? 'Anonymous';
        });

        $totalReviews = $testimonials->count();
        $avgRating    = $totalReviews > 0 ? round($testimonials->avg('rating'), 1) : ($mentor->rating ?? 5.0);

        return response()->json([
            'success' => true,
            'data'    => [
                'mentor'        => $mentor,
                'average_rating' => $avgRating,
                'total_reviews'  => $totalReviews,
                'testimonials'   => $testimonials,
            ],
        ], 200);
    }
}
