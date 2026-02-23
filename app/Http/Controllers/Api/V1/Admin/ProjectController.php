<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\V1\BaseApiController;
use App\Http\Requests\Api\V1\Admin\ProjectGalleryStoreRequest;
use App\Http\Requests\Api\V1\Admin\ProjectGalleryUpdateRequest;
use App\Http\Requests\Api\V1\Admin\ProjectStoreRequest;
use App\Http\Requests\Api\V1\Admin\ProjectUpdateRequest;
use App\Http\Resources\V1\ProjectDetailResource;
use App\Http\Resources\V1\ProjectListResource;
use App\Models\Project;
use App\Models\ProjectImage;
use App\Services\MediaService;
use App\Services\ProjectService;
use App\Support\MediaUrl;
use Illuminate\Http\Request;

class ProjectController extends BaseApiController
{
    public function __construct(
        private readonly ProjectService $projectService,
        private readonly MediaService $mediaService
    ) {
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

    public function store(ProjectStoreRequest $request)
    {
        $project = $this->projectService->create(
            $request->validated(),
            $request->file('cover'),
            $this->mediaService
        );

        return $this->successResponse(
            (new ProjectDetailResource($project->load('images')))->resolve(),
            'تم إنشاء العنصر بنجاح',
            'Created successfully',
            201
        );
    }

    public function show(Project $project)
    {
        $project->load('images');

        return $this->successResponse(
            (new ProjectDetailResource($project))->resolve(),
            'تم الحصول على البيانات بنجاح',
            'Data fetched successfully'
        );
    }

    public function update(ProjectUpdateRequest $request, Project $project)
    {
        $project = $this->projectService->update(
            $project,
            $request->validated(),
            $request->file('cover'),
            $this->mediaService
        );

        $project->load('images');

        return $this->successResponse(
            (new ProjectDetailResource($project))->resolve(),
            'تم تحديث العنصر بنجاح',
            'Updated successfully'
        );
    }

    public function destroy(Project $project)
    {
        $project->load('images');
        $this->projectService->delete($project, $this->mediaService);

        return $this->successResponse(
            null,
            'تم حذف العنصر بنجاح',
            'Deleted successfully'
        );
    }

    public function storeGallery(ProjectGalleryStoreRequest $request, Project $project)
    {
        $image = $this->projectService->addGalleryImage(
            $project,
            $request->file('image'),
            $request->get('name'),
            (int) $request->get('sort_order', 0),
            $this->mediaService
        );

        return $this->successResponse(
            [
                'id' => $image->id,
                'name' => $image->name,
                'url' => MediaUrl::toUrl($image->path),
                'sort_order' => $image->sort_order,
            ],
            'تم إضافة الصورة بنجاح',
            'Image added successfully',
            201
        );
    }

    public function updateGallery(ProjectGalleryUpdateRequest $request, Project $project, ProjectImage $image)
    {
        if ($image->project_id !== $project->id) {
            return $this->errorResponse([], 'غير موجود', 'Not found', 404);
        }

        $data = [];
        if ($request->exists('name')) {
            $data['name'] = $request->get('name');
        }

        if ($request->exists('sort_order')) {
            $data['sort_order'] = (int) $request->get('sort_order');
        }

        $image = $this->projectService->updateGalleryImage($image, $request->file('image'), $data, $this->mediaService);

        return $this->successResponse(
            [
                'id' => $image->id,
                'name' => $image->name,
                'url' => MediaUrl::toUrl($image->path),
                'sort_order' => $image->sort_order,
            ],
            'تم تحديث الصورة بنجاح',
            'Image updated successfully'
        );
    }

    public function destroyGallery(Project $project, ProjectImage $image)
    {
        if ($image->project_id !== $project->id) {
            return $this->errorResponse([], 'غير موجود', 'Not found', 404);
        }

        $this->projectService->deleteGalleryImage($image, $this->mediaService);

        return $this->successResponse(
            null,
            'تم حذف الصورة بنجاح',
            'Image deleted successfully'
        );
    }
}
