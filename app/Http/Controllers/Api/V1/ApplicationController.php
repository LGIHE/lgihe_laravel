<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\ApplicationStatusHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ApplicationController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // Personal Information
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'date_of_birth' => 'required|date',
            'gender' => 'required|string|in:male,female,other',
            'nationality' => 'required|string|max:255',
            'id_number' => 'nullable|string|max:50',
            'passport_number' => 'nullable|string|max:50',
            
            // Contact Information
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:50',
            'alternative_phone' => 'nullable|string|max:50',
            'address' => 'required|string',
            'city' => 'required|string|max:255',
            'district' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            
            // Programme Information
            'programme_choice_1' => 'required|string|max:255',
            'programme_choice_2' => 'nullable|string|max:255',
            'intake_year' => 'required|string|max:10',
            'study_mode' => 'nullable|string|in:full-time,part-time',
            
            // Education & Employment
            'education_history' => 'nullable|array',
            'employment_history' => 'nullable|array',
            
            // Next of Kin
            'kin_name' => 'nullable|string|max:255',
            'kin_relationship' => 'nullable|string|max:255',
            'kin_phone' => 'nullable|string|max:50',
            'kin_email' => 'nullable|email|max:255',
            
            // Additional
            'additional_info' => 'nullable|string|max:5000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            $referenceNo = Application::generateReferenceNumber();

            $application = Application::create([
                'reference_no' => $referenceNo,
                'first_name' => $request->first_name,
                'middle_name' => $request->middle_name,
                'last_name' => $request->last_name,
                'date_of_birth' => $request->date_of_birth,
                'gender' => $request->gender,
                'nationality' => $request->nationality,
                'id_number' => $request->id_number,
                'passport_number' => $request->passport_number,
                'email' => $request->email,
                'phone' => $request->phone,
                'alternative_phone' => $request->alternative_phone,
                'address' => $request->address,
                'city' => $request->city,
                'district' => $request->district,
                'country' => $request->country,
                'programme_choice_1' => $request->programme_choice_1,
                'programme_choice_2' => $request->programme_choice_2,
                'intake_year' => $request->intake_year,
                'study_mode' => $request->study_mode,
                'education_history' => $request->education_history,
                'employment_history' => $request->employment_history,
                'kin_name' => $request->kin_name,
                'kin_relationship' => $request->kin_relationship,
                'kin_phone' => $request->kin_phone,
                'kin_email' => $request->kin_email,
                'additional_info' => $request->additional_info,
                'status' => 'submitted',
                'submitted_at' => now(),
            ]);

            // Create initial status history
            ApplicationStatusHistory::create([
                'application_id' => $application->id,
                'from_status' => null,
                'to_status' => 'submitted',
                'comment' => 'Application submitted',
                'changed_by' => 1, // System user - you should create a system user
            ]);

            DB::commit();

            // TODO: Queue email notifications
            // dispatch(new SendApplicationConfirmationEmail($application));
            // dispatch(new NotifyAdmissionsOfNewApplication($application));

            return response()->json([
                'success' => true,
                'message' => 'Your application has been submitted successfully.',
                'reference_no' => $referenceNo,
                'application_id' => $application->id,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing your application. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
