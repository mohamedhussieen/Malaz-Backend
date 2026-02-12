<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Enums\ContactMessageStatus;
use App\Http\Requests\Api\V1\ApiFormRequest;
use Illuminate\Validation\Rule;

class ContactMessageStatusRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(array_column(ContactMessageStatus::cases(), 'value'))],
        ];
    }
}
