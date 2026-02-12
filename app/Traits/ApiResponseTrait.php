<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\MessageBag;

trait ApiResponseTrait
{
    protected function successResponse(
        mixed $data,
        string $messageAr,
        string $messageEn,
        int $statusCode = 200
    ): JsonResponse {
        $locale = app()->getLocale() === 'ar' ? 'ar' : 'en';

        return response()->json([
            'status' => true,
            'status_code' => $statusCode,
            'message' => $locale === 'ar' ? $messageAr : $messageEn,
            'message_ar' => $messageAr,
            'message_en' => $messageEn,
            'data' => $data,
        ], $statusCode);
    }

    protected function errorResponse(
        mixed $errors,
        string $messageAr,
        string $messageEn,
        int $statusCode
    ): JsonResponse {
        $locale = app()->getLocale() === 'ar' ? 'ar' : 'en';

        if (is_array($errors) && $errors === []) {
            $errors = (object) [];
        }

        return response()->json([
            'status' => false,
            'status_code' => $statusCode,
            'message' => $locale === 'ar' ? $messageAr : $messageEn,
            'message_ar' => $messageAr,
            'message_en' => $messageEn,
            'errors' => $errors instanceof MessageBag ? $errors->toArray() : $errors,
        ], $statusCode);
    }

    protected function paginationPayload($paginator, array $items): array
    {
        return [
            'items' => $items,
            'pagination' => [
                'page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ];
    }
}
