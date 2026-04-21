<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactInquiry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ContactInquiryAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = ContactInquiry::with('assignedUser:id,name');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $inquiries = $query->latest()->paginate($request->get('per_page', 15));

        return response()->json($inquiries);
    }

    public function show($id)
    {
        $inquiry = ContactInquiry::with('assignedUser:id,name')->findOrFail($id);
        return response()->json($inquiry);
    }

    public function updateStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:new,in_progress,resolved,spam',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $inquiry = ContactInquiry::findOrFail($id);

        $data = ['status' => $request->status];

        if ($request->has('assigned_to')) {
            $data['assigned_to'] = $request->assigned_to;
        }

        if ($request->status === 'resolved') {
            $data['resolved_at'] = now();
        }

        $inquiry->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Inquiry status updated successfully',
            'data' => $inquiry->fresh(),
        ]);
    }

    public function destroy($id)
    {
        $inquiry = ContactInquiry::findOrFail($id);
        $inquiry->delete();

        return response()->json([
            'success' => true,
            'message' => 'Inquiry deleted successfully',
        ]);
    }
}
