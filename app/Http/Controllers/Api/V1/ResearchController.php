<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Research;
use Illuminate\Http\Request;

class ResearchController extends Controller
{
    public function index(Request $request)
    {
        $research = Research::published()
            ->with('creator:id,name')
            ->latest('published_at')
            ->paginate($request->get('per_page', 12));

        return response()->json($research);
    }

    public function show($id)
    {
        $research = Research::published()
            ->where('id', $id)
            ->with('creator:id,name')
            ->firstOrFail();

        return response()->json($research);
    }
}
