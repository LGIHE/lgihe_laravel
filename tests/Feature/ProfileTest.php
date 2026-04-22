<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a test user
        $this->user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);
    }

    public function test_user_can_view_profile()
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/v1/profile');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'user' => [
                    'id' => $this->user->id,
                    'name' => 'Test User',
                    'email' => 'test@example.com',
                ],
            ]);
    }

    public function test_user_can_update_profile()
    {
        Sanctum::actingAs($this->user);

        $response = $this->putJson('/api/v1/profile', [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Profile updated successfully',
                'user' => [
                    'name' => 'Updated Name',
                    'email' => 'updated@example.com',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);
    }

    public function test_user_cannot_update_profile_with_existing_email()
    {
        $otherUser = User::factory()->create(['email' => 'other@example.com']);
        Sanctum::actingAs($this->user);

        $response = $this->putJson('/api/v1/profile', [
            'name' => 'Updated Name',
            'email' => 'other@example.com',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_user_can_change_password()
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/profile/change-password', [
            'current_password' => 'password123',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Password changed successfully',
            ]);

        // Verify password was changed
        $this->user->refresh();
        $this->assertTrue(Hash::check('newpassword123', $this->user->password));
    }

    public function test_user_cannot_change_password_with_wrong_current_password()
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/profile/change-password', [
            'current_password' => 'wrongpassword',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Current password is incorrect',
            ]);
    }

    public function test_user_can_delete_account()
    {
        Sanctum::actingAs($this->user);

        $response = $this->deleteJson('/api/v1/profile', [
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Account deleted successfully',
            ]);

        $this->assertDatabaseMissing('users', [
            'id' => $this->user->id,
        ]);
    }

    public function test_user_cannot_delete_account_with_wrong_password()
    {
        Sanctum::actingAs($this->user);

        $response = $this->deleteJson('/api/v1/profile', [
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Password is incorrect',
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
        ]);
    }

    public function test_unauthenticated_user_cannot_access_profile()
    {
        $response = $this->getJson('/api/v1/profile');
        $response->assertStatus(401);

        $response = $this->putJson('/api/v1/profile', [
            'name' => 'Test',
            'email' => 'test@example.com',
        ]);
        $response->assertStatus(401);

        $response = $this->postJson('/api/v1/profile/change-password', [
            'current_password' => 'password',
            'password' => 'newpassword',
            'password_confirmation' => 'newpassword',
        ]);
        $response->assertStatus(401);

        $response = $this->deleteJson('/api/v1/profile', [
            'password' => 'password',
        ]);
        $response->assertStatus(401);
    }
}