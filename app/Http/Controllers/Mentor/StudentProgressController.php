<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Models\Graduation;
use App\Models\Mentor;
use App\Models\MentoringSession;
use App\Models\PaketBeasiswa;
use App\Models\Task;
use App\Models\TaskSubmission;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;

class StudentProgressController extends Controller
{
    /**
     * GET /api/v1/mentor/students/{student_id}/progress
     * Full progress breakdown for a specific student.
     * Required by: peserta_detail_page.dart
     *
     * Class/paket is resolved from the student's beasiswa_diampu field.
     */
    public function show(string $studentId): JsonResponse
    {
        $student = User::find($studentId);

        if (! $student) {
            return response()->json(['message' => 'Student not found.'], 404);
        }

        // Normalize array (handles JSON strings)
        $normalizeArray = function(mixed $value): array {
            if (is_array($value)) return $value;
            if (is_string($value) && str_starts_with(trim($value), '[')) {
                $decoded = json_decode($value, true);
                return is_array($decoded) ? $decoded : [];
            }
            return [];
        };

        $beasiswaDiampuRaw = $student->beasiswa_diampu ?? [];
        $beasiswaDiampu = $normalizeArray($beasiswaDiampuRaw);

        // If student has no beasiswa, return empty progress
        if (empty($beasiswaDiampu)) {
            return response()->json([
                'student_profile' => [
                    'name'            => $student->name,
                    'university'      => $student->university,
                    'profile_picture' => \App\Http\Resources\UserResource::resolveUrl($student->profile_picture),
                ],
                'progress_percentage'  => 0,
                'fase_passed'          => 0,
                'tasks'                => [],
                'mentoring_attendance' => [],
            ]);
        }

        // Resolve paket(s) the student is enrolled in
        // Search by both exact array match or regex if stored as JSON string
        $pakets = PaketBeasiswa::where(function($q) use ($beasiswaDiampu) {
            foreach ($beasiswaDiampu as $b) {
                $q->orWhere('nama_beasiswa', 'regex', "/".preg_quote($b, '/')."/i");
            }
        })->get();
        
        $paketIds = $pakets->pluck('_id')->map(fn ($id) => (string) $id)->toArray();
        $paketNames = $pakets->pluck('nama_beasiswa')->toArray();

        // Calculate Fase Checkpoint Percentage
        $fasePercentage = 0;
        $faseCompletedTotal = 0;
        $faseTotalItems = 0;

        foreach ($pakets as $paket) {
            $rawFase = $paket->fase_checkpoint ?? '[]';
            $faseParsed = $normalizeArray($rawFase);
            $faseTotalItems += count($faseParsed);
            
            $faseCompletedTotal += \App\Models\CheckpointSubmission::where('student_id', $studentId)
                ->where(function($q) use ($paket) {
                    $q->where('paket_beasiswa', $paket->nama_beasiswa)
                      ->orWhere('class_id', (string) $paket->_id);
                })->count();
        }

        if ($faseTotalItems > 0) {
            $fasePercentage = (int) round(($faseCompletedTotal / $faseTotalItems) * 100);
        } else {
            $fasePercentage = $student->progress_percentage ?? 0;
        }

        // Aggregate tasks across all pakets
        $tasks = Task::where(function($q) use ($paketIds, $paketNames) {
            $q->whereIn('class_id', $paketIds)
              ->orWhereIn('paket_beasiswa', $paketNames);
        })->get();

        $taskList = $tasks->map(function (Task $task) use ($studentId) {
            $submission = TaskSubmission::where('task_id', (string) $task->_id)
                ->where('student_id', $studentId)
                ->first();

            $status = $submission?->status?->value ?? $submission?->status ?? 'pending';

            return [
                'task_id'           => (string) $task->_id,
                'title'             => $task->title,
                'submission_status' => $status,
                'is_fase_passed'    => in_array($status, ['graded']),
                'score'             => $submission?->score,
                'file_url'          => $submission?->file_url,
                'deadline_date'     => $task->deadline_date,
            ];
        });

        // Count completed
        $totalTasks     = $tasks->count();
        $completedTasks = $taskList->where('is_fase_passed', true)->count();

        // Mentoring sessions for all pakets
        $sessions = MentoringSession::where(function($q) use ($paketIds, $paketNames) {
            $q->whereIn('class_id', $paketIds)
              ->orWhereIn('paket_beasiswa', $paketNames);
        })->get();
        
        $attendance = $sessions->map(fn (MentoringSession $s) => [
            'session_id' => (string) $s->_id,
            'title'      => $s->title,
            'attended'   => null,
        ]);

        // Graduation status: read from `graduations` collection scoped to CURRENT active beasiswa.
        // This prevents stale 'lulus' from a previous beasiswa leaking into new enrollment.
        $currentBeasiswaName = $beasiswaDiampu[0] ?? null;
        $graduationRecord = $currentBeasiswaName
            ? Graduation::where('student_id', $studentId)
                ->where('beasiswa_name', $currentBeasiswaName)
                ->orderBy('updated_at', 'desc')
                ->first()
            : null;

        return response()->json([
            'student_profile' => [
                'name'                 => $student->name,
                'university'           => $student->university,
                'profile_picture'      => \App\Http\Resources\UserResource::resolveUrl($student->profile_picture),
                'beasiswa_diampu'      => $beasiswaDiampu,
                'graduation_proof_url' => $graduationRecord?->proof_url ?? null,
                'graduation_status'    => $graduationRecord?->status ?? null,
            ],
            'progress_percentage'  => $fasePercentage,
            'fase_passed'          => $completedTasks,
            'tasks_summary'        => "{$completedTasks}/{$totalTasks}",
            'tasks'                => $taskList->values(),
            'mentoring_attendance' => $attendance->values(),
        ]);
    }

