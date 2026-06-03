<?php

namespace App\Services;

use App\Enums\SubmissionStatus;
use App\Models\PaketBeasiswa;
use App\Models\Task;
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
     * - If no prior submission: create new with status=submitted.
     * - If exists but NOT completed (is_completed=false): push current state
     *   into revision_history, update file_url + reset status to submitted.
     * - If is_completed=true: block resubmission (return false).
     */
    public function submitTask(string $taskId, User $student, UploadedFile $file): mixed
    {
        $task = $this->taskRepo->findById($taskId);

        if (! $task) {
            return null;
        }

        $fileUrl   = $this->fileUploadService->upload($file, 'submissions');
        $studentId = (string) $student->_id;

        $existing = $this->submissionRepo->findByTaskAndStudent($taskId, $studentId);

        if ($existing) {
            // Block resubmission if marked complete
            if ($existing->is_completed) {
                return false;
            }

            // Push current attempt to history
            $history = $existing->revision_history ?? [];
            $history[] = [
                'file_url'     => $existing->file_url,
                'submitted_at' => $existing->submitted_at?->toIso8601String(),
                'feedback'     => $existing->feedback,
            ];

            $this->submissionRepo->update($existing, [
                'file_url'         => $fileUrl,
                'status'           => SubmissionStatus::Submitted->value,
                'feedback'         => null,   // reset feedback on resubmit
                'submitted_at'     => now(),
                'revision_history' => $history,
            ]);
            $this->recalculateProgress($studentId);
            return $existing->fresh();
        }

        $submission = $this->submissionRepo->create([
            'task_id'          => $taskId,
            'student_id'       => $studentId,
            'file_url'         => $fileUrl,
            'status'           => SubmissionStatus::Submitted->value,
            'submitted_at'     => now(),
            'revision_history' => [],
            'is_completed'     => false,
        ]);
        $this->recalculateProgress($studentId);
        return $submission;
    }

    // ──────────────────────────────────────────────────────
    private function recalculateProgress(string $studentId): void
    {
        $student = User::find($studentId);
        if (! $student) return;

        $beasiswaDiampu = $student->beasiswa_diampu ?? [];
        if (empty($beasiswaDiampu)) return;

        $pakets = PaketBeasiswa::where('nama_beasiswa', 'in', $beasiswaDiampu)->get();
        if ($pakets->isEmpty()) return;

        $paketIds   = $pakets->pluck('_id')->map(fn($id) => (string) $id)->toArray();
        $totalTasks = Task::whereIn('class_id', $paketIds)->count();
        if ($totalTasks === 0) return;

        $completedTasks = $this->submissionRepo->countSubmittedByStudentForClasses($studentId, $paketIds);
        $percentage     = (int) round(($completedTasks / $totalTasks) * 100);
        $student->update(['progress_percentage' => $percentage]);
    }
}
