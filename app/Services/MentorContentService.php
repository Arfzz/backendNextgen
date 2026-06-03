<?php

namespace App\Services;

use App\Models\PaketBeasiswa;
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

    public function createTask(string $classId, $mentor, array $data, $request = null): mixed
    {
        $paket = PaketBeasiswa::find($classId);

        $fileUrl = null;
        if ($request && $request->hasFile('file')) {
            $fileUrl = $this->fileUploadService->upload($request->file('file'), 'tasks');
        }

        return $this->taskRepo->create([
            'class_id'       => $classId,
            'paket_beasiswa' => $paket?->nama_beasiswa,
            'mentor_id'      => (string) $mentor->_id,
            'title'          => $data['title'],
            'description'    => $data['description'],
            'deadline_date'  => $data['deadline_date'],
            'file_url'       => $fileUrl,
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

    public function createMentoringSession(string $classId, $mentor, array $data): mixed
    {
        $paket = PaketBeasiswa::find($classId);

        return $this->mentoringRepo->create([
            'class_id'       => $classId,
            'paket_beasiswa' => $paket?->nama_beasiswa,
            'mentor_id'      => (string) $mentor->_id,
            'title'          => $data['title'],
            'session_date'   => $data['session_date'],
            'link'           => $data['link'] ?? null,
        ]);
    }

    public function updateMentoringSession(string $sessionId, array $data): mixed
    {
        $session = $this->mentoringRepo->findById($sessionId);
        if (! $session) return null;

        $this->mentoringRepo->update($session, $data);
        return $session->fresh();
    }

    public function deleteMentoringSession(string $sessionId): bool
    {
        $session = $this->mentoringRepo->findById($sessionId);
        return $session ? $this->mentoringRepo->delete($session) : false;
    }

    // ── Documents ────────────────────────────────────────────────────────────

    public function uploadDocument(string $classId, $mentor, array $data, $file): mixed
    {
        $paket   = PaketBeasiswa::find($classId);
        $fileUrl = $this->fileUploadService->upload($file, 'documents');

        return $this->documentRepo->create([
            'class_id'       => $classId,
            'paket_beasiswa' => $paket?->nama_beasiswa,
            'mentor_id'      => (string) $mentor->_id,
            'title'          => $data['title'],
            'file_url'       => $fileUrl,
            'uploaded_by'    => (string) $mentor->_id,
            'uploaded_at'    => now(),
        ]);
    }

    public function deleteDocument(string $documentId): bool
    {
        $doc = $this->documentRepo->findById($documentId);
        return $doc ? $this->documentRepo->delete($doc) : false;
    }
}
