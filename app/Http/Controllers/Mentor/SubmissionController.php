<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mentor\GradeSubmissionRequest;
use App\Http\Resources\TaskSubmissionResource;
use App\Models\Task;
use App\Services\MentorDashboardService;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;

class SubmissionController extends Controller
{
    public function __construct(private readonly MentorDashboardService $service) {}

    /** Send ulasan/feedback — student can still revise */
    public function review(GradeSubmissionRequest $request, string $submissionId): JsonResponse
    {
        $submission = $this->service->reviewSubmission($submissionId, $request->validated());

        if (! $submission) {
            return response()->json(['message' => 'Submission not found.'], 404);
        }

        // Notify the student their task was reviewed
        // TaskSubmission uses 'student_id', and title is looked up from the Task
        try {
            $taskTitle = Task::find($submission->task_id)?->title ?? 'Tugas';
            NotificationService::submissionGraded(
                (string) $submission->student_id,
                $taskTitle,
                $request->input('feedback', ''),
                (string) $submission->_id
            );
        } catch (\Throwable) {}

        return response()->json([
            'message'    => 'Ulasan berhasil dikirim.',
            'submission' => new TaskSubmissionResource($submission),
        ]);
    }

    /** Tandai Selesai — blocks further resubmission */
    public function complete(string $submissionId): JsonResponse
    {
        $submission = $this->service->completeSubmission($submissionId);

        if (! $submission) {
            return response()->json(['message' => 'Submission not found.'], 404);
        }

        try {
            $taskTitle = Task::find($submission->task_id)?->title ?? 'Tugas';
            NotificationService::submissionGraded(
                (string) $submission->student_id,
                $taskTitle,
                'Telah diselesaikan secara final.',
                (string) $submission->_id
            );
        } catch (\Throwable) {}

        return response()->json([
            'message'    => 'Tugas ditandai selesai.',
            'submission' => new TaskSubmissionResource($submission),
        ]);
    }

    /** Legacy grade endpoint (kept for backward compat) */
    public function grade(GradeSubmissionRequest $request, string $submissionId): JsonResponse
    {
        return $this->review($request, $submissionId);
    }
}
