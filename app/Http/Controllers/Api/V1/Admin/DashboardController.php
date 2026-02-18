<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\V1\BaseApiController;
use App\Models\ContactMessage;
use App\Models\Project;

class DashboardController extends BaseApiController
{
    public function counts()
    {
        return $this->successResponse(
            [
                'project_counts' => Project::query()->count(),
                'x_count' => ContactMessage::query()->count(),
            ],
            'تم الحصول على البيانات بنجاح',
            'Data fetched successfully'
        );
    }
}

