<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Http\Requests\Api\V1\ApiFormRequest;

class ProjectUpdateRequest extends ApiFormRequest
{
    protected function prepareForValidation(): void
    {
        if (!$this->exists('is_featured_home')) {
            return;
        }

        $this->merge([
            'is_featured_home' => $this->normalizeBooleanInput($this->input('is_featured_home')),
        ]);
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
            'is_featured_home' => ['sometimes', 'boolean'],
            'price' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'status' => ['sometimes', 'nullable', 'string', 'in:active,inactive,sold,draft'],
            'valuation' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'yield' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'property_type' => ['sometimes', 'nullable', 'array'],
            'property_type.*' => ['string', 'max:100'],
            'year_built' => ['sometimes', 'nullable', 'integer', 'between:1900,2100'],
            'area_sqft' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'features' => ['sometimes', 'nullable', 'array'],
            'features.*' => ['string', 'max:255'],
            'cover' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }
}
