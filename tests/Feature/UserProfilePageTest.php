<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class UserProfilePageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create basic permissions and roles for testing
        Permission::create(['name' => 'view_users']);
        Role::create(['name' => 'Admin']);
        
        $this->user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);
        
        // Give user permission to access admin panel
        $this->user->givePermissionTo('view_users');
    }

    public function test_authenticated_user_can_access_profile_page()
    {
        $response = $this->actingAs($this->user)
            ->get('/admin/user-profile');

        $response->assertStatus(200);
        $response->assertSee('Profile Information');
        $response->assertSee('Update Password');
        $response->assertSee('Account Information');
    }

    public function test_unauthenticated_user_cannot_access_profile_page()
    {
        $response = $this->get('/admin/user-profile');

        $response->assertRedirect('/admin/login');
    }

    public function test_profile_page_displays_user_information()
    {
        $response = $this->actingAs($this->user)
            ->get('/admin/user-profile');

        $response->assertStatus(200);
        $response->assertSee($this->user->name);
        $response->assertSee($this->user->email);
        $response->assertSee($this->user->created_at->format('F j, Y'));
    }

    public function test_profile_page_shows_user_roles_if_present()
    {
        // Assign a role to the user
        $this->user->assignRole('Admin');

        $response = $this->actingAs($this->user)
            ->get('/admin/user-profile');

        $response->assertStatus(200);
        $response->assertSee('Roles');
        $response->assertSee('Admin');
    }
}