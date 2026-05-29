<?php

namespace App\Http\Controllers\Api\V1;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Models\Story;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StoryController extends Controller
{
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'store_id' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $stories = Story::with('store')
            ->active()
            ->when($request->store_id, function ($query) use ($request) {
                $query->where('store_id', $request->store_id);
            })
            ->latest()
            ->get();

        return response()->json($stories, 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'nullable|string|max:191',
            'image' => 'nullable|string',
            'video' => 'nullable|string',
            'type' => 'required|in:image,video',
            'duration' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $story = Story::create([
            'store_id' => $request->user()->id,
            'title' => $request->title,
            'image' => $request->image,
            'video' => $request->video,
            'type' => $request->type,
            'duration' => $request->duration ?? 5,
            'expires_at' => now()->addHours(24),
            'status' => 1,
        ]);

        return response()->json([
            'story' => $story,
            'message' => translate('messages.Story created successfully'),
        ], 200);
    }

    public function destroy($id)
    {
        $story = Story::where('id', $id)
            ->where('store_id', auth('api')->user()->id)
            ->firstOrFail();

        $story->delete();

        return response()->json([
            'message' => translate('messages.Story deleted successfully'),
        ], 200);
    }

    public function view(Request $request, $id)
    {
        $story = Story::with('store')->findOrFail($id);
        return response()->json($story, 200);
    }
}
