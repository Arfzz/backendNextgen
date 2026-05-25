<?php

namespace App\Http\Controllers;

use App\Models\ClassMember;
use App\Models\Kelas;
use App\Models\Mentor;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class TestimonialFormController extends Controller
{
    /**
     * Step 1: Authenticate peserta with email + password.
     * Returns their class(es) and linked mentor(s).
     */
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        // Find user by email
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau password salah.',
            ], 401);
        }

        // Find all classes the user belongs to as a student
        $memberships = ClassMember::where('student_id', (string) $user->_id)->get();

        if ($memberships->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda belum terdaftar di kelas manapun.',
            ], 403);
        }

        // Resolve each class → mentor
        $classes = [];
        foreach ($memberships as $membership) {
            $kelas = Kelas::find($membership->class_id);
            if (!$kelas) continue;

            $mentor = Mentor::find($kelas->mentor_id);
            if (!$mentor) continue;

            $classes[] = [
                'class_id'    => (string) $kelas->_id,
                'class_name'  => $kelas->name,
                'mentor_id'   => (string) $mentor->_id,
                'mentor_name' => $mentor->nama_mentor,
                'rating'      => $mentor->rating ?? 5.0,
            ];
        }

        if (empty($classes)) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada mentor yang terhubung dengan akun Anda.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'user' => [
                'id'   => (string) $user->_id,
                'name' => $user->name,
            ],
            'classes' => $classes,
        ]);
    }

    /**
     * Step 2: Submit the testimonial.
     */
    public function submit(Request $request)
    {
        $request->validate([
            'user_id'   => 'required|string',
            'mentor_id' => 'required|string',
            'rating'    => 'required|numeric|min:1|max:5',
            'content'   => 'required|string|min:10|max:1000',
        ]);

        // Prevent duplicate testimonial for same user+mentor
        $existing = Testimonial::where('user_id', $request->user_id)
            ->where('mentor_id', $request->mentor_id)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah pernah memberikan testimoni untuk mentor ini.',
            ], 422);
        }

        Testimonial::create([
            'user_id'   => $request->user_id,
            'mentor_id' => $request->mentor_id,
            'rating'    => (float) $request->rating,
            'content'   => $request->content,
            'status'    => 'pending',
            'show_mobile' => false,
            'show_web'    => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Testimoni berhasil dikirim! Terima kasih atas ulasan Anda.',
        ]);
    }
}
