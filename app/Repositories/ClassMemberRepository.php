<?php

namespace App\Repositories;

use App\Models\ClassMember;
use Illuminate\Support\Collection;

class ClassMemberRepository
{
    public function findActiveByStudentId(string $studentId): ?ClassMember
    {
        return ClassMember::where('student_id', $studentId)
            ->where('status', 'ongoing')
            ->latest('joined_at')
            ->first();
    }

    public function findByClassId(string $classId): Collection
    {
        return ClassMember::where('class_id', $classId)->get();
    }

    public function findByStudentAndClass(string $studentId, string $classId): ?ClassMember
    {
        return ClassMember::where('student_id', $studentId)
            ->where('class_id', $classId)
            ->first();
    }
}
