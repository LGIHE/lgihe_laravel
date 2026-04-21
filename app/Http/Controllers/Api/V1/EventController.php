<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $events = Event::upcoming()
            ->with('creator:id,name')
            ->paginate($request->get('per_page', 12));

        return response()->json($events);
    }

    public function show($id)
    {
        $event = Event::published()
            ->where('id', $id)
            ->with('creator:id,name')
            ->firstOrFail();

        return response()->json($event);
    }
}
