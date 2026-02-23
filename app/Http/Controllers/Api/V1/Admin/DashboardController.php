<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\V1\BaseApiController;
use App\Models\ContactMessage;
use App\Models\Owner;
use App\Models\Project;
use Illuminate\Support\Facades\Schema;

class DashboardController extends BaseApiController
{
    public function counts()
    {
        $featuredProjectsCount = Schema::hasColumn('projects', 'is_featured_home')
            ? Project::query()->where('is_featured_home', true)->count()
            : 0;

        return $this->successResponse(
            [
                'owners_count' => Owner::query()->count(),
                'projects_count' => Project::query()->count(),
                'featured_projects_count' => $featuredProjectsCount,
                'messages_count' => ContactMessage::query()->count(),
            ],
            'تم الحصول على البيانات بنجاح',
            'Data fetched successfully'
        );
    }
}
