<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Api\V1\BaseApiController;
use App\Http\Resources\V1\HomeResource;
use App\Http\Resources\V1\ProjectListResource;
use App\Services\HomeService;
use App\Services\ProjectService;

class HomeController extends BaseApiController
{
    public function __construct(
        private readonly HomeService $homeService,
        private readonly ProjectService $projectService
    ) {
    }

    public function index()
    {
        $home = $this->homeService->get();
        $payload = (new HomeResource($home))->resolve();
        $payload['featured_projects'] = ProjectListResource::collection(
            $this->projectService->featuredForHome()
        )->resolve();

        return $this->successResponse(
            $payload,
            'تم الحصول على البيانات بنجاح',
            'Data fetched successfully'
        );
    }
}