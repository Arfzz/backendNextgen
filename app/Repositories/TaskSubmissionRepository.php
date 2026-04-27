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
