<?php

namespace App\Http\Controllers;

use App\Models\Testimonial;
use App\Models\Mentor;
use Illuminate\Http\Request;

class TestimonialController extends Controller
{
    /**
     * Display a listing of testimonials in the CMS dashboard.
     */
    public function index(Request $request)
    {
        $query = Testimonial::query();

        // Search by content or user name
        if ($request->has('search') && $request->search != '') {
            $query->where('content', 'regexp', '/' . preg_quote($request->search) . '/i');
        }

        // Filter by status
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        $testimonials = $query->orderBy('created_at', 'desc')->paginate(20);

        // Eager-load related data manually (MongoDB)
        $testimonials->each(function ($testimonial) {
            $testimonial->user_data   = $testimonial->user;
            $testimonial->mentor_data = $testimonial->mentor;
        });

        $search = $request->search;
        $status = $request->status;

        return view('testimonial.index', compact('testimonials', 'search', 'status'));
    }

    /**
     * Update testimonial status, show_mobile, show_web fields.
     */
    public function update(Request $request, $id)
    {
        $testimonial = Testimonial::findOrFail($id);

        $validated = $request->validate([
            'status'      => 'required|in:pending,is_approved,rejected',
        ]);

        // If status is rejected, delete the testimonial and recalculate mentor rating
        if ($validated['status'] === 'rejected') {
            $mentorId = $testimonial->mentor_id;
            $testimonial->delete();
            $this->recalculateMentorRating($mentorId);

            return redirect()->route('testimonial.index')->with('success', 'Testimoni berhasil dihapus (rejected).');
        }

        $testimonial->status = $validated['status'];
        $testimonial->save();

        // Recalculate mentor rating whenever testimonial is approved
        if ($validated['status'] === 'is_approved') {
            $this->recalculateMentorRating($testimonial->mentor_id);
        }

        return redirect()->route('testimonial.index')->with('success', 'Testimoni berhasil diperbarui!');
    }

    /**
     * Remove the specified testimonial.
     */
    public function destroy($id)
    {
        $testimonial = Testimonial::findOrFail($id);
        $mentorId = $testimonial->mentor_id;
        $testimonial->delete();

        $this->recalculateMentorRating($mentorId);

        return redirect()->route('testimonial.index')->with('success', 'Testimoni berhasil dihapus!');
    }

    /**
     * Recalculate and update mentor's average rating.
     */
    private function recalculateMentorRating($mentorId)
    {
        if (!$mentorId) return;

        $mentor = Mentor::find($mentorId);
        if (!$mentor) return;

        // Only count approved testimonials for rating
        $testimonials = Testimonial::where('mentor_id', $mentorId)
            ->where('status', 'is_approved')
            ->get();

        if ($testimonials->count() > 0) {
            $avgRating = $testimonials->avg('rating');
            $mentor->rating = round($avgRating, 1);
        } else {
            // Default rating when no approved testimonials
            $mentor->rating = 5.0;
        }

        $mentor->save();
    }
}
