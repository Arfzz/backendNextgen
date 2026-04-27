<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClassMemberResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                  => (string) $this->_id,
            'class_id'            => (string) $this->class_id,
            'student_id'          => (string) $this->student_id,
            'progress_percentage' => $this->progress_percentage ?? 0,
            'fase_passed'         => $this->fase_passed ?? 0,
            'status'              => $this->status?->value ?? $this->status,
            'joined_at'           => $this->joined_at?->toIso8601String(),
            'student'             => $this->when(
                isset($this->student),
                fn () => new UserResource($this->student)
            ),
        ];
    }
}
