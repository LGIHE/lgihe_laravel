<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Mail\AbuseReportSubmitted;
use App\Models\AbuseReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class AbuseReportController extends Controller
{
    /**
     * Submit a new abuse report
     */
    public function store(Request $request)
    {
        // Determine if this is an anonymous report
        $isAnonymous = $request->boolean('anonymousReport', false);

        // Build validation rules
        $rules = [
            // Required fields
            'incidentType' => 'required|string|in:physical-abuse,sexual-harassment,sexual-assault,verbal-abuse,bullying,discrimination,stalking,emotional-abuse,financial-exploitation,neglect,other',
            'incidentDate' => 'required|date|before_or_equal:today',
            'incidentLocation' => 'required|string|max:1000',
            'personsInvolved' => 'required|string|max:5000',
            'detailedDescription' => 'required|string|max:10000',
            
            // Optional fields
            'witnessesPresent' => 'nullable|string|max:5000',
            'previouslyReported' => 'nullable|string|max:5000',
            'evidenceAvailable' => 'nullable|string|max:5000',
            'anonymousReport' => 'boolean',
        ];

        // Add reporter information validation only if not anonymous
        if (!$isAnonymous) {
            $rules = array_merge($rules, [
                'reporterName' => 'nullable|string|max:255',
                'reporterEmail' => 'nullable|email|max:255',
                'reporterPhone' => 'nullable|string|max:50',
                'reporterRelationship' => 'nullable|string|in:victim,witness,third-party,concerned-party,other',
                'preferredContact' => 'nullable|string|in:email,phone,no-contact',
            ]);
        }

        // Validate the request
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed. Please check your input.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Generate unique report ID
            $reportId = AbuseReport::generateReportId();

            // Create the abuse report
            $report = AbuseReport::create([
                'report_id' => $reportId,
                'reporter_name' => $isAnonymous ? null : $request->input('reporterName'),
                'reporter_email' => $isAnonymous ? null : $request->input('reporterEmail'),
                'reporter_phone' => $isAnonymous ? null : $request->input('reporterPhone'),
                'reporter_relationship' => $isAnonymous ? null : $request->input('reporterRelationship'),
                'preferred_contact' => $isAnonymous ? null : $request->input('preferredContact'),
                'anonymous_report' => $isAnonymous,
                'incident_type' => $request->input('incidentType'),
                'incident_date' => $request->input('incidentDate'),
                'incident_location' => $request->input('incidentLocation'),
                'persons_involved' => $request->input('personsInvolved'),
                'detailed_description' => $request->input('detailedDescription'),
                'witnesses_present' => $request->input('witnessesPresent'),
                'previously_reported' => $request->input('previouslyReported'),
                'evidence_available' => $request->input('evidenceAvailable'),
                'status' => 'pending',
            ]);

            // Send email notification to safeguarding team
            Mail::to('safeguarding@lgihe.ac.ug')->send(new AbuseReportSubmitted($report));

            DB::commit();

            // Log the submission (without sensitive details)
            Log::info('Abuse report submitted', [
                'report_id' => $reportId,
                'incident_type' => $request->input('incidentType'),
                'is_anonymous' => $isAnonymous,
                'timestamp' => now()->toIso8601String(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Report submitted successfully. The safeguarding team has been notified and will review your report.',
                'reportId' => $reportId,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            // Log the error (without sensitive details)
            Log::error('Failed to submit abuse report', [
                'error' => $e->getMessage(),
                'timestamp' => now()->toIso8601String(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to submit report. Please try again or contact support if the problem persists.',
            ], 500);
        }
    }
}
