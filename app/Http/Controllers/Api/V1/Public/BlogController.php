<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Api\V1\BaseApiController;
use App\Http\Resources\V1\BlogDetailResource;
use App\Http\Resources\V1\BlogListResource;
use App\Services\BlogService;
use Illuminate\Http\Request;

class BlogController extends BaseApiController
{
    public function __construct(private readonly BlogService $blogService)
    {
    }

    public function index(Request $request)
    {
        $perPage = (int) $request->get('per_page', 10);
        $page = (int) $request->get('page', 1);

        $paginator = $this->blogService->paginatePublic($perPage, $page);
        $items = BlogListResource::collection($paginator->getCollection())->resolve();

        return $this->successResponse(
            $this->paginationPayload($paginator, $items),
            'تم الحصول على البيانات بنجاح',
            'Data fetched successfully'
        );
    }

    public function show(string $slug)
    {
        $blog = $this->blogService->findPublicBySlug($slug);

        return $this->successResponse(
            (new BlogDetailResource($blog))->resolve(),
            'تم الحصول على البيانات بنجاح',
            'Data fetched successfully'
        );
    }
}
