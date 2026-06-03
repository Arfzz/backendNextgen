<?php

namespace App\Http\Resources;

use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MentorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => (string) $this->_id,
            'name'            => $this->name ?? $this->nama_mentor,
            'email'           => $this->email,
            'role'            => 'mentor',
            'university'      => $this->university ?? $this->pendidikan,
            'profile_picture' => UserResource::resolveUrl($this->profile_picture),
            'rating_score'    => $this->rating_score ?? $this->rating ?? 5.0,
            'students_passed' => $this->students_passed ?? 0,
            'awardee'         => $this->awardee ?? [],
        ];
    }
}
