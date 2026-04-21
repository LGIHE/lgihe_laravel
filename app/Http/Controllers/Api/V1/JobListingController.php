<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\JobListing;
use Illuminate\Http\Request;

class JobListingController extends Controller
{
    public function index(Request $request)
    {
        $jobs = JobListing::active()
            ->with('creator:id,name')
            ->latest('published_at')
            ->paginate($request->get('per_page', 12));

        return response()->json($jobs);
    }

    public function show($id)
    {
        $job = JobListing::active()
            ->where('id', $id)
            ->with('creator:id,name')
            ->firstOrFail();

        return response()->json($job);
    }
}
