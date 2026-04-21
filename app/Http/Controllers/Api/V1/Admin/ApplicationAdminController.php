<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\ApplicationStatusHistory;
use App\Models\ApplicationNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ApplicationAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = Application::with('reviewer:id,name');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('reference_no', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $applications = $query->latest('submitted_at')->paginate($request->get('per_page', 15));

        return response()->json($applications);
    }

    public function show($id)
    {
        $application = Application::with([
            'reviewer:id,name',
            'statusHistories.changer:id,name',
            'notes.creator:id,name'
        ])->findOrFail($id);

        return response()->json($application);
    }

    public function updateStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:submitted,under_review,pending_documents,shortlisted,accepted,rejected,withdrawn',
            'comment' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $application = Application::findOrFail($id);
        $oldStatus = $application->status;

        DB::beginTransaction();
        try {
            $application->update([
                'status' => $request->status,
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);

            ApplicationStatusHistory::create([
                'application_id' => $application->id,
                'from_status' => $oldStatus,
                'to_status' => $request->status,
                'comment' => $request->comment,
                'changed_by' => auth()->id(),
            ]);

            DB::commit();

            // TODO: Send notification to applicant
            // dispatch(new SendApplicationStatusUpdateEmail($application));

            return response()->json([
                'success' => true,
                'message' => 'Application status updated successfully',
                'data' => $application->fresh(),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update application status',
            ], 500);
        }
    }

    public function addNote(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'note' => 'required|string|max:5000',
            'is_internal' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $application = Application::findOrFail($id);

        $note = ApplicationNote::create([
            'application_id' => $application->id,
            'note' => $request->note,
            'is_internal' => $request->get('is_internal', true),
            'created_by' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Note added successfully',
            'data' => $note->load('creator:id,name'),
        ], 201);
    }
}
