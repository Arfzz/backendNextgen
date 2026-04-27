<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MentorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => (string) $this->_id,
            'name'            => $this->name,
            'university'      => $this->university,
            'profile_picture' => $this->profile_picture,
            'rating_score'    => $this->rating_score ?? 0.0,
            'students_passed' => $this->students_passed ?? 0,
        ];
    }
}
