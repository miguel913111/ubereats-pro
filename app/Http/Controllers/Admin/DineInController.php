<?php

namespace App\Http\Controllers\Admin;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Models\StoreTable;
use App\Models\TableReservation;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;

class DineInController extends Controller
{
    public function tables(Request $request)
    {
        if (!Helpers::module_permission_check('dine_in')) {
            Toastr::error(translate('messages.access_denied'));
            return back();
        }
        $tables = StoreTable::with('store')
            ->when($request->store_id, function ($query) use ($request) {
                $query->where('store_id', $request->store_id);
            })
            ->latest()
            ->paginate(25);

        return view('admin-views.dine-in.tables', compact('tables'));
    }

    public function reservations(Request $request)
    {
        if (!Helpers::module_permission_check('dine_in')) {
            Toastr::error(translate('messages.access_denied'));
            return back();
        }
        $status = $request->get('status', 'all');
        $reservations = TableReservation::with(['store', 'storeTable', 'user'])
            ->when($status != 'all', function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->when($request->store_id, function ($query) use ($request) {
                $query->where('store_id', $request->store_id);
            })
            ->latest()
            ->paginate(25);

        return view('admin-views.dine-in.reservations', compact('reservations', 'status'));
    }

    public function confirmReservation($id)
    {
        if (!Helpers::module_permission_check('dine_in')) {
            Toastr::error(translate('messages.access_denied'));
            return back();
        }
        $reservation = TableReservation::findOrFail($id);
        $reservation->status = 'confirmed';
        $reservation->confirmed_at = now();
        $reservation->save();

        Toastr::success(translate('messages.Reservation confirmed successfully'));
        return redirect()->back();
    }

    public function completeReservation($id)
    {
        if (!Helpers::module_permission_check('dine_in')) {
            Toastr::error(translate('messages.access_denied'));
            return back();
        }
        $reservation = TableReservation::findOrFail($id);
        $reservation->status = 'completed';
        $reservation->save();

        Toastr::success(translate('messages.Reservation completed successfully'));
        return redirect()->back();
    }

    public function cancelReservation(Request $request, $id)
    {
        if (!Helpers::module_permission_check('dine_in')) {
            Toastr::error(translate('messages.access_denied'));
            return back();
        }
        $reservation = TableReservation::findOrFail($id);
        $reservation->status = 'cancelled';
        $reservation->cancellation_reason = $request->reason;
        $reservation->save();

        Toastr::success(translate('messages.Reservation cancelled successfully'));
        return redirect()->back();
    }
}
