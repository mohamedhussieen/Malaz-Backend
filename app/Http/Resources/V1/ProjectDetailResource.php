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
            'name_ar' => $this->name_ar ?? $this->name,
            'description' => $this->localized('description'),
            'location' => $this->localized('location'),
            'cover_url' => MediaUrl::toUrl($this->cover_path),
            'is_featured_home' => (bool) $this->is_featured_home,
            'price' => $this->price !== null ? (int) $this->price : null,
            'status' => $this->status,
            'valuation' => $this->valuation !== null ? (int) $this->valuation : null,
            'yield' => $this->yield !== null ? (float) $this->yield : null,
            'property_type' => is_array($this->property_type) ? $this->property_type : [],
            'year_built' => $this->year_built !== null ? (int) $this->year_built : null,
            'area_sqft' => $this->area_sqft !== null ? (int) $this->area_sqft : null,
            'features' => is_array($this->features) ? $this->features : [],
            'gallery' => $this->images
                ? $this->images->map(fn ($image) => [
                    'id' => $image->id,
                    'name' => $image->name,
                    'sort_order' => (int) $image->sort_order,
                    'url' => MediaUrl::toUrl($image->path),
                ])->values()->all()
                : [],
            'gallery_urls' => $this->images
                ? $this->images->map(fn ($image) => MediaUrl::toUrl($image->path))->values()->all()
                : [],
        ];
    }
}
