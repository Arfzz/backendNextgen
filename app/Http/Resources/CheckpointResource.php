<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CheckpointResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => (string) $this->_id,
            'class_id'      => (string) $this->class_id,
            'title'         => $this->title,
            'schedule_date' => $this->schedule_date?->toDateString(),
            'order_index'   => $this->order_index,
            'is_completed'  => $this->is_completed ?? false,
        ];
    }
}
