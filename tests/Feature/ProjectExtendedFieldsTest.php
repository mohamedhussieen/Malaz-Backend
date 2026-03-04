<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProjectExtendedFieldsTest extends TestCase
{
    use RefreshDatabase;

    public function test_project_extended_fields_are_persisted_and_returned_in_public_and_admin_apis(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('secret'),
        ]);

        $token = $admin->createToken('admin')->plainTextToken;
        $headers = [
            'Authorization' => "Bearer {$token}",
            'Accept' => 'application/json',
        ];

        $create = $this->post('/api/v1/admin/projects', [
            'name_ar' => 'مشروع 996',
            'name_en' => 'Project 996',
            'description_ar' => 'وصف عربي',
            'description_en' => 'Lorem ipsum ...',
            'location_ar' => 'لاكينشير',
            'location_en' => 'Lakinshire',
            'is_featured_home' => false,
            'price' => 2100000,
            'status' => 'active',
            'valuation' => 25000000,
            'yield' => 12.5,
            'property_type' => ['villa', 'x', 'y', 'm'],
            'year_built' => 2018,
            'area_sqft' => 3200,
            'features' => ['Swimming Pool', 'Gym', 'Security', 'Parking'],
            'cover' => UploadedFile::fake()->image('project-cover.jpg'),
        ], $headers);

        $create->assertStatus(201);
        $create->assertJsonPath('status', true);
        $projectId = (int) $create->json('data.id');

        $this->assertDatabaseHas('projects', [
            'id' => $projectId,
            'price' => 2100000,
            'status' => 'active',
            'valuation' => 25000000,
            'year_built' => 2018,
            'area_sqft' => 3200,
        ]);

        $this->getJson("/api/v1/projects/{$projectId}")
            ->assertOk()
            ->assertJsonPath('data.name', 'Project 996')
            ->assertJsonPath('data.name_ar', 'مشروع 996')
            ->assertJsonPath('data.price', 2100000)
            ->assertJsonPath('data.status', 'active')
            ->assertJsonPath('data.valuation', 25000000)
            ->assertJsonPath('data.yield', 12.5)
            ->assertJsonPath('data.property_type.0', 'villa')
            ->assertJsonPath('data.year_built', 2018)
            ->assertJsonPath('data.area_sqft', 3200)
            ->assertJsonPath('data.features.0', 'Swimming Pool');

        $this->getJson('/api/v1/projects?per_page=10&page=1')
            ->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.items.0.price', 2100000)
            ->assertJsonPath('data.items.0.status', 'active')
            ->assertJsonPath('data.items.0.property_type.0', 'villa');

        $this->getJson("/api/v1/admin/projects/{$projectId}", $headers)
            ->assertOk()
            ->assertJsonPath('data.price', 2100000)
            ->assertJsonPath('data.status', 'active')
            ->assertJsonPath('data.features.1', 'Gym');
    }
}
