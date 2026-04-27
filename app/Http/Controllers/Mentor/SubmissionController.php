<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mentor\GradeSubmissionRequest;
use App\Http\Resources\TaskSubmissionResource;
use App\Services\MentorDashboardService;
use Illuminate\Http\JsonResponse;

class SubmissionController extends Controller
{
    public function __construct(private readonly MentorDashboardService $service) {}

    public function grade(GradeSubmissionRequest $request, string $submissionId): JsonResponse
    {
        $submission = $this->service->gradeSubmission($submissionId, $request->validated());

        if (! $submission) {
            return response()->json(['message' => 'Submission not found.'], 404);
        }

        return response()->json([
            'message'    => 'Submission graded.',
            'submission' => new TaskSubmissionResource($submission),
        ]);
    }
}
