<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\News;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    public function index(Request $request)
    {
        $news = News::published()
            ->with('creator:id,name')
            ->latest('published_at')
            ->paginate($request->get('per_page', 12));

        return response()->json($news);
    }

    public function show($slug)
    {
        $news = News::published()
            ->where('slug', $slug)
            ->with('creator:id,name')
            ->firstOrFail();

        return response()->json($news);
    }
}
