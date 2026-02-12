<?php

namespace App\Http\Resources\V1;

use App\Http\Resources\V1\Concerns\ResolvesLocalizedFields;
use App\Support\MediaUrl;
use Illuminate\Http\Resources\Json\JsonResource;

class OwnerResource extends JsonResource
{
    use ResolvesLocalizedFields;

    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->localized('name'),
            'title' => $this->localized('title'),
            'bio' => $this->localized('bio'),
            'avatar_url' => MediaUrl::toUrl($this->avatar_path),
        ];
    }
}
