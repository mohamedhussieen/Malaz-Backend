<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Api\V1\BaseApiController;
use App\Http\Resources\V1\ProjectDetailResource;
use App\Http\Resources\V1\ProjectListResource;
use App\Services\ProjectService;
use Illuminate\Http\Request;

class ProjectController extends BaseApiController
{
    public function __construct(private readonly ProjectService $projectService)
    {
    }

    public function index(Request $request)
    {
        $perPage = (int) $request->get('per_page', 10);
        $page = (int) $request->get('page', 1);
        $search = $request->get('search');
        $featured = filter_var($request->get('featured'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        $paginator = $this->projectService->paginate(
            $perPage,
            $page,
            is_string($search) ? $search : null,
            $featured
        );
        $items = ProjectListResource::collection($paginator->getCollection())->resolve();

        return $this->successResponse(
            $this->paginationPayload($paginator, $items),
            'تم الحصول على البيانات بنجاح',
            'Data fetched successfully'
        );
    }

    public function show(int $project)
    {
        $projectModel = $this->projectService->findWithImages($project);

        return $this->successResponse(
            (new ProjectDetailResource($projectModel))->resolve(),
            'تم الحصول على البيانات بنجاح',
            'Data fetched successfully'
        );
    }
}
