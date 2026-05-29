<?php

namespace App\Http\Controllers\Admin;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Models\StoreDeliveryZone;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;

class StoreDeliveryZoneController extends Controller
{
    public function index(Request $request)
    {
        if (!Helpers::module_permission_check('store_delivery_zone')) {
            Toastr::error(translate('messages.access_denied'));
            return back();
        }
        $zones = StoreDeliveryZone::with('store')
            ->when($request->store_id, function ($query) use ($request) {
                $query->where('store_id', $request->store_id);
            })
            ->latest()
            ->paginate(25);

        return view('admin-views.store-delivery-zone.index', compact('zones'));
    }

    public function status(Request $request)
    {
        if (!Helpers::module_permission_check('store_delivery_zone')) {
            Toastr::error(translate('messages.access_denied'));
            return back();
        }
        $zone = StoreDeliveryZone::findOrFail($request->id);
        $zone->status = $request->status;
        $zone->save();

        Toastr::success(translate('messages.Status updated successfully'));
        return redirect()->back();
    }

    public function destroy($id)
    {
        if (!Helpers::module_permission_check('store_delivery_zone')) {
            Toastr::error(translate('messages.access_denied'));
            return back();
        }
        $zone = StoreDeliveryZone::findOrFail($id);
        $zone->delete();

        Toastr::success(translate('messages.Zone deleted successfully'));
        return redirect()->back();
    }
}
