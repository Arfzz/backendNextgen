<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\SubmitTaskRequest;
use App\Http\Resources\TaskResource;
use App\Http\Resources\TaskSubmissionResource;
use App\Repositories\TaskRepository;
use App\Services\StudentTaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function __construct(
        private readonly TaskRepository    $taskRepo,
        private readonly StudentTaskService $taskService,
    ) {}

    public function show(string $taskId): JsonResponse
    {
        $task = $this->taskRepo->findById($taskId);

        if (! $task) {
            return response()->json(['message' => 'Task not found.'], 404);
        }

        return response()->json(new TaskResource($task));
    }

    public function submit(SubmitTaskRequest $request, string $taskId): JsonResponse
    {
        $submission = $this->taskService->submitTask($taskId, $request->user(), $request->file('file'));

        if (! $submission) {
            return response()->json(['message' => 'Task not found.'], 404);
        }

        return response()->json([
            'message'    => 'Task submitted successfully.',
            'submission' => new TaskSubmissionResource($submission),
        ], 201);
    }
}
