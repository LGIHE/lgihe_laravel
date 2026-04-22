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
            'name' => 'required|string|max:255',
            'properties' => 'nullable|array',
            'timestamp' => 'required|date',
            'sessionId' => 'nullable|string|max:255',
            'userAgent' => 'nullable|string',
            'referrer' => 'nullable|string',
            'screenResolution' => 'nullable|string|max:50',
            'country' => 'nullable|string|max:100',
            'countryCode' => 'nullable|string|max:10',
            'city' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        AnalyticsEvent::create([
            'name' => $request->name,
            'properties' => $request->properties,
            'session_id' => $request->sessionId,
            'user_agent' => $request->userAgent,
            'referrer' => $request->referrer,
            'screen_resolution' => $request->screenResolution,
            'country' => $request->country,
            'country_code' => $request->countryCode,
            'city' => $request->city,
            'timestamp' => $request->timestamp,
        ]);

        return response()->json(['success' => true], 201);
    }

    public function storePageLoad(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'url' => 'required|string|max:500',
            'loadTime' => 'required|integer',
            'timestamp' => 'required|date',
            'sessionId' => 'nullable|string|max:255',
            'userAgent' => 'nullable|string',
            'country' => 'nullable|string|max:100',
            'countryCode' => 'nullable|string|max:10',
            'city' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        PageLoad::create([
            'url' => $request->url,
            'load_time' => $request->loadTime,
            'session_id' => $request->sessionId,
            'user_agent' => $request->userAgent,
            'country' => $request->country,
            'country_code' => $request->countryCode,
            'city' => $request->city,
            'timestamp' => $request->timestamp,
        ]);

        return response()->json(['success' => true], 201);
    }

    public function storeError(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required|string',
            'stack' => 'nullable|string',
            'url' => 'nullable|string',
            'userAgent' => 'nullable|string',
            'timestamp' => 'required|date',
            'severity' => 'required|in:low,medium,high,critical',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        AnalyticsError::create([
            'message' => $request->message,
            'stack' => $request->stack,
            'url' => $request->url,
            'user_agent' => $request->userAgent,
            'severity' => $request->severity,
            'timestamp' => $request->timestamp,
        ]);

        return response()->json(['success' => true], 201);
    }
}
