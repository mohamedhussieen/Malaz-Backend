<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\V1\BaseApiController;
use App\Models\ContactMessage;
use App\Models\Owner;
use App\Models\Project;

class DashboardController extends BaseApiController
{
    public function counts()
    {
        return $this->successResponse(
            [
                'owners_count' => Owner::query()->count(),
                'projects_count' => Project::query()->count(),
                'messages_count' => ContactMessage::query()->count(),
            ],
            'تم الحصول على البيانات بنجاح',
            'Data fetched successfully'
        );
    }
}
