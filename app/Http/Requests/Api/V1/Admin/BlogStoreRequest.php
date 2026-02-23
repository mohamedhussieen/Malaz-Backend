<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Http\Requests\Api\V1\ApiFormRequest;
use Illuminate\Validation\Rule;

class BlogStoreRequest extends ApiFormRequest
{
    protected function prepareForValidation(): void
    {
        if (!$this->exists('is_published')) {
            return;
        }

        $this->merge([
            'is_published' => $this->normalizeBooleanInput($this->input('is_published')),
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
            'title_ar' => ['required', 'string', 'max:255'],
            'title_en' => ['required', 'string', 'max:255'],
            'excerpt_ar' => ['nullable', 'string'],
            'excerpt_en' => ['nullable', 'string'],
            'content_ar' => ['nullable', 'string'],
            'content_en' => ['nullable', 'string'],
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash', Rule::unique('blogs', 'slug')],
            'cover' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'is_published' => ['sometimes', 'boolean'],
            'published_at' => ['nullable', 'date'],
            'paragraphs' => ['sometimes', 'array', 'min:1'],
            'paragraphs.*.header_ar' => ['nullable', 'string', 'max:255'],
            'paragraphs.*.header_en' => ['nullable', 'string', 'max:255'],
            'paragraphs.*.content_ar' => ['required_with:paragraphs', 'string'],
            'paragraphs.*.content_en' => ['required_with:paragraphs', 'string'],
            'paragraphs.*.sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
