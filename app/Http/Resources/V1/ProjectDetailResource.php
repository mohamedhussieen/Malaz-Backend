<?php

namespace App\Http\Resources\V1;

use App\Http\Resources\V1\Concerns\ResolvesLocalizedFields;
use App\Support\MediaUrl;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectDetailResource extends JsonResource
{
    use ResolvesLocalizedFields;

    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->localized('name'),
            'description' => $this->localized('description'),
            'location' => $this->localized('location'),
            'cover_url' => MediaUrl::toUrl($this->cover_path),
            'is_featured_home' => (bool) $this->is_featured_home,
            'gallery' => $this->images
                ? $this->images->map(fn ($image) => [
                    'id' => $image->id,
                    'name' => $image->name,
                    'sort_order' => $image->sort_order,
                    'url' => MediaUrl::toUrl($image->path),
                ])->values()->all()
                : [],
            'gallery_urls' => $this->images
                ? $this->images->map(fn ($image) => MediaUrl::toUrl($image->path))->values()->all()
                : [],
        ];
    }
}
