<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Http\Requests\Api\V1\ApiFormRequest;

class HomeUpdateRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'headline_text_ar' => ['sometimes', 'required', 'string', 'max:255'],
            'headline_text_en' => ['sometimes', 'required', 'string', 'max:255'],
            'body_text_ar' => ['sometimes', 'required', 'string'],
            'body_text_en' => ['sometimes', 'required', 'string'],
            'youtube_url' => ['nullable', 'url'],
        ];
    }
}
