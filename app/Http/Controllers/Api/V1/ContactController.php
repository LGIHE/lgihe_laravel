<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ContactInquiry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $inquiry = ContactInquiry::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'subject' => $request->subject,
            'message' => $request->message,
            'source' => 'website',
            'status' => 'new',
        ]);

        // TODO: Queue email notifications
        // dispatch(new SendContactConfirmationEmail($inquiry));
        // dispatch(new NotifyAdminOfContactInquiry($inquiry));

        return response()->json([
            'success' => true,
            'message' => 'Your inquiry has been submitted successfully. We will get back to you soon.',
            'inquiry_id' => $inquiry->id,
        ], 201);
    }
}
