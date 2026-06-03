<?php

namespace App\Http\Resources;

use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PackageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => (string) $this->_id,
            'title'         => $this->title,
            'description'   => $this->description,
            'deadline_date' => $this->deadline_date?->toDateString(),
            'price'         => $this->price,
            'old_price'     => $this->old_price,
            'features'      => $this->features ?? [],
            'cover_image'   => UserResource::resolveUrl($this->cover_image),
        ];
    }
}
