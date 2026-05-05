<?php

namespace Tests\Feature;

use App\Models\AbuseReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AbuseReportTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test anonymous abuse report submission
     */
    public function test_can_submit_anonymous_abuse_report(): void
    {
        Mail::fake();

        $response = $this->postJson('/api/v1/report-abuse', [
            'anonymousReport' => true,
            'incidentType' => 'bullying',
            'incidentDate' => '2026-05-01',
            'incidentLocation' => 'Library',
            'personsInvolved' => 'Test Person',
            'detailedDescription' => 'This is a test report for anonymous submission.',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'reportId',
            ]);

        $this->assertDatabaseHas('abuse_reports', [
            'incident_type' => 'bullying',
            'anonymous_report' => true,
            'reporter_name' => null,
            'reporter_email' => null,
        ]);
    }

    /**
     * Test identified abuse report submission
     */
    public function test_can_submit_identified_abuse_report(): void
    {
        Mail::fake();

        $response = $this->postJson('/api/v1/report-abuse', [
            'reporterName' => 'John Doe',
            'reporterEmail' => 'john@example.com',
            'reporterPhone' => '+256700000000',
            'reporterRelationship' => 'witness',
            'preferredContact' => 'email',
            'anonymousReport' => false,
            'incidentType' => 'verbal-abuse',
            'incidentDate' => '2026-05-01',
            'incidentLocation' => 'Classroom 101',
            'personsInvolved' => 'Staff Member X',
            'detailedDescription' => 'Detailed test description of the incident.',
            'witnessesPresent' => 'Student B, Student C',
            'previouslyReported' => 'No',
            'evidenceAvailable' => 'Audio recording',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('abuse_reports', [
            'incident_type' => 'verbal-abuse',
            'anonymous_report' => false,
            'reporter_name' => 'John Doe',
            'reporter_email' => 'john@example.com',
        ]);
    }

    /**
     * Test validation for missing required fields
     */
    public function test_validation_fails_for_missing_required_fields(): void
    {
        $response = $this->postJson('/api/v1/report-abuse', [
            'incidentType' => 'bullying',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'errors',
            ]);
    }

    /**
     * Test validation for invalid incident type
     */
    public function test_validation_fails_for_invalid_incident_type(): void
    {
        $response = $this->postJson('/api/v1/report-abuse', [
            'incidentType' => 'invalid-type',
            'incidentDate' => '2026-05-01',
            'incidentLocation' => 'Library',
            'personsInvolved' => 'Test Person',
            'detailedDescription' => 'Test description',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['incidentType']);
    }

    /**
     * Test validation for future incident date
     */
    public function test_validation_fails_for_future_incident_date(): void
    {
        $response = $this->postJson('/api/v1/report-abuse', [
            'incidentType' => 'bullying',
            'incidentDate' => '2027-01-01',
            'incidentLocation' => 'Library',
            'personsInvolved' => 'Test Person',
            'detailedDescription' => 'Test description',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['incidentDate']);
    }

    /**
     * Test report ID generation
     */
    public function test_report_id_is_generated_correctly(): void
    {
        $reportId = AbuseReport::generateReportId();

        $this->assertStringStartsWith('ABR-', $reportId);
        $this->assertMatchesRegularExpression('/^ABR-\d+-[A-Z0-9]+$/', $reportId);
    }

    /**
     * Test incident type display attribute
     */
    public function test_incident_type_display_attribute(): void
    {
        $report = AbuseReport::factory()->create([
            'incident_type' => 'sexual-harassment',
        ]);

        $this->assertEquals('Sexual Harassment', $report->incident_type_display);
    }
}
