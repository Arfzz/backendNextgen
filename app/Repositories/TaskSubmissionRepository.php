<?php

namespace App\Repositories;

use App\Models\TaskSubmission;
use App\Enums\SubmissionStatus;
use Illuminate\Support\Collection;

class TaskSubmissionRepository
{
    public function findByTaskAndStudent(string $taskId, string $studentId): ?TaskSubmission
    {
        return TaskSubmission::where('task_id', $taskId)
            ->where('student_id', $studentId)
            ->first();
    }

    public function findByTaskId(string $taskId): Collection
    {
        return TaskSubmission::where('task_id', $taskId)->get();
    }

    public function findByStudentId(string $studentId): Collection
    {
        return TaskSubmission::where('student_id', $studentId)->get();
    }

    /**
     * Count distinct tasks submitted (status submitted|graded) by a student
     * whose task belongs to any of the given class_ids (paket _ids).
     * Uses two-step approach since MongoDB doesn't support SQL subqueries.
     */
    public function countSubmittedByStudentForClasses(string $studentId, array $classIds): int
    {
        // Step 1: collect all task IDs in those classes
        $taskIds = \App\Models\Task::whereIn('class_id', $classIds)
            ->pluck('_id')
            ->map(fn($id) => (string) $id)
            ->toArray();

        if (empty($taskIds)) {
            return 0;
        }

        // Step 2: count how many of those tasks have been submitted/reviewed/graded by the student
        return TaskSubmission::where('student_id', $studentId)
            ->whereIn('status', ['submitted', 'reviewed', 'graded'])
            ->whereIn('task_id', $taskIds)
            ->count();
    }

    public function create(array $data): TaskSubmission
    {
        return TaskSubmission::create($data);
    }

    public function update(TaskSubmission $submission, array $data): bool
    {
        return $submission->update($data);
    }

    public function findById(string $id): ?TaskSubmission
    {
        return TaskSubmission::find($id);
    }
}
