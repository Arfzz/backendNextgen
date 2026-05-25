<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Models\ClassMember;
use App\Models\Kelas;
use App\Models\MentoringSession;
use App\Models\Task;
use App\Models\TaskSubmission;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class StudentProgressController extends Controller
{
    /**
     * GET /api/v1/mentor/students/{student_id}/progress
     * Full progress breakdown for a specific student.
     * Required by: peserta_detail_page.dart
     */
    public function show(string $studentId): JsonResponse
    {
        $student = User::find($studentId);

        if (! $student) {
            return response()->json(['message' => 'Student not found.'], 404);
        }

        // Find student's active membership to get their class
        $membership = ClassMember::where('student_id', $studentId)->first();

        if (! $membership) {
            return response()->json([
                'student_profile' => [
                    'name'            => $student->name,
                    'university'      => $student->university,
                    'profile_picture' => $student->profile_picture,
                ],
                'progress_percentage'  => 0,
                'tasks'                => [],
                'mentoring_attendance' => [],
            ]);
        }

        $classId = (string) $membership->class_id;

        // --- Tasks & their submissions ---
        $tasks = Task::where('class_id', $classId)->get();
        $taskList = $tasks->map(function (Task $task) use ($studentId) {
            $submission = TaskSubmission::where('task_id', (string) $task->_id)
                ->where('student_id', $studentId)
                ->first();

            return [
                'task_id'           => (string) $task->_id,
                'title'             => $task->title,
                'submission_status' => $submission?->status?->value ?? 'pending',
                'score'             => $submission?->score,
                'file_url'          => $submission?->file_url,
            ];
        });

        // --- Mentoring attendance (sessions for student's class) ---
        $sessions = MentoringSession::where('class_id', $classId)->get();
        $attendance = $sessions->map(fn (MentoringSession $s) => [
            'session_id' => (string) $s->_id,
            'title'      => $s->title,
            'attended'   => null, // Extend: add AttendanceModel if needed
        ]);

        return response()->json([
            'student_profile' => [
                'name'            => $student->name,
                'university'      => $student->university,
                'profile_picture' => $student->profile_picture,
            ],
            'progress_percentage'  => $membership->progress_percentage ?? 0,
            'tasks'                => $taskList->values(),
            'mentoring_attendance' => $attendance->values(),
        ]);
    }
}
