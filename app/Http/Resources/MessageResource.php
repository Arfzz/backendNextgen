<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => (string) $this->_id,
            'conversation_id' => (string) $this->conversation_id,
            'sender_id'       => (string) $this->sender_id,
            'content'         => $this->content,
            'is_read'         => $this->is_read,
            'created_at'      => $this->created_at?->toIso8601String(),
            
            // Opsional: Load relasi sender jika dipanggil
            'sender'          => new UserResource($this->whenLoaded('sender')),
        ];
    }
}
