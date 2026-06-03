<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\SubmitTaskRequest;
use App\Http\Resources\TaskResource;
use App\Http\Resources\TaskSubmissionResource;
use App\Repositories\TaskRepository;
use App\Repositories\TaskSubmissionRepository;
use App\Services\NotificationService;
use App\Services\StudentTaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function __construct(
        private readonly TaskRepository           $taskRepo,
        private readonly TaskSubmissionRepository $submissionRepo,
        private readonly StudentTaskService       $taskService,
    ) {}

    public function show(Request $request, string $taskId): JsonResponse
    {
        $task = $this->taskRepo->findById($taskId);

        if (! $task) {
            return response()->json(['message' => 'Task not found.'], 404);
        }

        // Attach this student's submission so TaskResource can include it
        $studentId = (string) $request->user()?->_id;
        $submission = $this->submissionRepo->findByTaskAndStudent($taskId, $studentId);
        $task->submission = $submission; // inject as dynamic property

        return response()->json(new TaskResource($task));
    }

    public function submit(SubmitTaskRequest $request, string $taskId): JsonResponse
    {
        $result = $this->taskService->submitTask($taskId, $request->user(), $request->file('file'));

        if ($result === null) {
            return response()->json(['message' => 'Task not found.'], 404);
        }

        // is_completed=true means blocked
        if ($result === false) {
            return response()->json(['message' => 'Tugas sudah ditandai selesai, tidak bisa mengirim ulang.'], 422);
        }

        // Notify mentors of this beasiswa — uses task.paket_beasiswa for correct filtering
        try {
            $task = $this->taskRepo->findById($taskId);
            if ($task) {
                $beasiswaName = $task->paket_beasiswa;
                if (!$beasiswaName && $task->class_id) {
                    $beasiswaName = \App\Models\PaketBeasiswa::find($task->class_id)?->nama_beasiswa;
                }
                if ($beasiswaName) {
                    NotificationService::broadcastTaskSubmittedToMentors(
                        $beasiswaName,
                        $request->user()->name,
                        $task->title ?? 'Tugas',
                        (string) $result->_id
                    );
                }
            }
        } catch (\Throwable) {}

        return response()->json([
            'message'    => 'Task submitted successfully.',
            'submission' => new TaskSubmissionResource($result),
        ], 201);
    }
}
