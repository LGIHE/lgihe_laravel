<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Tender;
use Illuminate\Http\Request;

class TenderController extends Controller
{
    public function index(Request $request)
    {
        $tenders = Tender::open()
            ->with('creator:id,name')
            ->latest('published_at')
            ->paginate($request->get('per_page', 12));

        return response()->json($tenders);
    }

    public function show($id)
    {
        $tender = Tender::open()
            ->where('id', $id)
            ->with('creator:id,name')
            ->firstOrFail();

        return response()->json($tender);
    }
}
