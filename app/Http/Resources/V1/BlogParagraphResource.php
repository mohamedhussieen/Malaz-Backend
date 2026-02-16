<?php

namespace App\Http\Resources\V1;

use App\Http\Resources\V1\Concerns\ResolvesLocalizedFields;
use Illuminate\Http\Resources\Json\JsonResource;

class BlogParagraphResource extends JsonResource
{
    use ResolvesLocalizedFields;

    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'header' => $this->localized('header'),
            'content' => $this->localized('content'),
            'header_ar' => $this->header_ar,
            'header_en' => $this->header_en,
            'content_ar' => $this->content_ar,
            'content_en' => $this->content_en,
            'sort_order' => $this->sort_order,
        ];
    }
}
