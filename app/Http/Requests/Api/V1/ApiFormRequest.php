<?php

namespace App\Http\Requests\Api\V1;

use App\Traits\ApiResponseTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

abstract class ApiFormRequest extends FormRequest
{
    use ApiResponseTrait;

    public function authorize(): bool
    {
        return true;
    }

    protected function failedValidation(Validator $validator): void
    {
        $response = $this->errorResponse(
            $validator->errors(),
            'خطأ في التحقق من البيانات',
            'Validation error',
            422
        );

        throw new ValidationException($validator, $response);
    }
}
