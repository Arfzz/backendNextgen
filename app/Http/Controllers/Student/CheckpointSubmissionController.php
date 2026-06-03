<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\CheckpointSubmission;
use App\Models\PaketBeasiswa;
use App\Services\FileUploadService;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CheckpointSubmissionController extends Controller
{
    public function __construct(private readonly FileUploadService $uploader) {}

    /**
     * POST /api/v1/student/checkpoints/submit
     *
     * Body (multipart/form-data):
     *   - checkpoint_title : "Seleksi Wawancara"
     *   - file             : [file]
     */
    public function submit(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'checkpoint_title' => ['required', 'string', 'max:255'],
            'file'             => ['required', 'file', 'max:20480'], // 20 MB
        ]);

        $student        = $request->user();
        $beasiswaDiampu = $this->normalizeArray($student->beasiswa_diampu ?? []);

        // Resolve paket from beasiswa name
        $paket = null;
        foreach ($beasiswaDiampu as $nama) {
            $escaped = preg_quote($nama, '/');
            $paket   = PaketBeasiswa::where('nama_beasiswa', 'regex', "/{$escaped}/i")->first();
            if ($paket) break;
        }

        if (! $paket) {
            return response()->json(['message' => 'Paket beasiswa tidak ditemukan.'], 404);
        }

        $classId      = (string) $paket->_id;
        $namaBeasiswa = $paket->nama_beasiswa;

        // Upload file
        $fileUrl = $this->uploader->upload($request->file('file'), 'checkpoint_submissions');

        // Upsert: replace existing submission for same student + checkpoint_title
        $existing = CheckpointSubmission::where('student_id', (string) $student->_id)
            ->where('checkpoint_title', $validated['checkpoint_title'])
            ->where('class_id', $classId)
            ->first();

        if ($existing) {
            $existing->update([
                'file_url'     => $fileUrl,
                'submitted_at' => now(),
            ]);
            $submission = $existing->fresh();
        } else {
            $submission = CheckpointSubmission::create([
                'paket_beasiswa'   => $namaBeasiswa,
                'class_id'         => $classId,
                'student_id'       => (string) $student->_id,
                'checkpoint_title' => $validated['checkpoint_title'],
                'file_url'         => $fileUrl,
                'submitted_at'     => now(),
            ]);
        }

        // Notify all mentors of this beasiswa — uses beasiswa name already resolved above
        try {
            NotificationService::broadcastCheckpointToMentors(
                $namaBeasiswa,
                $student->name,
                $validated['checkpoint_title'],
                (string) $submission->_id
            );
        } catch (\Throwable) {}

        return response()->json([
            'message'    => 'Bukti checkpoint berhasil dikirim.',
            'submission' => [
                'id'               => (string) $submission->_id,
                'checkpoint_title' => $submission->checkpoint_title,
                'file_url'         => $submission->file_url,
                'submitted_at'     => $submission->submitted_at,
            ],
        ], 201);
    }

    /**
     * GET /api/v1/student/checkpoints/my-submissions
     * Returns all checkpoint submissions for the authenticated student.
     */
    public function mySubmissions(Request $request): JsonResponse
    {
        $student = $request->user();

        $submissions = CheckpointSubmission::where('student_id', (string) $student->_id)
            ->orderBy('submitted_at', 'desc')
            ->get();

        return response()->json(
            $submissions->map(fn ($s) => [
                'id'               => (string) $s->_id,
                'checkpoint_title' => $s->checkpoint_title,
                'file_url'         => $s->file_url,
                'submitted_at'     => $s->submitted_at,
                'paket_beasiswa'   => $s->paket_beasiswa,
            ])->values()
        );
    }

    // ─── Helper ──────────────────────────────────────────────────────────────
    private function normalizeArray(mixed $value): array
    {
        if (is_array($value)) return $value;
        if (is_string($value) && str_starts_with(trim($value), '[')) {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [];
        }
        return [];
    }
}
