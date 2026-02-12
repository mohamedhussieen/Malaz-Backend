<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Http\Requests\Api\V1\ApiFormRequest;
use App\Enums\PlatformLinkKey;
use Illuminate\Validation\Rule;

class PlatformLinkUpsertRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'url' => ['required', 'url'],
            'is_active' => ['sometimes', 'boolean'],
            'key' => ['sometimes', Rule::in(array_column(PlatformLinkKey::cases(), 'value'))],
        ];
    }
}
