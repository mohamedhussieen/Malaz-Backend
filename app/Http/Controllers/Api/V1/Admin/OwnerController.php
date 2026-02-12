<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\V1\BaseApiController;
use App\Http\Requests\Api\V1\Admin\OwnerStoreRequest;
use App\Http\Requests\Api\V1\Admin\OwnerUpdateRequest;
use App\Http\Resources\V1\OwnerResource;
use App\Models\Owner;
use App\Services\MediaService;
use App\Services\OwnerService;
use Illuminate\Http\Request;

class OwnerController extends BaseApiController
{
    public function __construct(
        private readonly OwnerService $ownerService,
        private readonly MediaService $mediaService
    ) {
    }

    public function index(Request $request)
    {
        $perPage = (int) $request->get('per_page', 10);
        $page = (int) $request->get('page', 1);

        $paginator = $this->ownerService->paginate($perPage, $page);
        $items = OwnerResource::collection($paginator->getCollection())->resolve();

        return $this->successResponse(
            $this->paginationPayload($paginator, $items),
            'تم الحصول على البيانات بنجاح',
            'Data fetched successfully'
        );
    }

    public function store(OwnerStoreRequest $request)
    {
        $owner = $this->ownerService->create(
            $request->validated(),
            $request->file('avatar'),
            $this->mediaService
        );

        return $this->successResponse(
            (new OwnerResource($owner))->resolve(),
            'تم إنشاء العنصر بنجاح',
            'Created successfully',
            201
        );
    }

    public function show(Owner $owner)
    {
        return $this->successResponse(
            (new OwnerResource($owner))->resolve(),
            'تم الحصول على البيانات بنجاح',
            'Data fetched successfully'
        );
    }

    public function update(OwnerUpdateRequest $request, Owner $owner)
    {
        $owner = $this->ownerService->update(
            $owner,
            $request->validated(),
            $request->file('avatar'),
            $this->mediaService
        );

        return $this->successResponse(
            (new OwnerResource($owner))->resolve(),
            'تم تحديث العنصر بنجاح',
            'Updated successfully'
        );
    }

    public function destroy(Owner $owner)
    {
        $this->ownerService->delete($owner, $this->mediaService);

        return $this->successResponse(
            null,
            'تم حذف العنصر بنجاح',
            'Deleted successfully'
        );
    }
}
