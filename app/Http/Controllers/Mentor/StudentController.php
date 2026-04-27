<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Http\Resources\TaskSubmissionResource;
use App\Repositories\TaskSubmissionRepository;
use App\Repositories\UserRepository;
use Illuminate\Http\JsonResponse;

class StudentController extends Controller
{
    public function __construct(
        private readonly TaskSubmissionRepository $submissionRepo,
        private readonly UserRepository           $userRepo,
    ) {}

    /**
     * Get all submissions for a specific student (for peserta_detail_page).
     */
    public function submissions(string $studentId): JsonResponse
    {
        $submissions = $this->submissionRepo->findByStudentId($studentId);

        return response()->json(TaskSubmissionResource::collection($submissions));
    }
}
