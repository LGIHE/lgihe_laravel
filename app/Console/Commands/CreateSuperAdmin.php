<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class CreateSuperAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:super-admin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a super admin user with full permissions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Creating Super Admin User');
        $this->info('========================');

        // Get user details
        $name = $this->ask('Enter full name');
        $email = $this->ask('Enter email address');
        $password = $this->secret('Enter password');
        $passwordConfirmation = $this->secret('Confirm password');

        // Validate input
        $validator = Validator::make([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $passwordConfirmation,
        ], [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }
            return 1;
        }

        // Check if Super Admin role exists
        $superAdminRole = Role::where('name', 'Super Admin')->first();
        
        if (!$superAdminRole) {
            $this->error('Super Admin role not found. Please run: php artisan db:seed --class=PermissionSeeder');
            return 1;
        }

        // Create user
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'email_verified_at' => now(),
        ]);

        // Assign Super Admin role
        $user->assignRole('Super Admin');

        $this->info('');
        $this->info('✓ Super Admin user created successfully!');
        $this->info('');
        $this->table(
            ['Field', 'Value'],
            [
                ['Name', $user->name],
                ['Email', $user->email],
                ['Role', 'Super Admin'],
            ]
        );

        return 0;
    }
}
