<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Api\V1\BaseApiController;
use App\Http\Resources\V1\HomeResource;
use App\Services\HomeService;

class HomeController extends BaseApiController
{
    public function __construct(private readonly HomeService $homeService)
    {
    }

    public function index()
    {
        $home = $this->homeService->get();

        return $this->successResponse(
            (new HomeResource($home))->resolve(),
            'تم الحصول على البيانات بنجاح',
            'Data fetched successfully'
        );
    }
}
