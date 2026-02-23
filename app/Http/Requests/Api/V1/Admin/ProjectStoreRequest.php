<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Http\Requests\Api\V1\ApiFormRequest;

class ProjectStoreRequest extends ApiFormRequest
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
            'name_ar' => ['required', 'string', 'max:255'],
            'name_en' => ['required', 'string', 'max:255'],
            'description_ar' => ['required', 'string'],
            'description_en' => ['required', 'string'],
            'location_ar' => ['required', 'string', 'max:255'],
            'location_en' => ['required', 'string', 'max:255'],
            'is_featured_home' => ['nullable', 'boolean'],
            'cover' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }
}
