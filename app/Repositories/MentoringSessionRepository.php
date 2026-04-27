<?php

namespace App\Repositories;

use App\Models\MentoringSession;
use Illuminate\Support\Collection;

class MentoringSessionRepository
{
    public function findByClassId(string $classId): Collection
    {
        return MentoringSession::where('class_id', $classId)
            ->orderBy('session_date')
            ->get();
    }

    public function create(array $data): MentoringSession
    {
        return MentoringSession::create($data);
    }

    public function findUpcomingByClassIds(array $classIds, int $limit = 3): Collection
    {
        return MentoringSession::whereIn('class_id', $classIds)
            ->where('session_date', '>=', now())
            ->orderBy('session_date')
            ->limit($limit)
            ->get();
    }
}
