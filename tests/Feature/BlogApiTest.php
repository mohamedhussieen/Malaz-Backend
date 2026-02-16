<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BlogApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_blog_routes_require_auth_and_return_401(): void
    {
        $response = $this->postJson('/api/v1/admin/blogs', [
            'title_ar' => 'Title Ar',
            'title_en' => 'Title',
            'paragraphs' => [
                [
                    'content_ar' => 'A',
                    'content_en' => 'B',
                ],
            ],
        ]);

        $response->assertStatus(401);
        $response->assertJson([
            'status' => false,
            'status_code' => 401,
        ]);
    }

    public function test_admin_blog_create_succeeds_when_paragraphs_missing(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $response = $this->postJson('/api/v1/admin/blogs', [
            'title_ar' => 'No Paragraphs',
            'title_en' => 'Without Paragraphs',
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('status', true);
        $response->assertJsonCount(0, 'data.paragraphs');
    }

    public function test_admin_blog_crud_and_public_endpoints_work_with_paragraphs(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $createPayload = [
            'title_ar' => 'Blog Ar',
            'title_en' => 'API Blog',
            'excerpt_ar' => 'Excerpt Ar',
            'excerpt_en' => 'Excerpt',
            'is_published' => true,
            'paragraphs' => [
                [
                    'header_ar' => 'Intro Ar',
                    'header_en' => 'Introduction',
                    'content_ar' => 'First paragraph ar',
                    'content_en' => 'First paragraph',
                    'sort_order' => 1,
                ],
                [
                    'header_ar' => 'Details Ar',
                    'header_en' => 'Details',
                    'content_ar' => 'Second paragraph ar',
                    'content_en' => 'Second paragraph',
                    'sort_order' => 2,
                ],
            ],
        ];

        $create = $this->postJson('/api/v1/admin/blogs', $createPayload);
        $create->assertStatus(201);
        $create->assertJsonPath('status', true);
        $create->assertJsonCount(2, 'data.paragraphs');

        $blogId = $create->json('data.id');
        $slug = $create->json('data.slug');

        $this->assertDatabaseHas('blogs', [
            'id' => $blogId,
            'slug' => $slug,
        ]);
        $this->assertDatabaseCount('blog_paragraphs', 2);

        $adminList = $this->getJson('/api/v1/admin/blogs?per_page=10&page=1');
        $adminList->assertOk();
        $adminList->assertJsonPath('status', true);

        $adminShow = $this->getJson("/api/v1/admin/blogs/{$blogId}");
        $adminShow->assertOk();
        $adminShow->assertJsonCount(2, 'data.paragraphs');

        $update = $this->putJson("/api/v1/admin/blogs/{$blogId}", [
            'title_ar' => 'Updated Ar',
            'title_en' => 'Updated API Blog',
            'paragraphs' => [
                [
                    'header_ar' => 'Single Ar',
                    'header_en' => 'Single Section',
                    'content_ar' => 'Replaced ar',
                    'content_en' => 'Replaced all paragraphs',
                    'sort_order' => 1,
                ],
            ],
        ]);
        $update->assertOk();
        $update->assertJsonCount(1, 'data.paragraphs');
        $this->assertDatabaseCount('blog_paragraphs', 1);

        $publicList = $this->getJson('/api/v1/blogs?per_page=10&page=1');
        $publicList->assertOk();
        $publicList->assertJsonPath('status', true);

        $publicShow = $this->getJson("/api/v1/blogs/{$slug}");
        $publicShow->assertOk();
        $publicShow->assertJsonCount(1, 'data.paragraphs');

        $delete = $this->deleteJson("/api/v1/admin/blogs/{$blogId}");
        $delete->assertOk();
        $this->assertDatabaseMissing('blogs', ['id' => $blogId]);
        $this->assertDatabaseCount('blog_paragraphs', 0);
    }
}