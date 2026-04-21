<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AnalyticsEvent;
use App\Models\AnalyticsError;
use App\Models\PageLoad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AnalyticsController extends Controller
{
    public function storeEvent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'session_id' => 'required|string|max:255',
            'event_type' => 'required|string|max:255',
            'event_name' => 'nullable|string|max:255',
            'event_data' => 'nullable|array',
            'page_url' => 'required|string|max:500',
            'referrer' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        AnalyticsEvent::create([
            'session_id' => $request->session_id,
            'event_type' => $request->event_type,
            'event_name' => $request->event_name,
            'event_data' => $request->event_data,
            'page_url' => $request->page_url,
            'referrer' => $request->referrer,
            'user_agent' => $request->userAgent(),
            'ip_address' => $request->ip(),
        ]);

        return response()->json(['success' => true], 201);
    }

    public function storeError(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'session_id' => 'required|string|max:255',
            'error_type' => 'required|string|max:255',
            'error_message' => 'required|string',
            'stack_trace' => 'nullable|string',
            'page_url' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        AnalyticsError::create([
            'session_id' => $request->session_id,
            'error_type' => $request->error_type,
            'error_message' => $request->error_message,
            'stack_trace' => $request->stack_trace,
            'page_url' => $request->page_url,
            'user_agent' => $request->userAgent(),
            'ip_address' => $request->ip(),
        ]);

        return response()->json(['success' => true], 201);
    }

    public function storePageLoad(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'session_id' => 'required|string|max:255',
            'page_url' => 'required|string|max:500',
            'page_title' => 'nullable|string|max:255',
            'referrer' => 'nullable|string|max:500',
            'load_time' => 'nullable|integer',
            'device_type' => 'nullable|string|max:50',
            'browser' => 'nullable|string|max:100',
            'os' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        PageLoad::create([
            'session_id' => $request->session_id,
            'page_url' => $request->page_url,
            'page_title' => $request->page_title,
            'referrer' => $request->referrer,
            'load_time' => $request->load_time,
            'device_type' => $request->device_type,
            'browser' => $request->browser,
            'os' => $request->os,
            'user_agent' => $request->userAgent(),
            'ip_address' => $request->ip(),
        ]);

        return response()->json(['success' => true], 201);
    }
}
