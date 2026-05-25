<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'      => (string) $this->_id,
            'title'   => $this->title,
            'time'    => $this->created_at?->toIso8601String(),
            'is_read' => (bool) $this->is_read,
            'type'    => $this->type,
        ];
    }
}
