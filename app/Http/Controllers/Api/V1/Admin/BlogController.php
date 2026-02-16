<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\V1\BaseApiController;
use App\Http\Requests\Api\V1\Admin\BlogStoreRequest;
use App\Http\Requests\Api\V1\Admin\BlogUpdateRequest;
use App\Http\Resources\V1\BlogDetailResource;
use App\Http\Resources\V1\BlogListResource;
use App\Models\Blog;
use App\Services\BlogService;
use App\Services\MediaService;
use Illuminate\Http\Request;

class BlogController extends BaseApiController
{
    public function __construct(
        private readonly BlogService $blogService,
        private readonly MediaService $mediaService
    ) {
    }

    public function index(Request $request)
    {
        $perPage = (int) $request->get('per_page', 10);
        $page = (int) $request->get('page', 1);

        $paginator = $this->blogService->paginateAdmin($perPage, $page);
        $items = BlogListResource::collection($paginator->getCollection())->resolve();

        return $this->successResponse(
            $this->paginationPayload($paginator, $items),
            'تم الحصول على البيانات بنجاح',
            'Data fetched successfully'
        );
    }

    public function store(BlogStoreRequest $request)
    {
        $blog = $this->blogService->create(
            $request->validated(),
            $request->file('cover'),
            $this->mediaService
        );

        return $this->successResponse(
            (new BlogDetailResource($blog))->resolve(),
            'تم إنشاء العنصر بنجاح',
            'Created successfully',
            201
        );
    }

    public function show(Blog $blog)
    {
        $blog->load('paragraphs');

        return $this->successResponse(
            (new BlogDetailResource($blog))->resolve(),
            'تم الحصول على البيانات بنجاح',
            'Data fetched successfully'
        );
    }

    public function update(BlogUpdateRequest $request, Blog $blog)
    {
        $blog = $this->blogService->update(
            $blog,
            $request->validated(),
            $request->file('cover'),
            $this->mediaService
        );

        return $this->successResponse(
            (new BlogDetailResource($blog))->resolve(),
            'تم تحديث العنصر بنجاح',
            'Updated successfully'
        );
    }

    public function destroy(Blog $blog)
    {
        $this->blogService->delete($blog, $this->mediaService);

        return $this->successResponse(
            null,
            'تم حذف العنصر بنجاح',
            'Deleted successfully'
        );
    }
}
