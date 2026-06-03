<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskSubmissionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $student = User::find($this->student_id);

        return [
            'id'               => (string) $this->_id,
            'task_id'          => (string) $this->task_id,
            'student_id'       => (string) $this->student_id,
            'name'             => $student?->name ?? 'Unknown',
            'university'       => $student?->university ?? '-',
            'file_url'         => $this->file_url,
            'status'           => $this->status?->value ?? $this->status,
            'score'            => $this->score,
            'feedback'         => $this->feedback,
            'is_completed'     => (bool) ($this->is_completed ?? false),
            'submitted_at'     => $this->submitted_at?->toIso8601String(),
            // revision history: previous attempts (oldest first)
            'revision_history' => collect($this->revision_history ?? [])->map(fn($h) => [
                'file_url'     => $h['file_url'] ?? null,
                'submitted_at' => $h['submitted_at'] ?? null,
                'feedback'     => $h['feedback'] ?? null,
            ])->values()->all(),
        ];
    }
}
