<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Http\Requests\Api\V1\ApiFormRequest;

class ProjectUpdateRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'name_ar' => ['sometimes', 'required', 'string', 'max:255'],
            'name_en' => ['sometimes', 'required', 'string', 'max:255'],
            'description_ar' => ['sometimes', 'required', 'string'],
            'description_en' => ['sometimes', 'required', 'string'],
            'location_ar' => ['sometimes', 'required', 'string', 'max:255'],
            'location_en' => ['sometimes', 'required', 'string', 'max:255'],
            'cover' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }
}
