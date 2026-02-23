<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ApiV1AllEndpointsTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_api_v1_endpoints_smoke_flow(): void
    {
        Storage::fake('public');

        $contact = $this->postJson('/api/v1/contact', [
            'name' => 'API User',
            'email' => 'api@example.com',
            'phone' => '01012345678',
            'whatsapp' => '01012345678',
            'msg' => 'Need proposal',
        ]);
        $contact->assertStatus(201);
        $contact->assertJsonPath('status', true);
        $contactId = (int) $contact->json('data.id');

        $this->getJson('/api/v1/home')->assertOk()->assertJsonPath('status', true);
        $this->getJson('/api/v1/owners?per_page=10&page=1')->assertOk()->assertJsonPath('status', true);
        $this->getJson('/api/v1/projects?per_page=10&page=1')->assertOk()->assertJsonPath('status', true);
        $this->getJson('/api/v1/blogs?per_page=10&page=1')->assertOk()->assertJsonPath('status', true);
        $this->getJson('/api/v1/platforms')->assertOk()->assertJsonPath('status', true);

        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('secret'),
        ]);

        $login = $this->postJson('/api/v1/admin/auth/login', [
            'email' => 'admin@example.com',
            'password' => 'secret',
        ]);
        $login->assertOk();
        $login->assertJsonPath('status', true);
        $token = (string) $login->json('data.token');
        $headers = $this->authHeaders($token);

        $this->getJson('/api/v1/admin/me', $headers)->assertOk()->assertJsonPath('status', true);

        $ownerCreate = $this->post('/api/v1/admin/owners', [
            'name_ar' => 'Owner Ar',
            'name_en' => 'Owner En',
            'title_ar' => 'Title Ar',
            'title_en' => 'Title En',
            'bio_ar' => 'Bio Ar',
            'bio_en' => 'Bio En',
            'avatar' => UploadedFile::fake()->image('owner.jpg'),
        ], $headers);
        $ownerCreate->assertStatus(201);
        $ownerCreate->assertJsonPath('status', true);
        $ownerId = (int) $ownerCreate->json('data.id');

        $this->getJson('/api/v1/admin/owners?per_page=10&page=1', $headers)->assertOk()->assertJsonPath('status', true);
        $this->getJson("/api/v1/admin/owners/{$ownerId}", $headers)->assertOk()->assertJsonPath('status', true);
        $ownerUpdate = $this->post("/api/v1/admin/owners/{$ownerId}", [
            '_method' => 'PUT',
            'name_ar' => 'Owner Ar Updated',
            'name_en' => 'Owner En Updated',
            'title_ar' => 'Title Ar Updated',
            'title_en' => 'Title En Updated',
            'bio_ar' => 'Bio Ar Updated',
            'bio_en' => 'Bio En Updated',
            'avatar' => UploadedFile::fake()->image('owner-updated.jpg'),
        ], $headers);
        $ownerUpdate->assertOk();
        $ownerUpdate->assertJsonPath('status', true);
        $this->assertDatabaseHas('owners', [
            'id' => $ownerId,
            'name_en' => 'Owner En Updated',
            'title_en' => 'Title En Updated',
        ]);

        $projectCreate = $this->post('/api/v1/admin/projects', [
            'name_ar' => 'Project Ar',
            'name_en' => 'Project En',
            'description_ar' => 'Description Ar',
            'description_en' => 'Description En',
            'location_ar' => 'Riyadh Ar',
            'location_en' => 'Riyadh En',
            'is_featured_home' => '1',
            'cover' => UploadedFile::fake()->image('project-cover.jpg'),
        ], $headers);
        $projectCreate->assertStatus(201);
        $projectCreate->assertJsonPath('status', true);
        $projectId = (int) $projectCreate->json('data.id');

        $this->getJson('/api/v1/admin/projects?per_page=10&page=1', $headers)->assertOk()->assertJsonPath('status', true);
        $this->getJson("/api/v1/admin/projects/{$projectId}", $headers)->assertOk()->assertJsonPath('status', true);
        $this->getJson("/api/v1/projects/{$projectId}")->assertOk()->assertJsonPath('status', true);
        $projectUpdate = $this->post("/api/v1/admin/projects/{$projectId}", [
            '_method' => 'PUT',
            'name_ar' => 'Project Ar Updated',
            'name_en' => 'Project En Updated',
            'description_ar' => 'Description Ar Updated',
            'description_en' => 'Description En Updated',
            'location_ar' => 'Jeddah Ar',
            'location_en' => 'Jeddah En',
            'is_featured_home' => 'true',
            'cover' => UploadedFile::fake()->image('project-cover-updated.jpg'),
        ], $headers);
        $projectUpdate->assertOk();
        $projectUpdate->assertJsonPath('status', true);
        $projectUpdate->assertJsonPath('data.is_featured_home', true);
        $this->assertDatabaseHas('projects', [
            'id' => $projectId,
            'name_en' => 'Project En Updated',
            'location_en' => 'Jeddah En',
            'is_featured_home' => 1,
        ]);

        $projectGalleryAdd = $this->post("/api/v1/admin/projects/{$projectId}/gallery", [
            'image' => UploadedFile::fake()->image('gallery.jpg'),
            'name' => 'Main render',
            'sort_order' => 1,
        ], $headers);
        $projectGalleryAdd->assertStatus(201);
        $projectGalleryAdd->assertJsonPath('status', true);
        $projectImageId = (int) $projectGalleryAdd->json('data.id');

        $projectGalleryUpdate = $this->post("/api/v1/admin/projects/{$projectId}/gallery/{$projectImageId}", [
            '_method' => 'PATCH',
            'name' => 'Main render updated',
            'sort_order' => 2,
        ], $headers);
        $projectGalleryUpdate->assertOk();
        $projectGalleryUpdate->assertJsonPath('status', true);
        $this->assertDatabaseHas('project_images', [
            'id' => $projectImageId,
            'name' => 'Main render updated',
            'sort_order' => 2,
        ]);

        $blogCreate = $this->post('/api/v1/admin/blogs', [
            'title_ar' => 'Blog Ar',
            'title_en' => 'Blog En',
            'excerpt_ar' => 'Excerpt Ar',
            'excerpt_en' => 'Excerpt En',
            'content_ar' => 'Content Ar',
            'content_en' => 'Content En',
            'is_published' => '1',
            'cover' => UploadedFile::fake()->image('blog-cover.jpg'),
            'paragraphs' => [
                [
                    'header_ar' => 'Intro Ar',
                    'header_en' => 'Intro En',
                    'content_ar' => 'Paragraph Ar',
                    'content_en' => 'Paragraph En',
                    'sort_order' => 1,
                ],
            ],
        ], $headers);
        $blogCreate->assertStatus(201);
        $blogCreate->assertJsonPath('status', true);
        $blogId = (int) $blogCreate->json('data.id');
        $blogSlug = (string) $blogCreate->json('data.slug');

        $this->getJson('/api/v1/admin/blogs?per_page=10&page=1', $headers)->assertOk()->assertJsonPath('status', true);
        $this->getJson("/api/v1/admin/blogs/{$blogId}", $headers)->assertOk()->assertJsonPath('status', true);

        $blogUpdate = $this->post("/api/v1/admin/blogs/{$blogId}", [
            '_method' => 'PUT',
            'title_ar' => 'Blog Ar Updated',
            'title_en' => 'Blog En Updated',
            'excerpt_ar' => 'Updated excerpt ar',
            'excerpt_en' => 'Updated excerpt en',
            'content_ar' => 'Updated content ar',
            'content_en' => 'Updated content en',
            'is_published' => 'true',
            'cover' => UploadedFile::fake()->image('blog-cover-updated.jpg'),
            'paragraphs' => [
                [
                    'header_ar' => 'Section Ar',
                    'header_en' => 'Section En',
                    'content_ar' => 'Updated paragraph ar',
                    'content_en' => 'Updated paragraph en',
                    'sort_order' => 1,
                ],
            ],
        ], $headers);
        $blogUpdate->assertOk();
        $blogUpdate->assertJsonPath('status', true);
        $blogUpdate->assertJsonPath('data.is_published', true);
        $this->assertDatabaseHas('blogs', [
            'id' => $blogId,
            'title_en' => 'Blog En Updated',
            'is_published' => 1,
        ]);

        $this->getJson("/api/v1/blogs/{$blogSlug}")->assertOk()->assertJsonPath('status', true);

        $this->getJson('/api/v1/admin/home', $headers)->assertOk()->assertJsonPath('status', true);
        $this->putJson('/api/v1/admin/home', [
            'headline_text_ar' => 'Headline Ar',
            'headline_text_en' => 'Headline En',
            'body_text_ar' => 'Body Ar',
            'body_text_en' => 'Body En',
            'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        ], $headers)->assertOk()->assertJsonPath('status', true);

        $homeHeroAdd = $this->post('/api/v1/admin/home/hero-gallery', [
            'image' => UploadedFile::fake()->image('home-hero.jpg'),
            'name' => 'Skyline Hero',
            'sort_order' => 1,
        ], $headers);
        $homeHeroAdd->assertStatus(201);
        $homeHeroAdd->assertJsonPath('status', true);
        $homeImageId = (int) $homeHeroAdd->json('data.id');

        $homeHeroUpdate = $this->post("/api/v1/admin/home/hero-gallery/{$homeImageId}", [
            '_method' => 'PATCH',
            'name' => 'Skyline Hero Updated',
            'sort_order' => 2,
        ], $headers);
        $homeHeroUpdate->assertOk()->assertJsonPath('status', true);
        $this->assertDatabaseHas('home_images', [
            'id' => $homeImageId,
            'name' => 'Skyline Hero Updated',
            'sort_order' => 2,
        ]);

        $platformUpsert = $this->putJson('/api/v1/admin/platform-links/facebook', [
            'url' => 'https://facebook.com/malaz',
            'is_active' => true,
        ], $headers);
        $platformUpsert->assertOk();
        $platformUpsert->assertJsonPath('status', true);
        $platformLinkId = (int) $platformUpsert->json('data.id');

        $this->getJson('/api/v1/admin/platform-links', $headers)->assertOk()->assertJsonPath('status', true);
        $this->patchJson("/api/v1/admin/platform-links/{$platformLinkId}/toggle", [], $headers)->assertOk()->assertJsonPath('status', true);

        $this->getJson('/api/v1/admin/contact-messages?per_page=10&page=1', $headers)->assertOk()->assertJsonPath('status', true);
        $this->getJson("/api/v1/admin/contact-messages/{$contactId}", $headers)->assertOk()->assertJsonPath('status', true);
        $this->patchJson("/api/v1/admin/contact-messages/{$contactId}/status", [
            'status' => 'read',
        ], $headers)->assertOk()->assertJsonPath('status', true);

        $this->getJson('/api/v1/admin/dashboard/counts', $headers)->assertOk()->assertJsonPath('status', true);

        $this->deleteJson("/api/v1/admin/home/hero-gallery/{$homeImageId}", [], $headers)->assertOk()->assertJsonPath('status', true);
        $this->deleteJson("/api/v1/admin/projects/{$projectId}/gallery/{$projectImageId}", [], $headers)->assertOk()->assertJsonPath('status', true);
        $this->deleteJson("/api/v1/admin/blogs/{$blogId}", [], $headers)->assertOk()->assertJsonPath('status', true);
        $this->deleteJson("/api/v1/admin/projects/{$projectId}", [], $headers)->assertOk()->assertJsonPath('status', true);
        $this->deleteJson("/api/v1/admin/owners/{$ownerId}", [], $headers)->assertOk()->assertJsonPath('status', true);
        $this->deleteJson("/api/v1/admin/contact-messages/{$contactId}", [], $headers)->assertOk()->assertJsonPath('status', true);

        $this->postJson('/api/v1/admin/auth/logout', [], $headers)->assertOk()->assertJsonPath('status', true);
        $this->assertNotNull($admin->fresh());
    }

    private function authHeaders(string $token): array
    {
        return [
            'Authorization' => "Bearer {$token}",
            'Accept' => 'application/json',
        ];
    }
}
