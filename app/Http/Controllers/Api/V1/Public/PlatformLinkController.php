<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Api\V1\BaseApiController;
use App\Http\Resources\V1\PlatformLinkResource;
use App\Services\PlatformLinkService;

class PlatformLinkController extends BaseApiController
{
    public function __construct(private readonly PlatformLinkService $platformLinkService)
    {
    }

    public function index()
    {
        $links = $this->platformLinkService->publicList();

        return $this->successResponse(
            PlatformLinkResource::collection($links)->resolve(),
            'تم الحصول على البيانات بنجاح',
            'Data fetched successfully'
        );
    }
}
