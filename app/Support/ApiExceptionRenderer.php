<?php

namespace App\Support;

use App\Traits\ApiResponseTrait;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class ApiExceptionRenderer
{
    use ApiResponseTrait;

    public function render(Throwable $e): JsonResponse
    {
        if ($e instanceof ValidationException) {
            return $this->errorResponse(
                $e->errors(),
                'خطأ في التحقق من البيانات',
                'Validation error',
                422
            );
        }

        if ($e instanceof AuthenticationException) {
            return $this->errorResponse(
                [],
                'غير مصرح',
                'Unauthenticated',
                401
            );
        }

        if ($e instanceof AuthorizationException) {
            return $this->errorResponse(
                [],
                'غير مصرح',
                'Unauthorized',
                403
            );
        }

        if ($e instanceof ModelNotFoundException || $e instanceof NotFoundHttpException) {
            return $this->errorResponse(
                [],
                'غير موجود',
                'Not found',
                404
            );
        }

        if ($e instanceof HttpException) {
            return $this->errorResponse(
                [],
                'حدث خطأ',
                'An error occurred',
                $e->getStatusCode()
            );
        }

        return $this->errorResponse(
            [],
            'حدث خطأ',
            'An error occurred',
            500
        );
    }
}
