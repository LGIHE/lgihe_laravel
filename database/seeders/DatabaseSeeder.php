<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\News;
use App\Models\JobListing;
use App\Models\Event;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create permissions
        $permissions = [
            'manage-content',
            'manage-applications',
            'manage-inquiries',
            'manage-users',
            'view-analytics',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles
        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin']);
        $contentEditor = Role::firstOrCreate(['name' => 'Content Editor']);
        $admissionsOfficer = Role::firstOrCreate(['name' => 'Admissions Officer']);
        $communicationsOfficer = Role::firstOrCreate(['name' => 'Communications Officer']);

        // Assign permissions to roles
        $superAdmin->givePermissionTo(Permission::all());
        $contentEditor->givePermissionTo(['manage-content']);
        $admissionsOfficer->givePermissionTo(['manage-applications', 'view-analytics']);
        $communicationsOfficer->givePermissionTo(['manage-inquiries']);

        // Create admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@lgihe.org'],
            [
                'name' => 'System Administrator',
                'password' => Hash::make('password'),
            ]
        );
        $admin->assignRole('Super Admin');

        // Create content editor
        $editor = User::firstOrCreate(
            ['email' => 'editor@lgihe.org'],
            [
                'name' => 'Content Editor',
                'password' => Hash::make('password'),
            ]
        );
        $editor->assignRole('Content Editor');

        // Create sample news
        News::firstOrCreate(
            ['slug' => 'welcome-to-lgihe'],
            [
                'title' => 'Welcome to LGIHE',
                'excerpt' => 'Lesotho Government Institute of Higher Education welcomes you.',
                'content' => '<p>Welcome to the Lesotho Government Institute of Higher Education. We are committed to providing quality education and fostering academic excellence.</p>',
                'status' => 'published',
                'published_at' => now(),
                'created_by' => $admin->id,
            ]
        );

        News::firstOrCreate(
            ['slug' => 'new-academic-year-2026'],
            [
                'title' => 'New Academic Year 2026 Begins',
                'excerpt' => 'The 2026 academic year kicks off with exciting new programmes.',
                'content' => '<p>We are thrilled to announce the start of the 2026 academic year. This year brings new programmes, enhanced facilities, and opportunities for our students.</p>',
                'status' => 'published',
                'published_at' => now()->subDays(5),
                'created_by' => $admin->id,
            ]
        );

        // Create sample job listing
        JobListing::firstOrCreate(
            ['slug' => 'lecturer-computer-science'],
            [
                'title' => 'Lecturer - Computer Science',
                'description' => 'We are seeking a qualified lecturer in Computer Science.',
                'requirements' => '<ul><li>Master\'s degree in Computer Science</li><li>3+ years teaching experience</li></ul>',
                'responsibilities' => '<ul><li>Teach undergraduate courses</li><li>Conduct research</li></ul>',
                'location' => 'Maseru, Lesotho',
                'employment_type' => 'full-time',
                'application_deadline' => now()->addMonths(2),
                'status' => 'active',
                'published_at' => now(),
                'created_by' => $admin->id,
            ]
        );

        // Create sample event
        Event::firstOrCreate(
            ['slug' => 'open-day-2026'],
            [
                'title' => 'LGIHE Open Day 2026',
                'description' => 'Join us for our annual open day to explore our campus and programmes.',
                'content' => '<p>Experience LGIHE firsthand! Tour our facilities, meet faculty, and learn about our programmes.</p>',
                'location' => 'LGIHE Campus, Maseru',
                'venue' => 'Main Auditorium',
                'start_date' => now()->addMonths(1),
                'end_date' => now()->addMonths(1)->addHours(6),
                'status' => 'published',
                'published_at' => now(),
                'created_by' => $admin->id,
            ]
        );

        $this->command->info('Database seeded successfully!');
        $this->command->info('Admin credentials: admin@lgihe.org / password');
        $this->command->info('Editor credentials: editor@lgihe.org / password');
    }
}
