<?php

namespace App\Http\Resources\V1;

use App\Http\Resources\V1\Concerns\ResolvesLocalizedFields;
use App\Support\MediaUrl;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectListResource extends JsonResource
{
    use ResolvesLocalizedFields;

    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->localized('name'),
            'location' => $this->localized('location'),
            'cover_url' => MediaUrl::toUrl($this->cover_path),
            'is_featured_home' => (bool) $this->is_featured_home,
        ];
    }
}