    /**
     * POST /api/v1/mentor/students/{student_id}/graduation-status
     * Set student graduation status: lulus or gagal.
     */
    public function setGraduationStatus(string $studentId): JsonResponse
    {
        $request = request();
        $student = User::find($studentId);

        if (! $student) {
            return response()->json(['message' => 'Student not found.'], 404);
        }

        $request->validate([
            'status' => ['required', 'in:lulus,gagal'],
        ]);

        $status = $request->status;

        // Normalize beasiswa_diampu — use getRawOriginal to bypass MongoDB cast quirk
        $normalizeArray = function (mixed $value): array {
            if (is_array($value)) return $value;
            if (is_string($value) && str_starts_with(trim($value), '[')) {
                $decoded = json_decode($value, true);
                return is_array($decoded) ? $decoded : [];
            }
            return [];
        };

        $rawBeasiswa    = $student->getRawOriginal('beasiswa_diampu') ?? $student->beasiswa_diampu ?? [];
        $beasiswaDiampu = $normalizeArray($rawBeasiswa);
        $beasiswaName   = $beasiswaDiampu[0] ?? implode(', ', $beasiswaDiampu);

        // Fallback: if beasiswaDiampu is already empty (timing edge case), try to find
        // an existing 'pending' Graduation record for this student to get the beasiswa name.
        if (empty($beasiswaName)) {
            $pendingRecord = Graduation::where('student_id', $studentId)
                ->where('status', 'pending')
                ->orderBy('updated_at', 'desc')
                ->first();
            $beasiswaName = $pendingRecord?->beasiswa_name ?? '';
        }

        // Find the mentor from the currently authenticated user (mentor role)
        $authMentor = auth()->user();
        $mentorId   = ($authMentor instanceof Mentor) ? (string) $authMentor->_id : null;

        // ── Write to `graduations` collection ───────────────────────────────
        Graduation::updateOrCreate(
            [
                'student_id'    => $studentId,
                'beasiswa_name' => $beasiswaName,
            ],
            [
                'mentor_id' => $mentorId,
                'status'    => $status,
                'notified'  => false,   // triggers popup in student app
            ]
        );

        // Clean up student's active enrollment and chats for BOTH lulus and gagal
        if (in_array($status, ['lulus', 'gagal'])) {
            $student->update(['beasiswa_diampu' => []]);
            \App\Models\ChatRoom::where('type', 'private')->where('participants', $studentId)->delete();
            \App\Models\ChatRoom::where('type', 'group')->where('participants', $studentId)->pull('participants', $studentId);
        }

        // Notify the student of their graduation result
        try {
            NotificationService::graduationResult(
                $studentId,
                $beasiswaName,
                $status
            );
        } catch (\Throwable) {}

        return response()->json([
            'message' => $status === 'lulus'
                ? 'Student berhasil dinyatakan lulus.'
                : 'Student dinyatakan tidak lulus.',
            'graduation_status' => $status,
        ]);
    }
}
