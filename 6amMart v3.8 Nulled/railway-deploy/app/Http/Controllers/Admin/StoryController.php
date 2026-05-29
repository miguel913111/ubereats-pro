<?php

namespace App\Http\Controllers\Admin;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Models\Story;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;

class StoryController extends Controller
{
    public function index(Request $request)
    {
        if (!Helpers::module_permission_check('story')) {
            Toastr::error(translate('messages.access_denied'));
            return back();
        }
        $stories = Story::with('store')
            ->when($request->store_id, function ($query) use ($request) {
                $query->where('store_id', $request->store_id);
            })
            ->latest()
            ->paginate(25);

        return view('admin-views.story.index', compact('stories'));
    }

    public function status($id, $status)
    {
        if (!Helpers::module_permission_check('story')) {
            Toastr::error(translate('messages.access_denied'));
            return back();
        }
        $story = Story::findOrFail($id);
        $story->status = $status;
        $story->save();

        Toastr::success(translate('messages.Story status updated'));
        return back();
    }

    public function destroy($id)
    {
        if (!Helpers::module_permission_check('story')) {
            Toastr::error(translate('messages.access_denied'));
            return back();
        }
        $story = Story::findOrFail($id);
        $story->delete();

        Toastr::success(translate('messages.Story deleted successfully'));
        return back();
    }
}
