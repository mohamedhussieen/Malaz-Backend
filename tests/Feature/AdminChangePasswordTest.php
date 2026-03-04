<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminChangePasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_change_password(): void
    {
        $admin = User::factory()->create([
            'password' => Hash::make('old-password'),
        ]);

        $token = $admin->createToken('admin')->plainTextToken;
        $headers = $this->authHeaders($token);

        $response = $this->postJson('/api/v1/admin/auth/change-password', [
            'current_password' => 'old-password',
            'new_password' => 'new-password-123',
            'new_password_confirmation' => 'new-password-123',
        ], $headers);

        $response->assertOk();
        $response->assertJsonPath('status', true);

        $this->assertTrue(Hash::check('new-password-123', $admin->fresh()->password));
    }

    public function test_admin_cannot_change_password_with_invalid_current_password(): void
    {
        $admin = User::factory()->create([
            'password' => Hash::make('old-password'),
        ]);

        $token = $admin->createToken('admin')->plainTextToken;
        $headers = $this->authHeaders($token);

        $response = $this->postJson('/api/v1/admin/auth/change-password', [
            'current_password' => 'wrong-password',
            'new_password' => 'new-password-123',
            'new_password_confirmation' => 'new-password-123',
        ], $headers);

        $response->assertStatus(422);
        $response->assertJsonPath('status', false);
        $this->assertArrayHasKey('current_password', $response->json('errors'));
        $this->assertTrue(Hash::check('old-password', $admin->fresh()->password));
    }

    private function authHeaders(string $token): array
    {
        return [
            'Authorization' => "Bearer {$token}",
            'Accept' => 'application/json',
        ];
    }
}
