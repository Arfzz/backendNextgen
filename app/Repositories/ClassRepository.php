<?php

namespace App\Repositories;

use App\Models\Kelas;
use Illuminate\Support\Collection;

class ClassRepository
{
    public function findById(string $id): ?Kelas
    {
        return Kelas::find($id);
    }

    public function findByMentorId(string $mentorId): Collection
    {
        return Kelas::where('mentor_id', $mentorId)->get();
    }
}
