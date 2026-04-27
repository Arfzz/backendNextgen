<?php

namespace App\Repositories;

use App\Models\Task;
use Illuminate\Support\Collection;

class TaskRepository
{
    public function findByClassId(string $classId): Collection
    {
        return Task::where('class_id', $classId)->orderBy('deadline_date')->get();
    }

    public function findById(string $id): ?Task
    {
        return Task::find($id);
    }

    public function create(array $data): Task
    {
        return Task::create($data);
    }

    public function update(Task $task, array $data): bool
    {
        return $task->update($data);
    }

    public function delete(Task $task): bool
    {
        return (bool) $task->delete();
    }

    public function findUpcomingByClassIds(array $classIds, int $limit = 3): Collection
    {
        return Task::whereIn('class_id', $classIds)
            ->where('deadline_date', '>=', now())
            ->orderBy('deadline_date')
            ->limit($limit)
            ->get();
    }
}
