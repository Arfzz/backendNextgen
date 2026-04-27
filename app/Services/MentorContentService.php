<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\CheckpointRepository;
use App\Repositories\DocumentRepository;
use App\Repositories\MentoringSessionRepository;
use App\Repositories\TaskRepository;
use App\Repositories\TaskSubmissionRepository;

class MentorContentService
{
    public function __construct(
        private readonly TaskRepository             $taskRepo,
        private readonly CheckpointRepository       $checkpointRepo,
        private readonly MentoringSessionRepository $mentoringRepo,
        private readonly DocumentRepository         $documentRepo,
        private readonly FileUploadService          $fileUploadService,
        private readonly TaskSubmissionRepository   $submissionRepo,
    ) {}

    // ── Tasks ────────────────────────────────────────────────────────────────

    public function createTask(string $classId, User $mentor, array $data): mixed
    {
        return $this->taskRepo->create([
            'class_id'      => $classId,
            'mentor_id'     => (string) $mentor->_id,
            'title'         => $data['title'],
            'description'   => $data['description'],
            'deadline_date' => $data['deadline_date'],
        ]);
    }

    public function updateTask(string $taskId, array $data): mixed
    {
        $task = $this->taskRepo->findById($taskId);
        if (! $task) return null;

        $this->taskRepo->update($task, $data);
        return $task->fresh();
    }

    public function deleteTask(string $taskId): bool
    {
        $task = $this->taskRepo->findById($taskId);
        return $task ? $this->taskRepo->delete($task) : false;
    }

    public function getTaskSubmissions(string $taskId): \Illuminate\Support\Collection
    {
        return $this->submissionRepo->findByTaskId($taskId);
    }

    // ── Checkpoints ──────────────────────────────────────────────────────────

    public function createCheckpoint(string $classId, array $data): mixed
    {
        return $this->checkpointRepo->create([
            'class_id'      => $classId,
            'title'         => $data['title'],
            'schedule_date' => $data['schedule_date'],
            'order_index'   => $data['order_index'],
        ]);
    }

    // ── Mentoring Sessions ───────────────────────────────────────────────────

    public function createMentoringSession(string $classId, array $data): mixed
    {
        return $this->mentoringRepo->create([
            'class_id'     => $classId,
            'title'        => $data['title'],
            'session_date' => $data['session_date'],
            'link'         => $data['link'],
        ]);
    }

    // ── Documents ────────────────────────────────────────────────────────────

    public function uploadDocument(string $classId, User $mentor, array $data, $file): mixed
    {
        $fileUrl = $this->fileUploadService->upload($file, 'documents');

        return $this->documentRepo->create([
            'class_id'    => $classId,
            'title'       => $data['title'],
            'file_url'    => $fileUrl,
            'uploaded_by' => (string) $mentor->_id,
            'uploaded_at' => now(),
        ]);
    }
}
