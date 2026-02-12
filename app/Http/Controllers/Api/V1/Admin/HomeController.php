<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\V1\BaseApiController;
use App\Http\Requests\Api\V1\Admin\HomeHeroImageRequest;
use App\Http\Requests\Api\V1\Admin\HomeUpdateRequest;
use App\Http\Resources\V1\HomeResource;
use App\Models\HomeImage;
use App\Services\HomeService;
use App\Services\MediaService;
use App\Support\MediaUrl;

class HomeController extends BaseApiController
{
    public function __construct(
        private readonly HomeService $homeService,
        private readonly MediaService $mediaService
    ) {
    }

    public function show()
    {
        $home = $this->homeService->get();

        return $this->successResponse(
            (new HomeResource($home))->resolve(),
            'تم الحصول على البيانات بنجاح',
            'Data fetched successfully'
        );
    }

    public function update(HomeUpdateRequest $request)
    {
        $home = $this->homeService->get();
        $home = $this->homeService->update($home, $request->validated());
        $home->load('images');

        return $this->successResponse(
            (new HomeResource($home))->resolve(),
            'تم تحديث البيانات بنجاح',
            'Updated successfully'
        );
    }

    public function storeHeroImage(HomeHeroImageRequest $request)
    {
        $home = $this->homeService->get();
        $image = $this->homeService->addHeroImage(
            $home,
            $request->file('image'),
            (int) $request->get('sort_order', 0),
            $this->mediaService
        );

        return $this->successResponse(
            [
                'id' => $image->id,
                'url' => MediaUrl::toUrl($image->path),
                'sort_order' => $image->sort_order,
            ],
            'تم إضافة الصورة بنجاح',
            'Image added successfully',
            201
        );
    }

    public function destroyHeroImage(HomeImage $image)
    {
        $this->homeService->deleteHeroImage($image, $this->mediaService);

        return $this->successResponse(
            null,
            'تم حذف الصورة بنجاح',
            'Image deleted successfully'
        );
    }
}
