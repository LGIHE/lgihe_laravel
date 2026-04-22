<?php

namespace Tests\Feature;

use App\Models\JobListing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class JobListingCreateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create permissions for testing
        Permission::create(['name' => 'view_users']);
        Permission::create(['name' => 'create_job_listings']);
        
        $this->user = User::factory()->create();
        $this->user->givePermissionTo(['view_users', 'create_job_listings']);
    }

    public function test_job_listing_can_be_created_with_new_fields()
    {
        $jobData = [
            'title' => 'Senior Lecturer - Computer Science',
            'slug' => 'senior-lecturer-computer-science',
            'description' => 'We are looking for a qualified lecturer...',
            'requirements' => 'PhD in Computer Science',
            'responsibilities' => 'Teaching and research duties',
            'location' => 'Kampala, Uganda',
            'department' => 'Computer Science Department',
            'reports_to' => 'Head of Department',
            'supervises_who' => 'Teaching Assistants, Research Associates',
            'employment_type' => 'full-time',
            'salary_range' => 'UGX 3,000,000 - 5,000,000',
            'application_deadline' => now()->addDays(30)->format('Y-m-d'),
            'status' => 'draft',
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ];

        $job = JobListing::create($jobData);

        $this->assertDatabaseHas('job_listings', [
            'title' => 'Senior Lecturer - Computer Science',
            'department' => 'Computer Science Department',
            'reports_to' => 'Head of Department',
            'supervises_who' => 'Teaching Assistants, Research Associates',
            'status' => 'draft',
        ]);

        $this->assertEquals('Computer Science Department', $job->department);
        $this->assertEquals('Head of Department', $job->reports_to);
        $this->assertEquals('Teaching Assistants, Research Associates', $job->supervises_who);
    }

    public function test_job_listing_published_at_is_set_when_active()
    {
        $jobData = [
            'title' => 'Test Job',
            'slug' => 'test-job',
            'description' => 'Test description',
            'status' => 'active',
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ];

        $job = JobListing::create($jobData);

        // When status is active, published_at should be set
        $this->assertNotNull($job->published_at);
    }
}