<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\PlatformLinkKey;
use App\Http\Controllers\Api\V1\BaseApiController;
use App\Http\Requests\Api\V1\Admin\PlatformLinkUpsertRequest;
use App\Http\Resources\V1\PlatformLinkResource;
use App\Models\PlatformLink;
use App\Services\PlatformLinkService;

class PlatformLinkController extends BaseApiController
{
    public function __construct(private readonly PlatformLinkService $platformLinkService)
    {
    }

    public function index()
    {
        $links = $this->platformLinkService->adminList();

        return $this->successResponse(
            PlatformLinkResource::collection($links)->resolve(),
            'تم الحصول على البيانات بنجاح',
            'Data fetched successfully'
        );
    }

    public function upsert(PlatformLinkUpsertRequest $request, string $key)
    {
        if (!in_array($key, array_column(PlatformLinkKey::cases(), 'value'), true)) {
            return $this->errorResponse(
                ['key' => ['Invalid platform key']],
                'خطأ في التحقق من البيانات',
                'Validation error',
                422
            );
        }

        $data = $request->validated();
        $data['key'] = $key;

        $platformLink = $this->platformLinkService->upsert($key, $data);

        return $this->successResponse(
            (new PlatformLinkResource($platformLink))->resolve(),
            'تم تحديث البيانات بنجاح',
            'Updated successfully'
        );
    }

    public function toggle(PlatformLink $platformLink)
    {
        $platformLink = $this->platformLinkService->toggle($platformLink);

        return $this->successResponse(
            (new PlatformLinkResource($platformLink))->resolve(),
            'تم تحديث البيانات بنجاح',
            'Updated successfully'
        );
    }
}
