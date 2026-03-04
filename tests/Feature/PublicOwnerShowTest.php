<?php

namespace Tests\Feature;

use App\Models\Owner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicOwnerShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_owner_show_returns_owner_by_id(): void
    {
        $owner = Owner::query()->create([
            'name' => 'Owner En',
            'name_ar' => 'مالك',
            'name_en' => 'Owner En',
            'title' => 'CEO',
            'title_ar' => 'المدير التنفيذي',
            'title_en' => 'CEO',
            'bio' => 'Owner bio',
            'bio_ar' => 'نبذة عن المالك',
            'bio_en' => 'Owner bio',
            'avatar_path' => 'owners/avatars/owner.jpg',
        ]);

        $response = $this->getJson("/api/v1/owners/{$owner->id}");

        $response->assertOk();
        $response->assertJsonPath('status', true);
        $response->assertJsonPath('data.id', $owner->id);
    }

    public function test_public_owner_show_returns_404_for_missing_owner(): void
    {
        $response = $this->getJson('/api/v1/owners/999999');

        $response->assertStatus(404);
        $response->assertJsonPath('status', false);
    }
}
