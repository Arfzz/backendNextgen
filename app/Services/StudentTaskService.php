<?php

namespace App\Services;

use App\Enums\SubmissionStatus;
use App\Models\User;
use App\Repositories\TaskRepository;
use App\Repositories\TaskSubmissionRepository;
use Illuminate\Http\UploadedFile;

class StudentTaskService
{
    public function __construct(
        private readonly TaskRepository           $taskRepo,
        private readonly TaskSubmissionRepository $submissionRepo,
        private readonly FileUploadService        $fileUploadService,
    ) {}

    /**
     * Submit or re-submit a task file.
     */
    public function submitTask(string $taskId, User $student, UploadedFile $file): mixed
    {
        $task = $this->taskRepo->findById($taskId);

        if (! $task) {
            return null;
        }

        $fileUrl     = $this->fileUploadService->upload($file, 'submissions');
        $studentId   = (string) $student->_id;

        $existing = $this->submissionRepo->findByTaskAndStudent($taskId, $studentId);

        if ($existing) {
            // Allow re-submission if not yet graded
            $this->submissionRepo->update($existing, [
                'file_url'     => $fileUrl,
                'status'       => SubmissionStatus::Submitted->value,
                'submitted_at' => now(),
            ]);
            return $existing->fresh();
        }

        return $this->submissionRepo->create([
            'task_id'      => $taskId,
            'student_id'   => $studentId,
            'file_url'     => $fileUrl,
            'status'       => SubmissionStatus::Submitted->value,
            'submitted_at' => now(),
        ]);
    }
}
