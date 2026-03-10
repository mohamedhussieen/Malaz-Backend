<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProjectExtendedFieldsTest extends TestCase
{
    use RefreshDatabase;

    public function test_project_extended_fields_are_persisted_and_returned_in_public_and_admin_apis(): void
    {
        Cache::flush();
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
            'project_hero_section' => true,
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
            ->assertJsonPath('data.project_hero_section', true)
            ->assertJsonPath('data.features.0', 'Swimming Pool');

        $this->getJson('/api/v1/projects?per_page=10&page=1')
            ->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.items.0.price', 2100000)
            ->assertJsonPath('data.items.0.status', 'active')
            ->assertJsonPath('data.items.0.project_hero_section', true)
            ->assertJsonPath('data.items.0.property_type.0', 'villa');

        $this->getJson("/api/v1/admin/projects/{$projectId}", $headers)
            ->assertOk()
            ->assertJsonPath('data.price', 2100000)
            ->assertJsonPath('data.status', 'active')
            ->assertJsonPath('data.project_hero_section', true)
            ->assertJsonPath('data.features.1', 'Gym');
    }

    public function test_project_hero_section_is_unique_and_can_be_filtered(): void
    {
        Cache::flush();
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

        $firstProject = $this->post('/api/v1/admin/projects', [
            'name_ar' => 'Ù…Ø´Ø±ÙˆØ¹ Ø£ÙˆÙ„',
            'name_en' => 'First Project',
            'description_ar' => 'ÙˆØµÙ Ø£ÙˆÙ„',
            'description_en' => 'First description',
            'location_ar' => 'Ø§Ù„Ø±ÙŠØ§Ø¶',
            'location_en' => 'Riyadh',
            'project_hero_section' => true,
        ], $headers);

        $firstProject->assertCreated();
        $firstId = (int) $firstProject->json('data.id');

        $secondProject = $this->post('/api/v1/admin/projects', [
            'name_ar' => 'Ù…Ø´Ø±ÙˆØ¹ Ø«Ø§Ù†ÙŠ',
            'name_en' => 'Second Project',
            'description_ar' => 'ÙˆØµÙ Ø«Ø§Ù†ÙŠ',
            'description_en' => 'Second description',
            'location_ar' => 'Ø¬Ø¯Ø©',
            'location_en' => 'Jeddah',
            'project_hero_section' => true,
        ], $headers);

        $secondProject->assertCreated();
        $secondId = (int) $secondProject->json('data.id');

        $this->assertDatabaseHas('projects', [
            'id' => $firstId,
            'project_hero_section' => 0,
        ]);

        $this->assertDatabaseHas('projects', [
            'id' => $secondId,
            'project_hero_section' => 1,
        ]);

        $this->getJson('/api/v1/projects?project_hero_section=true')
            ->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonCount(1, 'data.items')
            ->assertJsonPath('data.items.0.id', $secondId)
            ->assertJsonPath('data.items.0.project_hero_section', true);
    }
}
