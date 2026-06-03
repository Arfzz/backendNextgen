<?php

namespace App\Http\Resources;

use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => (string) $this->_id,
            'title'        => $this->title,
            'slug'         => $this->slug,
            'image_url'    => UserResource::resolveUrl($this->image_url ?? $this->thumbnail),
            'content'      => $this->content,
            'published_at' => $this->published_at?->toIso8601String(),
        ];
    }
}
