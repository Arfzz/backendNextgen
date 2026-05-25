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
            $this->recalculateProgress($studentId);
            return $existing->fresh();
        }

        $submission = $this->submissionRepo->create([
            'task_id'      => $taskId,
            'student_id'   => $studentId,
            'file_url'     => $fileUrl,
            'status'       => SubmissionStatus::Submitted->value,
            'submitted_at' => now(),
        ]);
        $this->recalculateProgress($studentId);
        return $submission;
    }

    // ──────────────────────────────────────────────────────
    // Recalculate progress_percentage for a student
    // based purely on submissions vs tasks in their beasiswa.
    // This is stored directly on the User model.
    // ──────────────────────────────────────────────────────
    private function recalculateProgress(string $studentId): void
    {
        $student = User::find($studentId);
        if (! $student) return;

        // All pakets from the student's beasiswa_diampu array
        $beasiswaDiampu = $student->beasiswa_diampu ?? [];
        if (empty($beasiswaDiampu)) return;

        $pakets = PaketBeasiswa::whereIn('nama_beasiswa', $beasiswaDiampu)->get();
        if ($pakets->isEmpty()) return;

        $paketIds = $pakets->pluck('_id')->map(fn($id) => (string) $id)->toArray();

        // Count all tasks in those pakets
        $totalTasks = Task::whereIn('class_id', $paketIds)->count();
        if ($totalTasks === 0) return;

        // Count submitted/graded tasks by this student
        $completedTasks = $this->submissionRepo->countSubmittedByStudentForClasses(
            $studentId, $paketIds
        );

        $percentage = (int) round(($completedTasks / $totalTasks) * 100);

        // Persist on the user document
        $student->update(['progress_percentage' => $percentage]);
    }
}
