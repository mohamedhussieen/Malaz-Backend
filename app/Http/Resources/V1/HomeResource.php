<?php

namespace App\Http\Resources\V1;

use App\Http\Resources\V1\Concerns\ResolvesLocalizedFields;
use App\Support\MediaUrl;
use Illuminate\Http\Resources\Json\JsonResource;

class HomeResource extends JsonResource
{
    use ResolvesLocalizedFields;

    public function toArray($request): array
    {
        return [
            'headline_text' => $this->localized('headline_text'),
            'body_text' => $this->localized('body_text'),
            'youtube_url' => $this->youtube_url,
            'hero_gallery_urls' => $this->images
                ? $this->images->map(fn ($image) => MediaUrl::toUrl($image->path))->values()->all()
                : [],
        ];
    }
}
