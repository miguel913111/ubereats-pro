<?php

namespace App\Http\Controllers\Api\V1;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Models\StoreTable;
use App\Models\TableReservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DineInController extends Controller
{
    public function getStores(Request $request)
    {
        $stores = Store::where('dine_in', 1)
            ->where('status', 1)
            ->when($request->zone_id, function ($query) use ($request) {
                $query->where('zone_id', $request->zone_id);
            })
            ->get();

        return response()->json($stores, 200);
    }

    public function getTables(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'store_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $tables = StoreTable::where('store_id', $request->store_id)
            ->where('status', 'available')
            ->get();

        return response()->json($tables, 200);
    }

    public function checkAvailability(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'store_id' => 'required|integer',
            'store_table_id' => 'required|integer',
            'reservation_date' => 'required|date',
            'reservation_time' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $existing = TableReservation::where('store_table_id', $request->store_table_id)
            ->where('reservation_date', $request->reservation_date)
            ->where('reservation_time', $request->reservation_time)
            ->whereIn('status', ['pending', 'confirmed'])
            ->exists();

        return response()->json([
            'available' => !$existing,
            'message' => $existing ? translate('messages.Table not available at this time') : translate('messages.Table is available'),
        ], 200);
    }

    public function bookTable(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'store_id' => 'required|integer',
            'store_table_id' => 'required|integer',
            'reservation_date' => 'required|date|after_or_equal:today',
            'reservation_time' => 'required',
            'number_of_guests' => 'required|integer|min:1',
            'special_request' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $existing = TableReservation::where('store_table_id', $request->store_table_id)
            ->where('reservation_date', $request->reservation_date)
            ->where('reservation_time', $request->reservation_time)
            ->whereIn('status', ['pending', 'confirmed'])
            ->exists();

        if ($existing) {
            return response()->json(['errors' => [['code' => 'not_available', 'message' => translate('messages.Table is already reserved at this time')]]], 403);
        }

        $reservation = TableReservation::create([
            'store_id' => $request->store_id,
            'store_table_id' => $request->store_table_id,
            'user_id' => $request->user()->id,
            'reservation_date' => $request->reservation_date,
            'reservation_time' => $request->reservation_time,
            'number_of_guests' => $request->number_of_guests,
            'special_request' => $request->special_request,
            'status' => 'pending',
        ]);

        return response()->json([
            'reservation' => $reservation,
            'message' => translate('messages.Table reserved successfully'),
        ], 200);
    }

    public function myReservations(Request $request)
    {
        $reservations = TableReservation::where('user_id', $request->user()->id)
            ->with(['store', 'storeTable'])
            ->latest()
            ->get();

        return response()->json($reservations, 200);
    }

    public function cancelReservation(Request $request, $id)
    {
        $reservation = TableReservation::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$reservation) {
            return response()->json(['errors' => [['code' => 'not_found', 'message' => translate('messages.Reservation not found')]]], 404);
        }

        if (!in_array($reservation->status, ['pending', 'confirmed'])) {
            return response()->json(['errors' => [['code' => 'cannot_cancel', 'message' => translate('messages.Cannot cancel this reservation')]]], 403);
        }

        $reservation->status = 'cancelled';
        $reservation->cancellation_reason = $request->reason;
        $reservation->save();

        return response()->json([
            'message' => translate('messages.Reservation cancelled successfully'),
        ], 200);
    }
}
