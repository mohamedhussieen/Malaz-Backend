<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Http\Requests\Api\V1\ApiFormRequest;

class ProjectStoreRequest extends ApiFormRequest
{
    protected function prepareForValidation(): void
    {
        foreach (['is_featured_home', 'project_hero_section'] as $field) {
            if (!$this->exists($field)) {
                continue;
            }

            $this->merge([
                $field => $this->normalizeBooleanInput($this->input($field)),
            ]);
        }
    }

    private function normalizeBooleanInput(mixed $value): mixed
    {
        if (!is_string($value)) {
            return $value;
        }

        return match (strtolower(trim($value))) {
            'true', '1', 'on', 'yes' => true,
            'false', '0', 'off', 'no' => false,
            default => $value,
        };
    }

    public function rules(): array
    {
        return [
            'name_ar' => ['required', 'string', 'max:255'],
            'name_en' => ['required', 'string', 'max:255'],
            'description_ar' => ['required', 'string'],
            'description_en' => ['required', 'string'],
            'location_ar' => ['required', 'string', 'max:255'],
            'location_en' => ['required', 'string', 'max:255'],
            'owner_name_ar' => ['nullable', 'string', 'max:255'],
            'owner_name_en' => ['nullable', 'string', 'max:255'],
            'owner_title_ar' => ['nullable', 'string', 'max:255'],
            'owner_title_en' => ['nullable', 'string', 'max:255'],
            'owner_avatar_url' => ['nullable', 'string', 'max:2048'],
            'owner_avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'is_featured_home' => ['nullable', 'boolean'],
            'project_hero_section' => ['nullable', 'boolean'],
            'price' => ['nullable', 'integer', 'min:0'],
            'status' => ['nullable', 'string', 'in:available,not available,sold out'],
            'valuation' => ['nullable', 'integer', 'min:0'],
            'yield' => ['nullable', 'numeric', 'min:0'],
            'property_type' => ['nullable', 'array'],
            'property_type.*' => ['string', 'max:100'],
            'year_built' => ['nullable', 'integer', 'between:1900,2100'],
            'area_sqft' => ['nullable', 'integer', 'min:0'],
            'min_investment' => ['nullable', 'integer', 'min:0'],
            'target_fund' => ['nullable', 'integer', 'min:0'],
            'funded_amount' => ['nullable', 'integer', 'min:0'],
            'cap_rate' => ['nullable', 'numeric', 'min:0'],
            'cash_on_cash' => ['nullable', 'numeric', 'min:0'],
            'irr' => ['nullable', 'numeric', 'min:0'],
            'features' => ['nullable', 'array'],
            'features.*' => ['string', 'max:255'],
            'cover' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }
}
