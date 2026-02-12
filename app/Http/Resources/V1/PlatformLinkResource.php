<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class PlatformLinkResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'key' => $this->key,
            'url' => $this->url,
            'is_active' => (bool) $this->is_active,
        ];
    }
}
