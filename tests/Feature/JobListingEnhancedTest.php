<?php

namespace Tests\Feature;

use App\Models\JobListing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class JobListingEnhancedTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        Storage::fake('public');
        
        $this->user = User::factory()->create();
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
            'status' => 'active',
            'published_at' => now(),
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ];

        $job = JobListing::create($jobData);

        $this->assertDatabaseHas('job_listings', [
            'title' => 'Senior Lecturer - Computer Science',
            'department' => 'Computer Science Department',
            'reports_to' => 'Head of Department',
            'supervises_who' => 'Teaching Assistants, Research Associates',
        ]);

        $this->assertEquals('Computer Science Department', $job->department);
        $this->assertEquals('Head of Department', $job->reports_to);
        $this->assertEquals('Teaching Assistants, Research Associates', $job->supervises_who);
    }

    public function test_job_listing_can_have_document_attached()
    {
        $file = UploadedFile::fake()->create('job-description.pdf', 1024, 'application/pdf');
        
        $job = JobListing::factory()->create([
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ]);

        // Simulate file upload
        $filePath = $file->store('job-documents', 'public');
        
        $job->update([
            'document_path' => $filePath,
            'document_name' => $file->getClientOriginalName(),
            'document_type' => $file->getMimeType(),
            'document_size' => 1024, // Set exact size
        ]);

        $this->assertTrue($job->hasDocument());
        $this->assertNotNull($job->document_url);
        $this->assertEquals('1 KB', $job->formatted_file_size);
        
        Storage::disk('public')->assertExists($filePath);
    }

    public function test_api_returns_job_with_document_info()
    {
        $file = UploadedFile::fake()->create('job-description.pdf', 2048, 'application/pdf');
        $filePath = $file->store('job-documents', 'public');

        $job = JobListing::factory()->create([
            'status' => 'active',
            'published_at' => now(),
            'department' => 'Engineering Department',
            'reports_to' => 'Dean of Engineering',
            'supervises_who' => 'Lab Assistants',
            'document_path' => $filePath,
            'document_name' => $file->getClientOriginalName(),
            'document_type' => $file->getMimeType(),
            'document_size' => $file->getSize(),
            'created_by' => $this->user->id,
        ]);

        $response = $this->getJson("/api/v1/jobs/{$job->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'title',
                'department',
                'reports_to',
                'supervises_who',
                'has_document',
                'document_download_url',
                'formatted_file_size',
            ])
            ->assertJson([
                'department' => 'Engineering Department',
                'reports_to' => 'Dean of Engineering',
                'supervises_who' => 'Lab Assistants',
                'has_document' => true,
            ]);

        $this->assertNotNull($response->json('document_download_url'));
    }

    public function test_document_can_be_downloaded()
    {
        $file = UploadedFile::fake()->create('job-description.pdf', 1024, 'application/pdf');
        $filePath = $file->store('job-documents', 'public');

        // Create actual file content for proper mime type detection
        Storage::disk('public')->put($filePath, '%PDF-1.4 fake pdf content');

        $job = JobListing::factory()->create([
            'status' => 'active',
            'published_at' => now(),
            'document_path' => $filePath,
            'document_name' => 'Job Description.pdf',
            'document_type' => 'application/pdf',
            'document_size' => 1024,
            'created_by' => $this->user->id,
        ]);

        $response = $this->get("/api/v1/jobs/{$job->id}/download-document");

        $response->assertStatus(200);
        $this->assertStringContainsString('attachment', $response->headers->get('content-disposition'));
    }

    public function test_download_returns_404_for_missing_document()
    {
        $job = JobListing::factory()->create([
            'status' => 'active',
            'published_at' => now(),
            'document_path' => null,
            'created_by' => $this->user->id,
        ]);

        $response = $this->get("/api/v1/jobs/{$job->id}/download-document");

        $response->assertStatus(404);
    }

    public function test_job_listing_index_includes_document_info()
    {
        $file = UploadedFile::fake()->create('job-description.pdf', 1024, 'application/pdf');
        $filePath = $file->store('job-documents', 'public');

        $jobWithDocument = JobListing::factory()->create([
            'status' => 'active',
            'published_at' => now()->subMinute(), // Earlier published date
            'document_path' => $filePath,
            'document_name' => 'Job Description.pdf',
            'created_by' => $this->user->id,
        ]);

        $jobWithoutDocument = JobListing::factory()->create([
            'status' => 'active',
            'published_at' => now(), // Later published date
            'document_path' => null,
            'created_by' => $this->user->id,
        ]);

        $response = $this->getJson('/api/v1/jobs');

        $response->assertStatus(200);
        
        $jobs = $response->json('data');
        $this->assertCount(2, $jobs);
        
        // Find jobs by ID since order might vary
        $jobWithDocData = collect($jobs)->firstWhere('id', $jobWithDocument->id);
        $jobWithoutDocData = collect($jobs)->firstWhere('id', $jobWithoutDocument->id);
        
        $this->assertTrue($jobWithDocData['has_document']);
        $this->assertNotNull($jobWithDocData['document_download_url']);
        
        $this->assertFalse($jobWithoutDocData['has_document']);
        $this->assertNull($jobWithoutDocData['document_download_url']);
    }
}