<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DiagnoseTenderPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tender:diagnose {user_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Diagnose tender permissions issues';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== Tender Permissions Diagnostic ===');
        $this->newLine();

        // Check if tender permissions exist
        $this->info('1. Checking if tender permissions exist...');
        $tenderPermissions = Permission::where('name', 'like', '%tender%')->get();
        
        if ($tenderPermissions->isEmpty()) {
            $this->error('   ❌ No tender permissions found!');
            $this->warn('   Run: php artisan db:seed --class=PermissionSeeder');
        } else {
            $this->info('   ✓ Found ' . $tenderPermissions->count() . ' tender permissions:');
            foreach ($tenderPermissions as $permission) {
                $this->line('     - ' . $permission->name);
            }
        }
        $this->newLine();

        // Check Super Admin role
        $this->info('2. Checking Super Admin role...');
        $superAdmin = Role::where('name', 'Super Admin')->first();
        
        if (!$superAdmin) {
            $this->error('   ❌ Super Admin role not found!');
            $this->warn('   Run: php artisan db:seed --class=PermissionSeeder');
        } else {
            $this->info('   ✓ Super Admin role exists');
            
            $superAdminTenderPerms = $superAdmin->permissions()->where('name', 'like', '%tender%')->get();
            if ($superAdminTenderPerms->isEmpty()) {
                $this->error('   ❌ Super Admin has NO tender permissions!');
                $this->warn('   Run: php artisan db:seed --class=PermissionSeeder');
            } else {
                $this->info('   ✓ Super Admin has ' . $superAdminTenderPerms->count() . ' tender permissions');
            }
        }
        $this->newLine();

        // Check specific user
        $userId = $this->argument('user_id') ?: 1;
        $this->info("3. Checking user (ID: {$userId})...");
        $user = User::find($userId);
        
        if (!$user) {
            $this->error("   ❌ User with ID {$userId} not found!");
        } else {
            $this->info("   ✓ User found: {$user->name} ({$user->email})");
            
            $userRoles = $user->roles->pluck('name')->toArray();
            if (empty($userRoles)) {
                $this->error('   ❌ User has NO roles assigned!');
            } else {
                $this->info('   ✓ User roles: ' . implode(', ', $userRoles));
            }
            
            if ($user->hasRole('Super Admin')) {
                $this->info('   ✓ User is Super Admin');
            } else {
                $this->warn('   ⚠ User is NOT Super Admin');
            }
            
            if ($user->can('view_tenders')) {
                $this->info('   ✓ User CAN view tenders');
            } else {
                $this->error('   ❌ User CANNOT view tenders');
            }
        }
        $this->newLine();

        // Check TenderResource
        $this->info('4. Checking TenderResource file...');
        $resourcePath = app_path('Filament/Resources/TenderResource.php');
        if (file_exists($resourcePath)) {
            $this->info('   ✓ TenderResource.php exists');
        } else {
            $this->error('   ❌ TenderResource.php NOT found!');
        }
        $this->newLine();

        // Provide recommendations
        $this->info('=== Recommendations ===');
        
        if ($tenderPermissions->isEmpty()) {
            $this->warn('1. Run: php artisan db:seed --class=PermissionSeeder');
        }
        
        $this->warn('2. Clear permission cache: php artisan permission:cache-reset');
        $this->warn('3. Clear all caches: php artisan optimize:clear');
        $this->warn('4. Log out and log back in to Filament');
        
        $this->newLine();
        $this->info('=== Quick Fix ===');
        $this->line('Run this command to fix everything:');
        $this->comment('php artisan db:seed --class=PermissionSeeder && php artisan permission:cache-reset && php artisan optimize:clear');

        return Command::SUCCESS;
    }
}
