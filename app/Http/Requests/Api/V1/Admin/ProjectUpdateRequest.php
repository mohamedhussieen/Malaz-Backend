<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Http\Requests\Api\V1\ApiFormRequest;

class ProjectUpdateRequest extends ApiFormRequest
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
            'name_ar' => ['sometimes', 'required', 'string', 'max:255'],
            'name_en' => ['sometimes', 'required', 'string', 'max:255'],
            'description_ar' => ['sometimes', 'required', 'string'],
            'description_en' => ['sometimes', 'required', 'string'],
            'location_ar' => ['sometimes', 'required', 'string', 'max:255'],
            'location_en' => ['sometimes', 'required', 'string', 'max:255'],
            'owner_name_ar' => ['sometimes', 'nullable', 'string', 'max:255'],
            'owner_name_en' => ['sometimes', 'nullable', 'string', 'max:255'],
            'owner_title_ar' => ['sometimes', 'nullable', 'string', 'max:255'],
            'owner_title_en' => ['sometimes', 'nullable', 'string', 'max:255'],
            'owner_avatar_url' => ['sometimes', 'nullable', 'string', 'max:2048'],
            'owner_avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'is_featured_home' => ['sometimes', 'boolean'],
            'project_hero_section' => ['sometimes', 'boolean'],
            'price' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'status' => ['sometimes', 'nullable', 'string', 'in:available,not available,sold out,comming soon'],
            'valuation' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'yield' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'property_type' => ['sometimes', 'nullable', 'array'],
            'property_type.*' => ['string', 'max:100'],
            'year_built' => ['sometimes', 'nullable', 'integer', 'between:1900,2100'],
            'area_sqft' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'min_investment' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'target_fund' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'funded_amount' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'cap_rate' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'cash_on_cash' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'irr' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'features' => ['sometimes', 'nullable', 'array'],
            'features.*' => ['string', 'max:255'],
            'cover' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ];
    }
}
