<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class FixTenderPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tender:fix-permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix tender permissions for all roles';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Fixing tender permissions...');
        $this->newLine();

        // Create tender permissions if they don't exist
        $this->info('1. Creating tender permissions...');
        $permissions = [
            'view_tenders',
            'create_tenders',
            'update_tenders',
            'delete_tenders',
        ];

        foreach ($permissions as $permissionName) {
            $permission = Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]);
            $this->line("   ✓ {$permissionName}");
        }
        $this->newLine();

        // Assign to Super Admin
        $this->info('2. Assigning permissions to Super Admin...');
        $superAdmin = Role::where('name', 'Super Admin')->first();
        if ($superAdmin) {
            $tenderPermissions = Permission::whereIn('name', $permissions)->get();
            $superAdmin->givePermissionTo($tenderPermissions);
            $this->info('   ✓ Super Admin now has all tender permissions');
        } else {
            $this->error('   ❌ Super Admin role not found!');
        }
        $this->newLine();

        // Assign to Admin
        $this->info('3. Assigning permissions to Admin...');
        $admin = Role::where('name', 'Admin')->first();
        if ($admin) {
            $tenderPermissions = Permission::whereIn('name', $permissions)->get();
            $admin->givePermissionTo($tenderPermissions);
            $this->info('   ✓ Admin now has all tender permissions');
        } else {
            $this->warn('   ⚠ Admin role not found (skipping)');
        }
        $this->newLine();

        // Assign to Editor
        $this->info('4. Assigning permissions to Editor...');
        $editor = Role::where('name', 'Editor')->first();
        if ($editor) {
            $tenderPermissions = Permission::whereIn('name', $permissions)->get();
            $editor->givePermissionTo($tenderPermissions);
            $this->info('   ✓ Editor now has all tender permissions');
        } else {
            $this->warn('   ⚠ Editor role not found (skipping)');
        }
        $this->newLine();

        // Assign view permission to Viewer
        $this->info('5. Assigning view permission to Viewer...');
        $viewer = Role::where('name', 'Viewer')->first();
        if ($viewer) {
            $viewPermission = Permission::where('name', 'view_tenders')->first();
            $viewer->givePermissionTo($viewPermission);
            $this->info('   ✓ Viewer now has view_tenders permission');
        } else {
            $this->warn('   ⚠ Viewer role not found (skipping)');
        }
        $this->newLine();

        // Clear permission cache
        $this->info('6. Clearing permission cache...');
        $this->call('permission:cache-reset');
        $this->info('   ✓ Permission cache cleared');
        $this->newLine();

        // Clear other caches
        $this->info('7. Clearing application caches...');
        $this->call('cache:clear');
        $this->call('config:clear');
        $this->call('route:clear');
        $this->call('view:clear');
        $this->info('   ✓ All caches cleared');
        $this->newLine();

        $this->info('=== SUCCESS ===');
        $this->info('Tender permissions have been fixed!');
        $this->newLine();
        $this->warn('IMPORTANT: Please log out and log back in to Filament for changes to take effect.');

        return Command::SUCCESS;
    }
}
