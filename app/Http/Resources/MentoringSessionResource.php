<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MentoringSessionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => (string) $this->_id,
            'class_id'     => (string) $this->class_id,
            'title'        => $this->title,
            'session_date' => $this->session_date?->toIso8601String(),
            'link'         => $this->link,
        ];
    }
}
