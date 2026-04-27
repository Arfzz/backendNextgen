<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => (string) $this->_id,
            'class_id'      => (string) $this->class_id,
            'mentor_id'     => (string) $this->mentor_id,
            'title'         => $this->title,
            'description'   => $this->description,
            'deadline_date' => $this->deadline_date?->toDateString(),
            'submission'    => $this->when(
                isset($this->submission),
                fn () => new TaskSubmissionResource($this->submission)
            ),
            'created_at'    => $this->created_at?->toIso8601String(),
        ];
    }
}
