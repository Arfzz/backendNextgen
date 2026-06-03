<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Graduation;
use App\Models\Mentor;
use App\Models\PaketBeasiswa;
use App\Models\Testimonial;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GraduationController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────────
    // Helper: normalize beasiswa_diampu (JSON string or real array)
    // ─────────────────────────────────────────────────────────────────────────
    private function normalizeArray(mixed $value): array
    {
        if (is_array($value)) return $value;
        if (is_string($value) && str_starts_with(trim($value), '[')) {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [];
        }
        return [];
    }

    /**
     * POST /api/v1/student/graduation
     * Upload bukti kelulusan + submit testimoni.
     * Body (multipart): proof_file (file), rating (int 1-5), content (string), mentor_id (string)
     */
    public function submit(Request $request): JsonResponse
    {
        $user = $request->user();

        $request->validate([
            'proof_file' => ['required', 'file', 'max:10240'],
            'rating'     => ['required', 'integer', 'min:1', 'max:5'],
            'content'    => ['required', 'string', 'min:10', 'max:1000'],
            'mentor_id'  => ['required', 'string'],
        ]);

        // Resolve the current active beasiswa name.
        // NOTE: MongoDB + Eloquent 'array' cast can fail to decode old JSON-string documents.
        // We access the raw attribute first, then normalizeArray as a safety net.
        $rawBeasiswa    = $user->getRawOriginal('beasiswa_diampu') ?? $user->beasiswa_diampu ?? [];
        $beasiswaDiampu = $this->normalizeArray($rawBeasiswa);
        $beasiswaName   = $beasiswaDiampu[0] ?? null;

        if (! $beasiswaName) {
            return response()->json(['message' => 'Kamu tidak memiliki beasiswa aktif.'], 422);
        }

        // Prevent double submission for the SAME beasiswa (allow re-submit if 'gagal')
        $existing = Graduation::where('student_id', (string) $user->_id)
            ->where('beasiswa_name', $beasiswaName)
            ->first();

        if ($existing && in_array($existing->status, ['pending', 'lulus'])) {
            return response()->json(['message' => 'Anda sudah pernah mengirim bukti kelulusan untuk beasiswa ini.'], 422);
        }

        // Upload proof file
        $file    = $request->file('proof_file');
        $path    = $file->store('graduation_proofs', 'public');
        $fileUrl = url(Storage::url($path));

        // Find mentor
        $mentor = Mentor::find($request->mentor_id);
        if (! $mentor) {
            return response()->json(['message' => 'Mentor tidak ditemukan.'], 404);
        }

        // Create testimonial
        $testimonial = Testimonial::create([
            'user_id'     => (string) $user->_id,
            'mentor_id'   => (string) $mentor->_id,
            'rating'      => (int) $request->rating,
            'content'     => $request->content,
            'show_mobile' => false,
            'show_web'    => false,
            'status'      => 'pending',
        ]);

        // Upsert graduation record (creates or updates for this student+beasiswa)
        $graduationRecord = Graduation::updateOrCreate(
            [
                'student_id'    => (string) $user->_id,
                'beasiswa_name' => $beasiswaName,
            ],
            [
                'mentor_id'      => (string) $mentor->_id,
                'status'         => 'pending',
                'proof_url'      => $fileUrl,
                'notified'       => false,
                'testimonial_id' => (string) $testimonial->_id,
            ]
        );

        // Recalculate mentor rating
        $this->recalculateMentorRating((string) $mentor->_id);

        // Notify mentor that graduation proof has been submitted
        try {
            NotificationService::graduationSubmitted(
                (string) $mentor->_id,
                $user->name,
                $beasiswaName,
                (string) $user->_id
            );
            // Also notify mentor of the new testimonial
            NotificationService::newTestimonial(
                (string) $mentor->_id,
                $user->name,
                (int) $request->rating,
                $request->content,
                (string) $testimonial->_id
            );
        } catch (\Throwable) {}

        return response()->json([
            'message'            => 'Bukti kelulusan dan testimoni berhasil dikirim.',
            'graduation_status'  => 'pending',
            'testimonial_id'     => (string) $testimonial->_id,
        ], 201);
    }

    /**
     * GET /api/v1/student/graduation/status
     * Returns the latest unnotified graduation record (for popup).
     */
    public function notificationStatus(Request $request): JsonResponse
    {
        $user = $request->user();

        // Find the latest graduation record that has NOT been notified yet
        $record = Graduation::where('student_id', (string) $user->_id)
            ->where('notified', false)
            ->whereIn('status', ['lulus', 'gagal'])
            ->orderBy('updated_at', 'desc')
            ->first();

        if (! $record) {
            return response()->json([
                'graduation_status'  => null,
                'needs_popup'        => false,
                'graduated_beasiswa' => null,
            ]);
        }

        return response()->json([
            'graduation_status'  => $record->status,
            'needs_popup'        => true,
            'graduated_beasiswa' => $record->beasiswa_name,
        ]);
    }

    /**
     * POST /api/v1/student/graduation/mark-notified
     * Mark the latest unnotified graduation as seen.
     */
    public function markNotified(Request $request): JsonResponse
    {
        $user = $request->user();

        Graduation::where('student_id', (string) $user->_id)
            ->where('notified', false)
            ->whereIn('status', ['lulus', 'gagal'])
            ->orderBy('updated_at', 'desc')
            ->limit(1)
            ->each(fn ($r) => $r->update(['notified' => true]));

        return response()->json(['message' => 'OK']);
    }

    private function recalculateMentorRating(string $mentorId): void
    {
        $mentor = Mentor::find($mentorId);
        if (! $mentor) return;

        $testimonials = Testimonial::where('mentor_id', $mentorId)
            ->where('status', 'is_approved')
            ->get();

        $mentor->rating = $testimonials->count() > 0
            ? round($testimonials->avg('rating'), 1)
            : 5.0;

        $mentor->save();
    }
}
