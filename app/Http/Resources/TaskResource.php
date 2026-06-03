<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // The `submission` property is dynamically injected by the controller
        // for the authenticated student — it may be null or a TaskSubmission instance.
        $submission = $this->resource->submission ?? null;

        return [
            'id'            => (string) $this->_id,
            'class_id'      => (string) $this->class_id,
            'mentor_id'     => (string) $this->mentor_id,
            'title'         => $this->title,
            'description'   => $this->description,
            'deadline_date' => $this->deadline_date,
            'file_url'      => $this->file_url,
            'submission'    => $submission !== null
                ? new TaskSubmissionResource($submission)
                : null,
            'created_at'    => $this->created_at?->toIso8601String(),
        ];
    }
}
