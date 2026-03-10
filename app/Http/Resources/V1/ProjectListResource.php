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
            'name_ar' => $this->name_ar ?? $this->name,
            'name_en' => $this->name_en ?? $this->name,
            'location' => $this->localized('location'),
            'location_ar' => $this->location_ar ?? $this->location,
            'location_en' => $this->location_en ?? $this->location,
            'cover_url' => MediaUrl::toUrl($this->cover_path),
            'owner_id' => $this->owner_id !== null ? (int) $this->owner_id : null,
            'owner' => ($this->owner_name !== null || $this->owner_name_ar !== null || $this->owner_name_en !== null) ? [
                'name' => app()->getLocale() === 'ar'
                    ? ($this->owner_name_ar ?? $this->owner_name_en ?? $this->owner_name)
                    : ($this->owner_name_en ?? $this->owner_name_ar ?? $this->owner_name),
                'name_ar' => $this->owner_name_ar ?? $this->owner_name,
                'name_en' => $this->owner_name_en ?? $this->owner_name,
                'title' => app()->getLocale() === 'ar'
                    ? ($this->owner_title_ar ?? $this->owner_title_en ?? $this->owner_title)
                    : ($this->owner_title_en ?? $this->owner_title_ar ?? $this->owner_title),
                'title_ar' => $this->owner_title_ar ?? $this->owner_title,
                'title_en' => $this->owner_title_en ?? $this->owner_title,
                'avatar_url' => $this->owner_avatar_url,
            ] : null,
            'is_featured_home' => (bool) $this->is_featured_home,
            'project_hero_section' => (bool) $this->project_hero_section,
            'price' => $this->price !== null ? (int) $this->price : null,
            'status' => $this->status,
            'valuation' => $this->valuation !== null ? (int) $this->valuation : null,
            'yield' => $this->yield !== null ? (float) $this->yield : null,
            'property_type' => is_array($this->property_type) ? $this->property_type : [],
            'year_built' => $this->year_built !== null ? (int) $this->year_built : null,
            'area_sqft' => $this->area_sqft !== null ? (int) $this->area_sqft : null,
            'min_investment' => $this->min_investment !== null ? (int) $this->min_investment : null,
            'target_fund' => $this->target_fund !== null ? (int) $this->target_fund : null,
            'funded_amount' => $this->funded_amount !== null ? (int) $this->funded_amount : null,
            'cap_rate' => $this->cap_rate !== null ? (float) $this->cap_rate : null,
            'cash_on_cash' => $this->cash_on_cash !== null ? (float) $this->cash_on_cash : null,
            'irr' => $this->irr !== null ? (float) $this->irr : null,
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
