<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define all resources and their actions
        $resources = [
            'users' => ['view', 'create', 'update', 'delete'],
            'roles' => ['view', 'create', 'update', 'delete'],
            'applications' => ['view', 'create', 'update', 'delete'],
            'contact_inquiries' => ['view', 'create', 'update', 'delete'],
            'events' => ['view', 'create', 'update', 'delete'],
            'job_listings' => ['view', 'create', 'update', 'delete'],
            'news' => ['view', 'create', 'update', 'delete'],
            'analytics' => ['view'],
        ];

        // Create permissions
        foreach ($resources as $resource => $actions) {
            foreach ($actions as $action) {
                Permission::firstOrCreate([
                    'name' => "{$action}_{$resource}",
                    'guard_name' => 'web',
                ]);
            }
        }

        // Create default roles with permissions
        $this->createSuperAdminRole();
        $this->createAdminRole();
        $this->createEditorRole();
        $this->createViewerRole();
    }

    private function createSuperAdminRole(): void
    {
        $role = Role::firstOrCreate([
            'name' => 'Super Admin',
            'guard_name' => 'web',
        ]);

        // Super Admin gets all permissions
        $role->syncPermissions(Permission::all());
    }

    private function createAdminRole(): void
    {
        $role = Role::firstOrCreate([
            'name' => 'Admin',
            'guard_name' => 'web',
        ]);

        // Admin gets most permissions except user/role management
        $permissions = Permission::where('name', 'not like', '%_users')
            ->where('name', 'not like', '%_roles')
            ->get();
        
        $role->syncPermissions($permissions);
    }

    private function createEditorRole(): void
    {
        $role = Role::firstOrCreate([
            'name' => 'Editor',
            'guard_name' => 'web',
        ]);

        // Editor can manage content but not users/roles
        $permissions = Permission::whereIn('name', [
            'view_applications',
            'update_applications',
            'view_contact_inquiries',
            'update_contact_inquiries',
            'view_events',
            'create_events',
            'update_events',
            'delete_events',
            'view_job_listings',
            'create_job_listings',
            'update_job_listings',
            'delete_job_listings',
            'view_news',
            'create_news',
            'update_news',
            'delete_news',
        ])->get();

        $role->syncPermissions($permissions);
    }

    private function createViewerRole(): void
    {
        $role = Role::firstOrCreate([
            'name' => 'Viewer',
            'guard_name' => 'web',
        ]);

        // Viewer can only view content
        $permissions = Permission::where('name', 'like', 'view_%')->get();

        $role->syncPermissions($permissions);
    }
}
