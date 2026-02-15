<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Http\Requests\Api\V1\ApiFormRequest;
use App\Models\Blog;
use Illuminate\Validation\Rule;

class BlogUpdateRequest extends ApiFormRequest
{
    public function rules(): array
    {
        /** @var Blog|null $blog */
        $blog = $this->route('blog');

        return [
            'title_ar' => ['sometimes', 'required', 'string', 'max:255'],
            'title_en' => ['sometimes', 'required', 'string', 'max:255'],
            'excerpt_ar' => ['sometimes', 'nullable', 'string'],
            'excerpt_en' => ['sometimes', 'nullable', 'string'],
            'content_ar' => ['sometimes', 'required', 'string'],
            'content_en' => ['sometimes', 'required', 'string'],
            'slug' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
                'alpha_dash',
                Rule::unique('blogs', 'slug')->ignore($blog?->id),
            ],
            'cover' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'is_published' => ['sometimes', 'boolean'],
            'published_at' => ['sometimes', 'nullable', 'date'],
        ];
    }
}
