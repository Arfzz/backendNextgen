<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Models\CheckpointSubmission;
use App\Models\PaketBeasiswa;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentCheckpointController extends Controller
{
    /**
     * GET /api/v1/mentor/students/{studentId}/checkpoints
     *
     * Returns:
     *  - List of fase_checkpoint titles from the student's paket
     *  - Each one enriched with submission info (file_url, submitted_at) if exists
     */
    public function index(string $studentId): JsonResponse
    {
        $student = User::find($studentId);
        if (! $student) {
            return response()->json(['message' => 'Student tidak ditemukan.'], 404);
        }

        $beasiswaDiampu = $this->normalizeArray($student->beasiswa_diampu ?? []);
        if (empty($beasiswaDiampu)) {
            return response()->json([
                'student_id'     => $studentId,
                'student_name'   => $student->name,
                'paket_beasiswa' => null,
                'checkpoints'    => [],
            ]);
        }

        // Resolve paket
        $paket = null;
        foreach ($beasiswaDiampu as $nama) {
            $escaped = preg_quote($nama, '/');
            $paket   = PaketBeasiswa::where('nama_beasiswa', 'regex', "/{$escaped}/i")->first();
            if ($paket) break;
        }

        if (! $paket) {
            return response()->json([
                'student_id'     => $studentId,
                'student_name'   => $student->name,
                'paket_beasiswa' => null,
                'checkpoints'    => [],
            ]);
        }

        // Parse fase_checkpoint from paket
        $rawFase = $paket->fase_checkpoint ?? '[]';
        $fases   = $this->normalizeArray($rawFase);

        // Get all submissions for this student + this paket
        $submissions = CheckpointSubmission::where('student_id', $studentId)
            ->where('class_id', (string) $paket->_id)
            ->get()
            ->keyBy('checkpoint_title');

        // Build response
        $result = collect($fases)->values()->map(function ($title, $idx) use ($submissions) {
            $sub = $submissions->get($title);
            return [
                'order_index'      => $idx + 1,
                'checkpoint_title' => $title,
                'has_submission'   => ! is_null($sub),
                'file_url'         => $sub?->file_url,
                'submitted_at'     => $sub?->submitted_at,
            ];
        })->values();

        return response()->json([
            'student_id'     => $studentId,
            'student_name'   => $student->name,
            'paket_beasiswa' => $paket->nama_beasiswa,
            'checkpoints'    => $result,
        ]);
    }

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
