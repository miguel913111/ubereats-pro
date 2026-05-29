<?php

namespace App\Http\Controllers\Admin;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Models\DeliveryBatch;
use App\Models\DeliveryTimeWindow;
use App\Models\BusinessSetting;
use App\Models\Zone;
use App\Services\DeliveryOptimizationService;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;

class DeliveryOptimizationController extends Controller
{
    /**
     * List all delivery batches
     */
    public function index(Request $request)
    {
        if (!Helpers::module_permission_check('delivery_optimization')) {
            Toastr::error(translate('messages.access_denied'));
            return back();
        }
        $batches = DeliveryBatch::with(['deliveryMan', 'zone', 'batchOrders.order'])
            ->latest()
            ->paginate(25);
        $zones = Zone::all();
        return view('admin-views.delivery-optimization.index', compact('batches', 'zones'));
    }

    /**
     * Auto-generate batches for a zone
     */
    public function autoBatch(Request $request)
    {
        if (!Helpers::module_permission_check('delivery_optimization')) {
            Toastr::error(translate('messages.access_denied'));
            return back();
        }
        $zoneId = $request->zone_id;
        $result = DeliveryOptimizationService::autoBatchZone($zoneId);

        Toastr::success($result['message']);
        return back();
    }

    /**
     * View batch details
     */
    public function show($id)
    {
        if (!Helpers::module_permission_check('delivery_optimization')) {
            Toastr::error(translate('messages.access_denied'));
            return back();
        }
        $batch = DeliveryBatch::with(['deliveryMan', 'zone', 'batchOrders.order', 'routeSegments'])
            ->findOrFail($id);
        return view('admin-views.delivery-optimization.show', compact('batch'));
    }

    /**
     * Global settings page
     */
    public function settings()
    {
        if (!Helpers::module_permission_check('delivery_optimization')) {
            Toastr::error(translate('messages.access_denied'));
            return back();
        }
        $settings = DeliveryOptimizationService::getSettings();
        return view('admin-views.delivery-optimization.settings', compact('settings'));
    }

    /**
     * Update global settings
     */
    public function updateSettings(Request $request)
    {
        if (!Helpers::module_permission_check('delivery_optimization')) {
            Toastr::error(translate('messages.access_denied'));
            return back();
        }
        $keys = [
            'batch_delivery_enabled',
            'batch_max_radius_km',
            'batch_max_orders',
            'batch_time_window_minutes',
            'batch_min_orders_to_group',
        ];

        foreach ($keys as $key) {
            BusinessSetting::updateOrCreate(
                ['key' => $key],
                ['value' => $request->input($key, '0')]
            );
        }

        Toastr::success(translate('messages.settings_updated'));
        return back();
    }

    /**
     * Time windows list
     */
    public function timeWindows(Request $request)
    {
        $timeWindows = DeliveryTimeWindow::with(['zone', 'store'])
            ->when($request->zone_id, function ($q) use ($request) {
                return $q->where('zone_id', $request->zone_id);
            })
            ->latest()
            ->paginate(25);
        $zones = Zone::all();
        return view('admin-views.delivery-optimization.time-windows', compact('timeWindows', 'zones'));
    }

    /**
     * Store time window
     */
    public function storeTimeWindow(Request $request)
    {
        if (!Helpers::module_permission_check('delivery_optimization')) {
            Toastr::error(translate('messages.access_denied'));
            return back();
        }
        DeliveryTimeWindow::create($request->all());
        Toastr::success(translate('messages.time_window_added'));
        return back();
    }

    /**
     * Delete time window
     */
    public function deleteTimeWindow($id)
    {
        if (!Helpers::module_permission_check('delivery_optimization')) {
            Toastr::error(translate('messages.access_denied'));
            return back();
        }
        DeliveryTimeWindow::findOrFail($id)->delete();
        Toastr::success(translate('messages.time_window_deleted'));
        return back();
    }
}
