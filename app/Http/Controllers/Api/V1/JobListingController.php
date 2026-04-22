<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\JobListing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class JobListingController extends Controller
{
    public function index(Request $request)
    {
        $jobs = JobListing::active()
            ->with('creator:id,name')
            ->latest('published_at')
            ->paginate($request->get('per_page', 12));

        // Add document info to each job
        $jobs->getCollection()->transform(function ($job) {
            $job->has_document = !empty($job->document_path);
            $job->document_download_url = $job->has_document ? route('job-listing.download-document', $job) : null;
            return $job;
        });

        return response()->json($jobs);
    }

    public function show($id)
    {
        $job = JobListing::active()
            ->where('id', $id)
            ->with('creator:id,name')
            ->firstOrFail();

        // Add document info
        $job->has_document = !empty($job->document_path);
        $job->document_download_url = $job->has_document ? route('job-listing.download-document', $job) : null;
        $job->formatted_file_size = $job->formatted_file_size;

        return response()->json($job);
    }

    public function downloadDocument(JobListing $jobListing)
    {
        if (empty($jobListing->document_path)) {
            abort(404, 'Document not found');
        }

        if (!Storage::disk('public')->exists($jobListing->document_path)) {
            abort(404, 'Document file not found');
        }

        $filePath = Storage::disk('public')->path($jobListing->document_path);
        $fileName = $jobListing->document_name ?: basename($jobListing->document_path);

        return response()->download($filePath, $fileName);
    }
}
