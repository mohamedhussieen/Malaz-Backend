<?php

namespace App\Http\Resources\V1;

use App\Http\Resources\V1\Concerns\ResolvesLocalizedFields;
use App\Support\MediaUrl;
use Illuminate\Http\Resources\Json\JsonResource;

class BlogDetailResource extends JsonResource
{
    use ResolvesLocalizedFields;

    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->localized('title'),
            'slug' => $this->slug,
            'excerpt' => $this->localized('excerpt'),
            'content' => $this->localized('content'),
            'cover_url' => MediaUrl::toUrl($this->cover_path),
            'is_published' => (bool) $this->is_published,
            'published_at' => $this->published_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
