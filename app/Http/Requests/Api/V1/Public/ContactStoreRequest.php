<?php

namespace App\Http\Requests\Api\V1\Public;

use App\Http\Requests\Api\V1\ApiFormRequest;

class ContactStoreRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email'],
            'phone' => ['nullable', 'string', 'max:50'],
            'whatsapp' => ['nullable', 'string', 'max:50'],
            'note' => ['nullable', 'string'],
        ];
    }
}
