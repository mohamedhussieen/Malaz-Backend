<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Api\V1\BaseApiController;
use App\Http\Resources\V1\OwnerResource;
use App\Services\OwnerService;
use Illuminate\Http\Request;

class OwnerController extends BaseApiController
{
    public function __construct(private readonly OwnerService $ownerService)
    {
    }

    public function index(Request $request)
    {
        $perPage = (int) $request->get('per_page', 10);
        $page = (int) $request->get('page', 1);
        $search = $request->get('search');

        $paginator = $this->ownerService->paginate($perPage, $page, is_string($search) ? $search : null);
        $items = OwnerResource::collection($paginator->getCollection())->resolve();

        return $this->successResponse(
            $this->paginationPayload($paginator, $items),
            'تم الحصول على البيانات بنجاح',
            'Data fetched successfully'
        );
    }
}
