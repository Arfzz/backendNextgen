<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => (string) $this->_id,
            'name'            => $this->name,
            'email'           => $this->email,
            'role'            => $this->role?->value ?? $this->role,
            'university'      => $this->university,
            'profile_picture' => $this->profile_picture,
            'rating_score'    => $this->rating_score,
            'students_passed' => $this->students_passed,
            'created_at'      => $this->created_at?->toIso8601String(),
        ];
    }
}
