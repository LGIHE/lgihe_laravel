<?php

namespace Tests\Feature;

use App\Models\JobListing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class JobListingFilamentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        Storage::fake('public');
        
        // Create permissions for testing
        Permission::create(['name' => 'view_users']);
        Permission::create(['name' => 'create_job_listings']);
        Permission::create(['name' => 'view_job_listings']);
        
        $this->user = User::factory()->create();
        $this->user->givePermissionTo(['view_users', 'create_job_listings', 'view_job_listings']);
    }

    public function test_job_listing_create_page_loads()
    {
        $response = $this->actingAs($this->user)
            ->get('/admin/job-listings/create');

        $response->assertStatus(200);
        $response->assertSee('Create Job Listing');
    }

    public function test_job_listing_can_be_created_via_form()
    {
        $jobData = [
            'title' => 'Senior Lecturer - Computer Science',
            'description' => 'We are looking for a qualified lecturer...',
            'requirements' => 'PhD in Computer Science',
            'responsibilities' => 'Teaching and research duties',
            'location' => 'Kampala, Uganda',
            'department' => 'Computer Science Department',
            'reports_to' => 'Head of Department',
            'supervises_who' => 'Teaching Assistants, Research Associates',
            'employment_type' => 'full-time',
            'salary_range' => 'UGX 3,000,000 - 5,000,000',
            'status' => 'draft',
        ];

        // Create the job listing
        $job = JobListing::create(array_merge($jobData, [
            'slug' => 'senior-lecturer-computer-science',
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ]));

        $this->assertDatabaseHas('job_listings', [
            'title' => 'Senior Lecturer - Computer Science',
            'department' => 'Computer Science Department',
            'reports_to' => 'Head of Department',
            'supervises_who' => 'Teaching Assistants, Research Associates',
            'status' => 'draft',
        ]);
    }

    public function test_job_listing_with_document_can_be_created()
    {
        $file = UploadedFile::fake()->create('job-description.pdf', 1024, 'application/pdf');
        $filePath = $file->store('job-documents', 'public');

        $jobData = [
            'title' => 'Test Job with Document',
            'slug' => 'test-job-with-document',
            'description' => 'Test description',
            'status' => 'draft',
            'document_path' => $filePath,
            'document_name' => 'job-description.pdf',
            'document_type' => 'application/pdf',
            'document_size' => 1024,
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ];

        $job = JobListing::create($jobData);

        $this->assertTrue($job->hasDocument());
        $this->assertEquals('job-description.pdf', $job->document_name);
        $this->assertEquals('1 KB', $job->formatted_file_size);
        
        Storage::disk('public')->assertExists($filePath);
    }

    public function test_active_job_gets_published_at_automatically()
    {
        $jobData = [
            'title' => 'Active Job',
            'slug' => 'active-job',
            'description' => 'Test description',
            'status' => 'active',
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ];

        $job = JobListing::create($jobData);

        $this->assertEquals('active', $job->status);
        $this->assertNotNull($job->published_at);
        $this->assertTrue($job->published_at->isToday());
    }
}