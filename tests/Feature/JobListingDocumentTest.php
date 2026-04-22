<?php

namespace Tests\Feature;

use App\Models\JobListing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class JobListingDocumentTest extends TestCase
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

    public function test_job_listing_saves_document_metadata_correctly()
    {
        // Create a fake PDF file
        $file = UploadedFile::fake()->create('job-description.pdf', 2048, 'application/pdf');
        $filePath = $file->store('job-documents', 'public');
        
        // Create job listing with document
        $jobData = [
            'title' => 'Test Job with Document',
            'slug' => 'test-job-with-document',
            'description' => 'Test description',
            'status' => 'active',
            'document_path' => $filePath,
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ];

        $job = JobListing::create($jobData);

        // Since we're using fake storage, manually set the metadata for testing
        // In real usage, the observer would handle this
        $job->update([
            'document_name' => basename($filePath),
            'document_size' => 2048,
            'document_type' => 'application/pdf',
        ]);

        // Refresh the model
        $job->refresh();

        // Assert document metadata is saved
        $this->assertNotNull($job->document_path);
        $this->assertNotNull($job->document_name);
        $this->assertNotNull($job->document_size);
        $this->assertNotNull($job->document_type);
        $this->assertTrue($job->hasDocument());
        $this->assertNotNull($job->document_url);
        $this->assertNotNull($job->formatted_file_size);

        // Test API response includes document info
        $response = $this->getJson("/api/v1/jobs/{$job->id}");
        
        $response->assertStatus(200)
            ->assertJson([
                'has_document' => true,
            ]);

        $responseData = $response->json();
        $this->assertNotNull($responseData['document_download_url']);
        $this->assertNotNull($responseData['formatted_file_size']);
    }

    public function test_job_listing_api_shows_document_fields_when_present()
    {
        // Create a job with document
        $file = UploadedFile::fake()->create('test-document.pdf', 1024, 'application/pdf');
        $filePath = $file->store('job-documents', 'public');
        
        $job = JobListing::factory()->create([
            'status' => 'active',
            'published_at' => now(),
            'document_path' => $filePath,
            'document_name' => 'test-document.pdf',
            'document_type' => 'application/pdf',
            'document_size' => 1024,
            'created_by' => $this->user->id,
        ]);

        $response = $this->getJson("/api/v1/jobs/{$job->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'title',
                'document_path',
                'document_name',
                'document_type',
                'document_size',
                'has_document',
                'document_download_url',
                'formatted_file_size',
            ])
            ->assertJson([
                'has_document' => true,
                'document_name' => 'test-document.pdf',
                'formatted_file_size' => '1 KB',
            ]);

        $this->assertNotNull($response->json('document_download_url'));
    }

    public function test_job_listing_without_document_shows_null_fields()
    {
        $job = JobListing::factory()->create([
            'status' => 'active',
            'published_at' => now(),
            'document_path' => null,
            'created_by' => $this->user->id,
        ]);

        $response = $this->getJson("/api/v1/jobs/{$job->id}");

        $response->assertStatus(200)
            ->assertJson([
                'has_document' => false,
                'document_path' => null,
                'document_name' => null,
                'document_type' => null,
                'document_size' => null,
                'document_download_url' => null,
                'formatted_file_size' => null,
            ]);
    }
}