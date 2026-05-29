<?php

namespace App\Http\Controllers\Api\V1;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Models\StoreTable;
use App\Models\TableReservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VendorDineInController extends Controller
{
    public function getTables(Request $request)
    {
        $tables = StoreTable::where('store_id', $request->user()->id)
            ->get();

        return response()->json($tables, 200);
    }

    public function storeTable(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'table_number' => 'required|string|max:50',
            'capacity' => 'required|integer|min:1',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $table = StoreTable::create([
            'store_id' => $request->user()->id,
            'table_number' => $request->table_number,
            'capacity' => $request->capacity,
            'description' => $request->description,
            'status' => 'available',
        ]);

        return response()->json([
            'table' => $table,
            'message' => translate('messages.Table created successfully'),
        ], 200);
    }

    public function updateTable(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'table_number' => 'required|string|max:50',
            'capacity' => 'required|integer|min:1',
            'status' => 'required|in:available,occupied,reserved,maintenance',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $table = StoreTable::where('id', $id)
            ->where('store_id', $request->user()->id)
            ->firstOrFail();

        $table->update($request->only(['table_number', 'capacity', 'status', 'description']));

        return response()->json([
            'table' => $table,
            'message' => translate('messages.Table updated successfully'),
        ], 200);
    }

    public function deleteTable($id)
    {
        $table = StoreTable::where('id', $id)
            ->where('store_id', auth('api')->user()->id)
            ->firstOrFail();

        $table->delete();

        return response()->json([
            'message' => translate('messages.Table deleted successfully'),
        ], 200);
    }

    public function getReservations(Request $request)
    {
        $status = $request->get('status', 'all');
        $reservations = TableReservation::with('user')
            ->where('store_id', $request->user()->id)
            ->when($status != 'all', function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->latest()
            ->get();

        return response()->json($reservations, 200);
    }

    public function confirmReservation($id)
    {
        $reservation = TableReservation::where('id', $id)
            ->where('store_id', auth('api')->user()->id)
            ->firstOrFail();

        $reservation->status = 'confirmed';
        $reservation->confirmed_at = now();
        $reservation->save();

        return response()->json([
            'message' => translate('messages.Reservation confirmed successfully'),
        ], 200);
    }

    public function completeReservation($id)
    {
        $reservation = TableReservation::where('id', $id)
            ->where('store_id', auth('api')->user()->id)
            ->firstOrFail();

        $reservation->status = 'completed';
        $reservation->save();

        return response()->json([
            'message' => translate('messages.Reservation completed successfully'),
        ], 200);
    }

    public function cancelReservation(Request $request, $id)
    {
        $reservation = TableReservation::where('id', $id)
            ->where('store_id', auth('api')->user()->id)
            ->firstOrFail();

        $reservation->status = 'cancelled';
        $reservation->cancellation_reason = $request->reason;
        $reservation->save();

        return response()->json([
            'message' => translate('messages.Reservation cancelled successfully'),
        ], 200);
    }

    public function storeDeliveryZone(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:191',
            'coordinates' => 'required|array',
            'delivery_charge' => 'nullable|numeric|min:0',
            'minimum_order_amount' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $zone = \App\Models\StoreDeliveryZone::create([
            'store_id' => $request->user()->id,
            'name' => $request->name ?? 'Zone',
            'coordinates' => $request->coordinates,
            'delivery_charge' => $request->delivery_charge ?? 0,
            'minimum_order_amount' => $request->minimum_order_amount ?? 0,
            'status' => 1,
        ]);

        return response()->json([
            'zone' => $zone,
            'message' => translate('messages.Delivery zone created successfully'),
        ], 200);
    }

    public function getDeliveryZones(Request $request)
    {
        $zones = \App\Models\StoreDeliveryZone::where('store_id', $request->user()->id)->get();
        return response()->json($zones, 200);
    }

    public function deleteDeliveryZone($id)
    {
        $zone = \App\Models\StoreDeliveryZone::where('id', $id)
            ->where('store_id', auth('api')->user()->id)
            ->firstOrFail();

        $zone->delete();

        return response()->json([
            'message' => translate('messages.Delivery zone deleted successfully'),
        ], 200);
    }
}
