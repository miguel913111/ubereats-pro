<?php

namespace App\Http\Controllers\Api\V1;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Models\DeliveryMan;
use App\Models\DeliveryTimeWindow;
use App\Services\DeliveryOptimizationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DeliveryOptimizationController extends Controller
{
    /**
     * Suggest delivery batches for the authenticated delivery man
     * POST /api/v1/delivery-optimization/suggest-batch
     */
    public function suggestBatch(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'zone_id' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $dm = DeliveryMan::where(['auth_token' => $request['token']])->first();
        if (!$dm) {
            return response()->json([
                'errors' => [['code' => 'auth', 'message' => translate('messages.invalid_token')]]
            ], 401);
        }

        $zoneId = $request->input('zone_id', $dm->zone_id);
        if (!$zoneId) {
            return response()->json([
                'errors' => [['code' => 'zone', 'message' => translate('messages.zone_id_required')]]
            ], 400);
        }

        $settings = DeliveryOptimizationService::getSettings();
        if (!$settings['enabled']) {
            return response()->json([
                'message' => translate('messages.batch_delivery_disabled'),
                'batches' => [],
            ], 200);
        }

        $orderCoords = DeliveryOptimizationService::findBatchableOrders($zoneId);
        if (empty($orderCoords)) {
            return response()->json([
                'message' => translate('messages.no_batchable_orders'),
                'batches' => [],
            ], 200);
        }

        $groups = DeliveryOptimizationService::groupOrdersByProximity($orderCoords, $settings['max_radius_km']);
        $suggestions = [];

        foreach ($groups as $group) {
            if (count($group) < $settings['min_orders']) {
                continue;
            }

            $optimized = DeliveryOptimizationService::optimizeRoute($group);
            $orders = [];
            foreach ($optimized['route'] as $stop) {
                $order = $stop['order'];
                $orders[] = [
                    'id' => $order->id,
                    'order_status' => $order->order_status,
                    'order_amount' => $order->order_amount,
                    'delivery_charge' => $order->delivery_charge,
                    'customer_name' => $order->customer?->f_name . ' ' . $order->customer?->l_name,
                    'customer_phone' => $order->customer?->phone,
                    'customer_lat' => $stop['coords']['customer_lat'],
                    'customer_lng' => $stop['coords']['customer_lng'],
                    'store_name' => $order->store?->name,
                    'store_lat' => $stop['coords']['store_lat'],
                    'store_lng' => $stop['coords']['store_lng'],
                    'sequence' => count($orders) + 1,
                    'distance_from_prev_km' => $stop['distance_from_prev_km'],
                    'estimated_time_min' => $stop['estimated_time_min'],
                ];
            }

            $suggestions[] = [
                'total_orders' => count($orders),
                'total_distance_km' => $optimized['total_distance_km'],
                'total_time_min' => $optimized['total_time_min'],
                'orders' => $orders,
            ];
        }

        return response()->json([
            'message' => translate('messages.batch_suggestions_found'),
            'count' => count($suggestions),
            'suggestions' => $suggestions,
        ], 200);
    }

    /**
     * Estimate delivery time windows based on coordinates
     * POST /api/v1/delivery-optimization/estimate-window
     */
    public function estimateWindow(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'store_lat' => 'required|numeric',
            'store_lng' => 'required|numeric',
            'customer_lat' => 'required|numeric',
            'customer_lng' => 'required|numeric',
            'zone_id' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $dm = DeliveryMan::where(['auth_token' => $request['token']])->first();
        if (!$dm) {
            return response()->json([
                'errors' => [['code' => 'auth', 'message' => translate('messages.invalid_token')]]
            ], 401);
        }

        $zoneId = $request->input('zone_id', $dm->zone_id);

        $distance = DeliveryOptimizationService::haversineDistance(
            (float) $request->store_lat,
            (float) $request->store_lng,
            (float) $request->customer_lat,
            (float) $request->customer_lng
        );

        $estimatedMinutes = round($distance * 3 + 5, 2); // ~3 min/km + 5 min stop

        $timeWindows = [];
        if ($zoneId) {
            $windows = DeliveryTimeWindow::where('zone_id', $zoneId)
                ->orWhereNull('zone_id')
                ->get();

            foreach ($windows as $window) {
                $timeWindows[] = [
                    'id' => $window->id,
                    'start_time' => $window->start_time,
                    'end_time' => $window->end_time,
                    'day' => $window->day,
                    'max_orders' => $window->max_orders,
                ];
            }
        }

        return response()->json([
            'distance_km' => round($distance, 2),
            'estimated_time_min' => $estimatedMinutes,
            'estimated_time_formatted' => gmdate('H:i', (int)($estimatedMinutes * 60)),
            'time_windows' => $timeWindows,
        ], 200);
    }
}
