<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Tender;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TenderController extends Controller
{
    public function index(Request $request)
    {
        $tenders = Tender::open()
            ->with('creator:id,name')
            ->latest('published_at')
            ->paginate($request->get('per_page', 12));

        // Add document info to each tender
        $tenders->getCollection()->transform(function ($tender) {
            $tender->has_rfp_document = !empty($tender->rfp_document_path);
            $tender->has_tor_document = !empty($tender->tor_document_path);
            $tender->rfp_download_url = $tender->has_rfp_document ? route('tender.download-rfp', $tender) : null;
            $tender->tor_download_url = $tender->has_tor_document ? route('tender.download-tor', $tender) : null;
            return $tender;
        });

        return response()->json($tenders);
    }

    public function show($id)
    {
        $tender = Tender::open()
            ->where('id', $id)
            ->with('creator:id,name')
            ->firstOrFail();

        // Add document info
        $tender->has_rfp_document = !empty($tender->rfp_document_path);
        $tender->has_tor_document = !empty($tender->tor_document_path);
        $tender->rfp_download_url = $tender->has_rfp_document ? route('tender.download-rfp', $tender) : null;
        $tender->tor_download_url = $tender->has_tor_document ? route('tender.download-tor', $tender) : null;
        $tender->formatted_rfp_file_size = $tender->formatted_rfp_file_size;
        $tender->formatted_tor_file_size = $tender->formatted_tor_file_size;

        return response()->json($tender);
    }

    public function downloadRfpDocument(Tender $tender)
    {
        if (empty($tender->rfp_document_path)) {
            abort(404, 'RFP document not found');
        }

        if (!Storage::disk('public')->exists($tender->rfp_document_path)) {
            abort(404, 'RFP document file not found');
        }

        $filePath = Storage::disk('public')->path($tender->rfp_document_path);
        $fileName = $tender->rfp_document_name ?: basename($tender->rfp_document_path);

        return response()->download($filePath, $fileName);
    }

    public function downloadTorDocument(Tender $tender)
    {
        if (empty($tender->tor_document_path)) {
            abort(404, 'ToR document not found');
        }

        if (!Storage::disk('public')->exists($tender->tor_document_path)) {
            abort(404, 'ToR document file not found');
        }

        $filePath = Storage::disk('public')->path($tender->tor_document_path);
        $fileName = $tender->tor_document_name ?: basename($tender->tor_document_path);

        return response()->download($filePath, $fileName);
    }
}
