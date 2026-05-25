<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\MentoringSession;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClassController extends Controller
{
    /**
     * GET /api/v1/mentor/classes
     * List all classes owned by the authenticated mentor.
     */
    public function index(Request $request): JsonResponse
    {
        $mentor = $request->user();
        $beasiswaDiampu = $mentor->beasiswa_diampu ?? [];

        $classes = \App\Models\PaketBeasiswa::whereIn('nama_beasiswa', $beasiswaDiampu)->get();

        $result = $classes->map(function ($paket) {
            $totalStudents = ClassMember::where('class_id', (string) $paket->_id)->count();
            $activeTasks   = Task::where('class_id', (string) $paket->_id)
                ->where('deadline_date', '>=', now())
                ->count();

            return [
                'class_id'       => (string) $paket->_id,
                'package_title'  => $paket->nama_beasiswa,
                'total_students' => $totalStudents,
                'active_tasks'   => $activeTasks,
                'gambar'         => $paket->gambar,
            ];
        });

        return response()->json($result->values());
    }

    /**
     * GET /api/v1/mentor/classes/{class_id}/content
     * Returns all tasks, mentoring sessions, and documents for a class.
     * Required by: mentor_isi_kelas_page.dart
     */
    public function content(string $classId): JsonResponse
    {
        $tasks     = Task::where('class_id', $classId)->orderBy('deadline_date')->get();
        $mentoring = MentoringSession::where('class_id', $classId)->orderBy('session_date')->get();
        $documents = Document::where('class_id', $classId)->orderBy('uploaded_at', 'desc')->get();
        $pesertaCount = ClassMember::where('class_id', $classId)->count();

        return response()->json([
            'peserta_count' => $pesertaCount,
            'tasks'     => $tasks->map(fn($t) => [
                'id'            => (string) $t->_id,
                'title'         => $t->title,
                'description'   => $t->description ?? '',
                'deadline_date' => $t->deadline_date,
            ])->values(),
            'mentoring_sessions' => $mentoring->map(fn($m) => [
                'id'           => (string) $m->_id,
                'title'        => $m->title,
                'session_date' => $m->session_date,
                'link'         => $m->link ?? '',
            ])->values(),
            'documents' => $documents->map(fn($d) => [
                'id'          => (string) $d->_id,
                'title'       => $d->title,
                'file_url'    => $d->file_url,
                'uploaded_at' => $d->uploaded_at,
            ])->values(),
        ]);
    }

    /**
     * GET /api/v1/mentor/classes/{class_id}/students
     * List all students whose beasiswa_diampu contains the package name.
     */
    public function students(string $classId): JsonResponse
    {
        // Resolve the paket/kelas from the given classId
        $paket = \App\Models\PaketBeasiswa::find($classId);
        if (! $paket) {
            return response()->json([]);
        }

        $namaBeasiswa = $paket->nama_beasiswa;

        // Find students who have this beasiswa in their beasiswa_diampu list
        // MongoDB's whereIn on array fields checks if the array contains the value
        $matchedStudents = User::where('role', 'student')
            ->whereIn('beasiswa_diampu', [$namaBeasiswa])
            ->get();

        $result = $matchedStudents->map(function (User $student) use ($namaBeasiswa) {
            return [
                'student_id'      => (string) $student->_id,
                'name'            => $student->name,
                'profile_picture' => $student->profile_picture,
                'progress'        => $student->progress_percentage ?? 0,
                'university'      => $student->university ?? '',
                'paket'           => $namaBeasiswa,
            ];
        })->values();

        return response()->json($result);
    }
}
