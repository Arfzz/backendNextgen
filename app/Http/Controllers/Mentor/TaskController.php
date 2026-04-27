<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mentor\StoreTaskRequest;
use App\Http\Requests\Mentor\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Http\Resources\TaskSubmissionResource;
use App\Services\MentorContentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function __construct(private readonly MentorContentService $service) {}

    public function store(StoreTaskRequest $request, string $classId): JsonResponse
    {
        $task = $this->service->createTask($classId, $request->user(), $request->validated());
        return response()->json(['message' => 'Task created.', 'task' => new TaskResource($task)], 201);
    }

    public function update(UpdateTaskRequest $request, string $taskId): JsonResponse
    {
        $task = $this->service->updateTask($taskId, $request->validated());
        if (! $task) return response()->json(['message' => 'Task not found.'], 404);
        return response()->json(['message' => 'Task updated.', 'task' => new TaskResource($task)]);
    }

    public function destroy(string $taskId): JsonResponse
    {
        $deleted = $this->service->deleteTask($taskId);
        if (! $deleted) return response()->json(['message' => 'Task not found.'], 404);
        return response()->json(['message' => 'Task deleted.']);
    }

    public function submissions(string $taskId): JsonResponse
    {
        $submissions = $this->service->getTaskSubmissions($taskId);
        return response()->json(TaskSubmissionResource::collection($submissions));
    }
}
