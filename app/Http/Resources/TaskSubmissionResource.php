<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskSubmissionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => (string) $this->_id,
            'task_id'      => (string) $this->task_id,
            'student_id'   => (string) $this->student_id,
            'file_url'     => $this->file_url,
            'status'       => $this->status?->value ?? $this->status,
            'score'        => $this->score,
            'feedback'     => $this->feedback,
            'submitted_at' => $this->submitted_at?->toIso8601String(),
        ];
    }
}
