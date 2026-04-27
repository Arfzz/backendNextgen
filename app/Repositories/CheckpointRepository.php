<?php

namespace App\Repositories;

use App\Models\Checkpoint;
use App\Models\StudentCheckpoint;
use Illuminate\Support\Collection;

class CheckpointRepository
{
    public function findByClassId(string $classId): Collection
    {
        return Checkpoint::where('class_id', $classId)->orderBy('order_index')->get();
    }

    public function create(array $data): Checkpoint
    {
        return Checkpoint::create($data);
    }

    public function getStudentCheckpoint(string $studentId, string $checkpointId): ?StudentCheckpoint
    {
        return StudentCheckpoint::where('student_id', $studentId)
            ->where('checkpoint_id', $checkpointId)
            ->first();
    }

    public function getCompletedCheckpointIds(string $studentId, array $checkpointIds): array
    {
        return StudentCheckpoint::where('student_id', $studentId)
            ->whereIn('checkpoint_id', $checkpointIds)
            ->where('is_completed', true)
            ->pluck('checkpoint_id')
            ->toArray();
    }
}
