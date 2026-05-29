<?php

namespace App\Http\Controllers\Api\V1;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Models\DeliveryBatch;
use App\Models\DeliveryTimeWindow;
use App\Services\DeliveryOptimizationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DeliveryBatchController extends Controller
{
    /**
     * Get active batch for logged-in delivery man
     */
    public function getMyBatch(Request $request)
    {
        $dm = $request->user();
        $batch = DeliveryOptimizationService::getActiveBatch($dm->id);

        if (!$batch) {
            return response()->json(['batch' => null], 200);
        }

        return response()->json([
            'batch' => [
                'id' => $batch->id,
                'status' => $batch->status,
                'total_orders' => $batch->total_orders,
                'total_distance_km' => $batch->total_distance_km,
                'estimated_duration_min' => $batch->estimated_duration_min,
                'started_at' => $batch->started_at,
                'orders' => $batch->batchOrders->map(function ($bo) {
                    return [
                        'sequence' => $bo->delivery_sequence,
                        'order_id' => $bo->order_id,
                        'distance_from_prev_km' => $bo->distance_from_prev_km,
                        'estimated_time_min' => $bo->estimated_time_min,
                        'picked_up_at' => $bo->picked_up_at,
                        'delivered_at' => $bo->delivered_at,
                        'customer_name' => $bo->order?->customer?->f_name . ' ' . $bo->order?->customer?->l_name,
                        'customer_phone' => $bo->order?->customer?->phone,
                        'delivery_address' => $bo->order?->delivery_address,
                        'order_amount' => $bo->order?->order_amount,
                        'payment_method' => $bo->order?->payment_method,
                        'order_status' => $bo->order?->order_status,
                    ];
                }),
                'route_segments' => $batch->routeSegments->map(function ($seg) {
                    return [
                        'sequence' => $seg->sequence,
                        'from_lat' => $seg->from_lat,
                        'from_lng' => $seg->from_lng,
                        'to_lat' => $seg->to_lat,
                        'to_lng' => $seg->to_lng,
                        'distance_km' => $seg->distance_km,
                        'estimated_minutes' => $seg->estimated_minutes,
                    ];
                }),
            ],
        ], 200);
    }

    /**
     * Start batch (mark as picked up)
     */
    public function startBatch(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'batch_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $dm = $request->user();
        $batch = DeliveryBatch::where('id', $request->batch_id)
            ->where('delivery_man_id', $dm->id)
            ->first();

        if (!$batch) {
            return response()->json(['errors' => [['code' => 'not_found', 'message' => 'Batch not found']]], 404);
        }

        $success = DeliveryOptimizationService::startBatch($batch->id);

        if (!$success) {
            return response()->json(['errors' => [['code' => 'invalid_status', 'message' => 'Batch cannot be started']]], 400);
        }

        return response()->json(['message' => 'Batch started successfully'], 200);
    }

    /**
     * Mark single order in batch as delivered
     */
    public function deliverOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'batch_id' => 'required|integer',
            'order_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $dm = $request->user();
        $batch = DeliveryBatch::where('id', $request->batch_id)
            ->where('delivery_man_id', $dm->id)
            ->first();

        if (!$batch) {
            return response()->json(['errors' => [['code' => 'not_found', 'message' => 'Batch not found']]], 404);
        }

        $success = DeliveryOptimizationService::deliverBatchOrder($batch->id, $request->order_id);

        if (!$success) {
            return response()->json(['errors' => [['code' => 'failed', 'message' => 'Failed to mark order as delivered']]], 400);
        }

        return response()->json(['message' => 'Order marked as delivered'], 200);
    }

    /**
     * Get delivery time windows for a zone
     */
    public function getTimeWindows(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'zone_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $windows = DeliveryTimeWindow::where('zone_id', $request->zone_id)
            ->where('status', 1)
            ->get();

        return response()->json(['time_windows' => $windows], 200);
    }
}
