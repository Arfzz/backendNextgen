<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\MentoringSession;
use App\Models\PaketBeasiswa;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClassController extends Controller
{
    /**
     * Normalize a value stored in MongoDB that may be a JSON string or array.
     * Handles: ["YAPI ITB"] stored as string or actual array.
     */
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
     * Count students whose beasiswa_diampu contains $namaBeasiswa.
     * Handles both real-array and JSON-string storage.
     */
    private function countStudentsByBeasiswa(string $namaBeasiswa): int
    {
        $escaped = preg_quote($namaBeasiswa, '/');
        return User::where('role', 'student')
            ->where('beasiswa_diampu', 'regex', "/{$escaped}/i")
            ->count();
    }

    /**
     * GET /api/v1/mentor/classes
     * List all "classes" (PaketBeasiswa) owned by the authenticated mentor.
     */
    public function index(Request $request): JsonResponse
    {
        $mentor         = $request->user();
        $beasiswaDiampu = $this->normalizeArray($mentor->beasiswa_diampu ?? []);

        if (empty($beasiswaDiampu)) {
            return response()->json([]);
        }

        $pakets = PaketBeasiswa::whereIn('nama_beasiswa', $beasiswaDiampu)->get();

        $result = $pakets->map(function (PaketBeasiswa $paket) {
            $classId      = (string) $paket->_id;
            $namaBeasiswa = $paket->nama_beasiswa;

            $totalStudents = $this->countStudentsByBeasiswa($namaBeasiswa);

            // Count tasks: by paket_beasiswa name (new) OR class_id (legacy)
            $activeTasks = Task::where(function ($q) use ($classId, $namaBeasiswa) {
                $q->where('paket_beasiswa', $namaBeasiswa)
                  ->orWhere('class_id', $classId);
            })->count();

            return [
                'class_id'       => $classId,
                'package_title'  => $namaBeasiswa,
                'nama_beasiswa'  => $namaBeasiswa,
                'total_students' => $totalStudents,
                'active_tasks'   => $activeTasks,
                'gambar'         => \App\Http\Resources\UserResource::resolveUrl($paket->gambar),
            ];
        });

        return response()->json($result->values());
    }

    /**
     * GET /api/v1/mentor/classes/{class_id}/content
     * Returns all tasks, mentoring sessions, and documents for a class (paket).
     */
    public function content(string $classId): JsonResponse
    {
        $paket        = PaketBeasiswa::find($classId);
        $namaBeasiswa = $paket?->nama_beasiswa;

        // Dual-query: match by paket_beasiswa name (new) OR class_id (legacy)
        $taskQuery = Task::where(function ($q) use ($classId, $namaBeasiswa) {
            $q->where('class_id', $classId);
            if ($namaBeasiswa) $q->orWhere('paket_beasiswa', $namaBeasiswa);
        });

        $mentoringQuery = MentoringSession::where(function ($q) use ($classId, $namaBeasiswa) {
            $q->where('class_id', $classId);
            if ($namaBeasiswa) $q->orWhere('paket_beasiswa', $namaBeasiswa);
        });

        $documentQuery = Document::where(function ($q) use ($classId, $namaBeasiswa) {
            $q->where('class_id', $classId);
            if ($namaBeasiswa) $q->orWhere('paket_beasiswa', $namaBeasiswa);
        });

        $tasks     = $taskQuery->orderBy('deadline_date')->get();
        $mentoring = $mentoringQuery->orderBy('session_date')->get();
        $documents = $documentQuery->orderBy('uploaded_at', 'desc')->get();

        $pesertaCount = $namaBeasiswa
            ? $this->countStudentsByBeasiswa($namaBeasiswa)
            : 0;

        return response()->json([
            'peserta_count'      => $pesertaCount,
            'tasks'              => $tasks->map(fn ($t) => [
                'id'             => (string) $t->_id,
                'title'          => $t->title,
                'description'    => $t->description ?? '',
                'deadline_date'  => $t->deadline_date,
                'paket_beasiswa' => $t->paket_beasiswa ?? $namaBeasiswa,
            ])->values(),
            'mentoring_sessions' => $mentoring->map(fn ($m) => [
                'id'             => (string) $m->_id,
                'title'          => $m->title,
                'session_date'   => $m->session_date,
                'link'           => $m->link ?? '',
                'paket_beasiswa' => $m->paket_beasiswa ?? $namaBeasiswa,
            ])->values(),
            'documents'          => $documents->map(fn ($d) => [
                'id'          => (string) $d->_id,
                'title'       => $d->title,
                'file_url'    => $d->file_url,
                'uploaded_at' => $d->uploaded_at,
            ])->values(),
        ]);
    }

    /**
     * GET /api/v1/mentor/classes/{class_id}/students
     * List all students whose beasiswa_diampu contains this paket's name.
     */
    public function students(string $classId): JsonResponse
    {
        $paket = PaketBeasiswa::find($classId);
        if (! $paket) {
            return response()->json([]);
        }

        $namaBeasiswa = $paket->nama_beasiswa;
        $escaped      = preg_quote($namaBeasiswa, '/');

        $matchedStudents = User::where('role', 'student')
            ->where('beasiswa_diampu', 'regex', "/{$escaped}/i")
            ->get();

        $result = $matchedStudents->map(fn (User $student) => [
            'student_id'      => (string) $student->_id,
            'name'            => $student->name,
            'profile_picture' => $student->profile_picture,
            'progress'        => $student->progress_percentage ?? 0,
            'university'      => $student->university ?? '',
            'paket'           => $namaBeasiswa,
        ])->values();

        return response()->json($result);
    }
}
