<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RBACTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed permissions
        $this->artisan('db:seed', ['--class' => 'PermissionSeeder']);
    }

    public function test_super_admin_role_has_all_permissions()
    {
        $superAdmin = Role::findByName('Super Admin');
        $allPermissions = Permission::all();
        
        $this->assertEquals($allPermissions->count(), $superAdmin->permissions->count());
    }

    public function test_user_with_super_admin_role_can_access_panel()
    {
        $user = User::factory()->create();
        $user->assignRole('Super Admin');
        
        $this->assertTrue($user->canAccessPanel(app(\Filament\Panel::class)));
    }

    public function test_user_without_role_cannot_access_panel()
    {
        $user = User::factory()->create();
        
        $this->assertFalse($user->canAccessPanel(app(\Filament\Panel::class)));
    }

    public function test_editor_can_create_news()
    {
        $user = User::factory()->create();
        $user->assignRole('Editor');
        
        $this->assertTrue($user->can('create_news'));
    }

    public function test_viewer_cannot_create_news()
    {
        $user = User::factory()->create();
        $user->assignRole('Viewer');
        
        $this->assertFalse($user->can('create_news'));
    }

    public function test_viewer_can_view_news()
    {
        $user = User::factory()->create();
        $user->assignRole('Viewer');
        
        $this->assertTrue($user->can('view_news'));
    }

    public function test_admin_cannot_manage_users()
    {
        $user = User::factory()->create();
        $user->assignRole('Admin');
        
        $this->assertFalse($user->can('create_users'));
        $this->assertFalse($user->can('update_users'));
        $this->assertFalse($user->can('delete_users'));
    }

    public function test_super_admin_can_manage_users()
    {
        $user = User::factory()->create();
        $user->assignRole('Super Admin');
        
        $this->assertTrue($user->can('create_users'));
        $this->assertTrue($user->can('update_users'));
        $this->assertTrue($user->can('delete_users'));
    }

    public function test_user_can_have_multiple_roles()
    {
        $user = User::factory()->create();
        $user->assignRole(['Editor', 'Viewer']);
        
        $this->assertTrue($user->hasRole('Editor'));
        $this->assertTrue($user->hasRole('Viewer'));
        $this->assertTrue($user->hasAnyRole(['Editor', 'Viewer']));
    }

    public function test_user_can_have_direct_permissions()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('view_news');
        
        $this->assertTrue($user->can('view_news'));
        $this->assertFalse($user->can('create_news'));
    }

    public function test_default_roles_exist()
    {
        $this->assertDatabaseHas('roles', ['name' => 'Super Admin']);
        $this->assertDatabaseHas('roles', ['name' => 'Admin']);
        $this->assertDatabaseHas('roles', ['name' => 'Editor']);
        $this->assertDatabaseHas('roles', ['name' => 'Viewer']);
    }

    public function test_all_resource_permissions_exist()
    {
        $expectedPermissions = [
            'view_users', 'create_users', 'update_users', 'delete_users',
            'view_roles', 'create_roles', 'update_roles', 'delete_roles',
            'view_applications', 'create_applications', 'update_applications', 'delete_applications',
            'view_contact_inquiries', 'create_contact_inquiries', 'update_contact_inquiries', 'delete_contact_inquiries',
            'view_events', 'create_events', 'update_events', 'delete_events',
            'view_job_listings', 'create_job_listings', 'update_job_listings', 'delete_job_listings',
            'view_news', 'create_news', 'update_news', 'delete_news',
            'view_analytics',
        ];

        foreach ($expectedPermissions as $permission) {
            $this->assertDatabaseHas('permissions', ['name' => $permission]);
        }
    }
}
