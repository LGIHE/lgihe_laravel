<?php

namespace Tests\Feature;

use App\Models\Tender;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class TenderTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create permissions
        Permission::create(['name' => 'view_users']);
        Permission::create(['name' => 'create_tenders']);
        Permission::create(['name' => 'view_tenders']);
        Permission::create(['name' => 'update_tenders']);
        Permission::create(['name' => 'delete_tenders']);

        $this->user = User::factory()->create();
        $this->user->givePermissionTo(['view_users', 'create_tenders', 'view_tenders', 'update_tenders', 'delete_tenders']);
    }

    /** @test */
    public function it_can_list_open_tenders_via_api()
    {
        // Create open tenders
        Tender::factory()->count(3)->create([
            'status' => 'open',
            'published_at' => now()->subDay(),
            'closing_date' => now()->addDays(7),
        ]);

        // Create draft tender (should not appear)
        Tender::factory()->create([
            'status' => 'draft',
        ]);

        $response = $this->getJson('/api/v1/tenders');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function it_can_show_a_single_tender_via_api()
    {
        $tender = Tender::factory()->create([
            'status' => 'open',
            'published_at' => now()->subDay(),
            'closing_date' => now()->addDays(7),
        ]);

        $response = $this->getJson("/api/v1/tenders/{$tender->id}");

        $response->assertStatus(200)
            ->assertJson([
                'id' => $tender->id,
                'title' => $tender->title,
                'reference_number' => $tender->reference_number,
            ]);
    }

    /** @test */
    public function it_can_create_tender_with_rfp_document()
    {
        Storage::fake('public');

        $tender = Tender::create([
            'title' => 'Test Tender',
            'slug' => 'test-tender',
            'reference_number' => 'TENDER/2026/001',
            'description' => 'Test description',
            'category' => 'goods',
            'closing_date' => now()->addDays(30),
            'rfp_document_path' => 'tender-documents/rfp/test-rfp.pdf',
            'rfp_document_name' => 'test-rfp.pdf',
            'rfp_document_type' => 'application/pdf',
            'rfp_document_size' => 1024,
            'status' => 'open',
            'published_at' => now(),
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ]);

        $this->assertDatabaseHas('tenders', [
            'title' => 'Test Tender',
            'reference_number' => 'TENDER/2026/001',
        ]);

        $this->assertNotNull($tender->rfp_document_path);
        $this->assertEquals('test-rfp.pdf', $tender->rfp_document_name);
    }

    /** @test */
    public function it_can_create_tender_with_both_rfp_and_tor_documents()
    {
        Storage::fake('public');

        $tender = Tender::create([
            'title' => 'Test Tender with Documents',
            'slug' => 'test-tender-with-documents',
            'reference_number' => 'TENDER/2026/002',
            'description' => 'Test description',
            'category' => 'services',
            'closing_date' => now()->addDays(30),
            'rfp_document_path' => 'tender-documents/rfp/test-rfp.pdf',
            'rfp_document_name' => 'test-rfp.pdf',
            'rfp_document_type' => 'application/pdf',
            'rfp_document_size' => 1024,
            'tor_document_path' => 'tender-documents/tor/test-tor.pdf',
            'tor_document_name' => 'test-tor.pdf',
            'tor_document_type' => 'application/pdf',
            'tor_document_size' => 2048,
            'status' => 'open',
            'published_at' => now(),
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ]);

        $this->assertNotNull($tender->rfp_document_path);
        $this->assertNotNull($tender->tor_document_path);
        $this->assertEquals('test-rfp.pdf', $tender->rfp_document_name);
        $this->assertEquals('test-tor.pdf', $tender->tor_document_name);
    }

    /** @test */
    public function it_formats_file_sizes_correctly()
    {
        $tender = Tender::factory()->create([
            'rfp_document_size' => 1024, // 1 KB
            'tor_document_size' => 1048576, // 1 MB
        ]);

        $this->assertEquals('1 KB', $tender->formatted_rfp_file_size);
        $this->assertEquals('1 MB', $tender->formatted_tor_file_size);
    }

    /** @test */
    public function it_only_shows_open_tenders_with_valid_closing_dates()
    {
        // Create open tender with future closing date
        $openTender = Tender::factory()->create([
            'status' => 'open',
            'published_at' => now()->subDay(),
            'closing_date' => now()->addDays(7),
        ]);

        // Create open tender with past closing date (should not appear)
        $closedTender = Tender::factory()->create([
            'status' => 'open',
            'published_at' => now()->subDay(),
            'closing_date' => now()->subDay(),
        ]);

        $openTenders = Tender::open()->get();

        $this->assertTrue($openTenders->contains($openTender));
        $this->assertFalse($openTenders->contains($closedTender));
    }

    /** @test */
    public function it_sets_published_at_when_status_is_open()
    {
        $tender = Tender::create([
            'title' => 'Test Tender',
            'slug' => 'test-tender',
            'reference_number' => 'TENDER/2026/003',
            'description' => 'Test description',
            'closing_date' => now()->addDays(30),
            'status' => 'open',
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ]);

        $this->assertNotNull($tender->published_at);
    }

    /** @test */
    public function it_does_not_set_published_at_when_status_is_draft()
    {
        $tender = Tender::create([
            'title' => 'Test Tender',
            'slug' => 'test-tender-draft',
            'reference_number' => 'TENDER/2026/004',
            'description' => 'Test description',
            'closing_date' => now()->addDays(30),
            'status' => 'draft',
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ]);

        $this->assertNull($tender->published_at);
    }

    /** @test */
    public function it_can_download_rfp_document()
    {
        Storage::fake('public');

        $filePath = 'tender-documents/rfp/test-rfp.pdf';
        Storage::disk('public')->put($filePath, 'Test RFP content');

        $tender = Tender::factory()->create([
            'rfp_document_path' => $filePath,
            'rfp_document_name' => 'test-rfp.pdf',
            'status' => 'open',
            'published_at' => now(),
        ]);

        $response = $this->get(route('tender.download-rfp', $tender));

        $response->assertStatus(200);
        $response->assertDownload('test-rfp.pdf');
    }

    /** @test */
    public function it_can_download_tor_document()
    {
        Storage::fake('public');

        $filePath = 'tender-documents/tor/test-tor.pdf';
        Storage::disk('public')->put($filePath, 'Test ToR content');

        $tender = Tender::factory()->create([
            'tor_document_path' => $filePath,
            'tor_document_name' => 'test-tor.pdf',
            'status' => 'open',
            'published_at' => now(),
        ]);

        $response = $this->get(route('tender.download-tor', $tender));

        $response->assertStatus(200);
        $response->assertDownload('test-tor.pdf');
    }

    /** @test */
    public function it_returns_404_when_rfp_document_does_not_exist()
    {
        $tender = Tender::factory()->create([
            'rfp_document_path' => null,
        ]);

        $response = $this->get(route('tender.download-rfp', $tender));

        $response->assertStatus(404);
    }

    /** @test */
    public function it_returns_404_when_tor_document_does_not_exist()
    {
        $tender = Tender::factory()->create([
            'tor_document_path' => null,
        ]);

        $response = $this->get(route('tender.download-tor', $tender));

        $response->assertStatus(404);
    }

    /** @test */
    public function it_deletes_documents_when_tender_is_soft_deleted()
    {
        Storage::fake('public');

        $rfpPath = 'tender-documents/rfp/test-rfp.pdf';
        $torPath = 'tender-documents/tor/test-tor.pdf';
        
        Storage::disk('public')->put($rfpPath, 'Test RFP content');
        Storage::disk('public')->put($torPath, 'Test ToR content');

        $tender = Tender::factory()->create([
            'rfp_document_path' => $rfpPath,
            'rfp_document_name' => 'test-rfp.pdf',
            'tor_document_path' => $torPath,
            'tor_document_name' => 'test-tor.pdf',
        ]);

        // Verify files exist
        Storage::disk('public')->assertExists($rfpPath);
        Storage::disk('public')->assertExists($torPath);

        // Soft delete the tender
        $tender->delete();

        // Verify files are deleted
        Storage::disk('public')->assertMissing($rfpPath);
        Storage::disk('public')->assertMissing($torPath);
    }

    /** @test */
    public function it_deletes_documents_when_tender_is_force_deleted()
    {
        Storage::fake('public');

        $rfpPath = 'tender-documents/rfp/test-rfp-force.pdf';
        $torPath = 'tender-documents/tor/test-tor-force.pdf';
        
        Storage::disk('public')->put($rfpPath, 'Test RFP content');
        Storage::disk('public')->put($torPath, 'Test ToR content');

        $tender = Tender::factory()->create([
            'rfp_document_path' => $rfpPath,
            'rfp_document_name' => 'test-rfp-force.pdf',
            'tor_document_path' => $torPath,
            'tor_document_name' => 'test-tor-force.pdf',
        ]);

        // Verify files exist
        Storage::disk('public')->assertExists($rfpPath);
        Storage::disk('public')->assertExists($torPath);

        // Force delete the tender
        $tender->forceDelete();

        // Verify files are deleted
        Storage::disk('public')->assertMissing($rfpPath);
        Storage::disk('public')->assertMissing($torPath);
    }

    /** @test */
    public function it_handles_deletion_when_documents_do_not_exist()
    {
        Storage::fake('public');

        $tender = Tender::factory()->create([
            'rfp_document_path' => 'tender-documents/rfp/non-existent.pdf',
            'tor_document_path' => 'tender-documents/tor/non-existent.pdf',
        ]);

        // Should not throw an error even if files don't exist
        $tender->delete();

        $this->assertSoftDeleted('tenders', ['id' => $tender->id]);
    }
}
