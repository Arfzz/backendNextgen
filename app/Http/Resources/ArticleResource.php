<?php

namespace App\Http\Resources;

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
            'image_url'    => $this->image_url,
            'content'      => $this->content,
            'published_at' => $this->published_at?->toIso8601String(),
        ];
    }
}
