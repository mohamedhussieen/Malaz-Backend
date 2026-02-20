<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\ProjectImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicProjectShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_project_show_returns_project_by_id(): void
    {
        $project = Project::query()->create([
            'name' => 'Tower',
            'name_ar' => 'برج',
            'name_en' => 'Tower',
            'description' => 'Project description',
            'description_ar' => 'وصف المشروع',
            'description_en' => 'Project description',
            'location' => 'Riyadh',
            'location_ar' => 'الرياض',
            'location_en' => 'Riyadh',
        ]);

        ProjectImage::query()->create([
            'project_id' => $project->id,
            'name' => 'Main render',
            'path' => 'projects/galleries/test.jpg',
            'sort_order' => 1,
        ]);

        $response = $this->getJson("/api/v1/projects/{$project->id}");

        $response->assertOk();
        $response->assertJsonPath('status', true);
        $response->assertJsonPath('data.id', $project->id);
        $response->assertJsonCount(1, 'data.gallery');
    }

    public function test_public_project_show_returns_404_for_missing_project(): void
    {
        $response = $this->getJson('/api/v1/projects/999999');

        $response->assertStatus(404);
        $response->assertJsonPath('status', false);
    }
}
