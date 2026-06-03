<?php

namespace App\Repositories;

use App\Models\MentoringSession;
use Illuminate\Support\Collection;

class MentoringSessionRepository
{
    public function findById(string $id): ?MentoringSession
    {
        return MentoringSession::find($id);
    }

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

    public function update(MentoringSession $session, array $data): bool
    {
        return $session->update($data);
    }

    public function delete(MentoringSession $session): bool
    {
        return $session->delete();
    }

    public function findUpcomingByClassIds(array $classIds, int $limit = 3): Collection
    {
        return MentoringSession::whereIn('class_id', $classIds)
            ->where('session_date', '>=', now())
            ->orderBy('session_date')
            ->limit($limit)
            ->get();
    }

    /**
     * Query by paket_beasiswa names (string array matching beasiswa_diampu).
     */
    public function findUpcomingByBeasiswa(array $beasiswaNames, int $limit = 5): Collection
    {
        return MentoringSession::whereIn('paket_beasiswa', $beasiswaNames)
            ->orderBy('session_date')
            ->limit($limit)
            ->get();
    }

    public function findByBeasiswa(array $beasiswaNames): Collection
    {
        return MentoringSession::whereIn('paket_beasiswa', $beasiswaNames)
            ->orderBy('session_date')
            ->get();
    }
}
