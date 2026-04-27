<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => (string) $this->_id,
            'class_id'    => (string) $this->class_id,
            'title'       => $this->title,
            'file_url'    => $this->file_url,
            'uploaded_at' => $this->uploaded_at?->toIso8601String(),
        ];
    }
}
