<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConversationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => (string) $this->_id,
            'student'         => new UserResource($this->whenLoaded('student')),
            'mentor'          => new UserResource($this->whenLoaded('mentor')),
            'last_message'    => $this->last_message,
            'last_message_at' => $this->last_message_at ? $this->last_message_at->toIso8601String() : null,
            'created_at'      => $this->created_at?->toIso8601String(),
        ];
    }
}
