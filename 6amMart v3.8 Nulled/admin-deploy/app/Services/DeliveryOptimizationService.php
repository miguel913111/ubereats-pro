<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Store;
use App\Models\DeliveryMan;
use App\Models\DeliveryBatch;
use App\Models\BatchOrder;
use App\Models\DeliveryRouteSegment;
use App\Models\CustomerAddress;
use App\Models\BusinessSetting;
use Illuminate\Support\Facades\DB;

class DeliveryOptimizationService
{
    /**
     * Calculate distance between two coordinates using Haversine formula (km)
     */
    public static function haversineDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371; // km
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLng / 2) * sin($dLng / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }

    /**
     * Get batch delivery settings
     */
    public static function getSettings(): array
    {
        return [
            'enabled' => (BusinessSetting::where('key', 'batch_delivery_enabled')->first()?->value ?? '1') == '1',
            'max_radius_km' => (float) (BusinessSetting::where('key', 'batch_max_radius_km')->first()?->value ?? 3.0),
            'max_orders' => (int) (BusinessSetting::where('key', 'batch_max_orders')->first()?->value ?? 4),
            'time_window_min' => (int) (BusinessSetting::where('key', 'batch_time_window_minutes')->first()?->value ?? 30),
            'min_orders' => (int) (BusinessSetting::where('key', 'batch_min_orders_to_group')->first()?->value ?? 2),
        ];
    }

    /**
     * Get coordinates for an order (store lat/lng and customer lat/lng)
     */
    public static function getOrderCoordinates(Order $order): ?array
    {
        $store = $order->store;
        if (!$store || empty($store->latitude) || empty($store->longitude)) {
            return null;
        }

        $customerLat = null;
        $customerLng = null;

        if ($order->delivery_address_id) {
            $address = CustomerAddress::find($order->delivery_address_id);
            if ($address) {
                $customerLat = $address->latitude;
                $customerLng = $address->longitude;
            }
        }

        // fallback: try to parse from delivery_address text
        if (empty($customerLat) || empty($customerLng)) {
            $addressData = json_decode($order->delivery_address, true);
            if ($addressData) {
                $customerLat = $addressData['latitude'] ?? null;
                $customerLng = $addressData['longitude'] ?? null;
            }
        }

        if (empty($customerLat) || empty($customerLng)) {
            return null;
        }

        return [
            'store_lat' => (float) $store->latitude,
            'store_lng' => (float) $store->longitude,
            'customer_lat' => (float) $customerLat,
            'customer_lng' => (float) $customerLng,
        ];
    }

    /**
     * Find available orders that can be batched together
     */
    public static function findBatchableOrders(int $zoneId, array $excludeOrderIds = []): array
    {
        $settings = self::getSettings();
        if (!$settings['enabled']) {
            return [];
        }

        $orders = Order::with(['store', 'customer'])
            ->where('zone_id', $zoneId)
            ->whereNull('batch_id')
            ->whereNull('delivery_man_id')
            ->whereIn('order_status', ['confirmed', 'processing', 'handover'])
            ->where('order_type', 'delivery')
            ->whereNotIn('id', $excludeOrderIds)
            ->where('created_at', '>=', now()->subMinutes($settings['time_window_min']))
            ->limit(50)
            ->get();

        $orderCoords = [];
        foreach ($orders as $order) {
            $coords = self::getOrderCoordinates($order);
            if ($coords) {
                $orderCoords[] = [
                    'order' => $order,
                    'coords' => $coords,
                ];
            }
        }

        return $orderCoords;
    }

    /**
     * Group orders by proximity using a simple clustering algorithm
     */
    public static function groupOrdersByProximity(array $orderCoords, float $maxRadiusKm = 3.0): array
    {
        $groups = [];
        $visited = [];

        foreach ($orderCoords as $i => $itemA) {
            if (in_array($i, $visited)) continue;

            $group = [$itemA];
            $visited[] = $i;

            foreach ($orderCoords as $j => $itemB) {
                if ($i === $j || in_array($j, $visited)) continue;

                // Calculate distance between customer locations
                $distance = self::haversineDistance(
                    $itemA['coords']['customer_lat'],
                    $itemA['coords']['customer_lng'],
                    $itemB['coords']['customer_lat'],
                    $itemB['coords']['customer_lng']
                );

                if ($distance <= $maxRadiusKm) {
                    $group[] = $itemB;
                    $visited[] = $j;
                }
            }

            $groups[] = $group;
        }

        return $groups;
    }

    /**
     * Optimize route using Nearest Neighbor algorithm
     * Route: Store -> Customer1 -> Customer2 -> ...
     */
    public static function optimizeRoute(array $group): array
    {
        if (empty($group)) {
            return [];
        }

        // Start from the store location of the first order
        $startLat = $group[0]['coords']['store_lat'];
        $startLng = $group[0]['coords']['store_lng'];

        $unvisited = $group;
        $route = [];
        $currentLat = $startLat;
        $currentLng = $startLng;
        $totalDistance = 0;
        $totalTime = 0;

        while (!empty($unvisited)) {
            $nearestIndex = 0;
            $nearestDistance = PHP_FLOAT_MAX;

            foreach ($unvisited as $idx => $item) {
                $dist = self::haversineDistance(
                    $currentLat,
                    $currentLng,
                    $item['coords']['customer_lat'],
                    $item['coords']['customer_lng']
                );

                if ($dist < $nearestDistance) {
                    $nearestDistance = $dist;
                    $nearestIndex = $idx;
                }
            }

            $nearest = $unvisited[$nearestIndex];
            $route[] = [
                'order' => $nearest['order'],
                'coords' => $nearest['coords'],
                'distance_from_prev_km' => round($nearestDistance, 2),
                'estimated_time_min' => round($nearestDistance * 3 + 5, 2), // ~3 min/km + 5 min stop
            ];

            $totalDistance += $nearestDistance;
            $totalTime += ($nearestDistance * 3 + 5);
            $currentLat = $nearest['coords']['customer_lat'];
            $currentLng = $nearest['coords']['customer_lng'];

            unset($unvisited[$nearestIndex]);
            $unvisited = array_values($unvisited);
        }

        return [
            'route' => $route,
            'total_distance_km' => round($totalDistance, 2),
            'total_time_min' => round($totalTime, 2),
        ];
    }

    /**
     * Create a delivery batch from grouped orders
     */
    public static function createBatch(int $deliveryManId, array $optimizedRoute, int $zoneId = null): ?DeliveryBatch
    {
        $settings = self::getSettings();
        $route = $optimizedRoute['route'];

        if (count($route) < $settings['min_orders']) {
            return null;
        }

        if (count($route) > $settings['max_orders']) {
            $route = array_slice($route, 0, $settings['max_orders']);
        }

        DB::beginTransaction();
        try {
            $batch = DeliveryBatch::create([
                'delivery_man_id' => $deliveryManId,
                'zone_id' => $zoneId,
                'status' => 'pending',
                'total_distance_km' => $optimizedRoute['total_distance_km'],
                'estimated_duration_min' => $optimizedRoute['total_time_min'],
                'total_orders' => count($route),
            ]);

            $prevLat = $route[0]['coords']['store_lat'];
            $prevLng = $route[0]['coords']['store_lng'];

            foreach ($route as $sequence => $stop) {
                BatchOrder::create([
                    'batch_id' => $batch->id,
                    'order_id' => $stop['order']->id,
                    'delivery_sequence' => $sequence + 1,
                    'distance_from_prev_km' => $stop['distance_from_prev_km'],
                    'estimated_time_min' => $stop['estimated_time_min'],
                ]);

                // Link order to batch
                $stop['order']->update(['batch_id' => $batch->id]);

                // Create route segment
                DeliveryRouteSegment::create([
                    'batch_id' => $batch->id,
                    'sequence' => $sequence + 1,
                    'from_lat' => $prevLat,
                    'from_lng' => $prevLng,
                    'to_lat' => $stop['coords']['customer_lat'],
                    'to_lng' => $stop['coords']['customer_lng'],
                    'from_type' => $sequence === 0 ? 'store' : 'customer',
                    'to_type' => 'customer',
                    'order_id' => $stop['order']->id,
                    'distance_km' => $stop['distance_from_prev_km'],
                    'estimated_minutes' => $stop['estimated_time_min'],
                ]);

                $prevLat = $stop['coords']['customer_lat'];
                $prevLng = $stop['coords']['customer_lng'];
            }

            // Update delivery man current orders
            $dm = DeliveryMan::find($deliveryManId);
            if ($dm) {
                $dm->current_orders = $dm->current_orders + count($route);
                $dm->save();
            }

            DB::commit();
            return $batch;
        } catch (\Exception $e) {
            DB::rollBack();
            info('Batch creation failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Auto-generate batches for a zone
     */
    public static function autoBatchZone(int $zoneId): array
    {
        $settings = self::getSettings();
        if (!$settings['enabled']) {
            return ['message' => 'Batch delivery disabled', 'batches' => []];
        }

        $orderCoords = self::findBatchableOrders($zoneId);
        if (empty($orderCoords)) {
            return ['message' => 'No batchable orders found', 'batches' => []];
        }

        $groups = self::groupOrdersByProximity($orderCoords, $settings['max_radius_km']);
        $batches = [];

        foreach ($groups as $group) {
            if (count($group) < $settings['min_orders']) {
                continue;
            }

            $optimized = self::optimizeRoute($group);

            // Find available delivery man
            $dm = DeliveryMan::where('zone_id', $zoneId)
                ->available()
                ->active()
                ->where('current_orders', '<', config('dm_maximum_orders', 1))
                ->first();

            if (!$dm) {
                continue;
            }

            $batch = self::createBatch($dm->id, $optimized, $zoneId);
            if ($batch) {
                $batches[] = $batch;
            }
        }

        return [
            'message' => count($batches) . ' batches created',
            'batches' => $batches,
        ];
    }

    /**
     * Get active batch for a delivery man
     */
    public static function getActiveBatch(int $deliveryManId): ?DeliveryBatch
    {
        return DeliveryBatch::with(['batchOrders.order', 'routeSegments'])
            ->where('delivery_man_id', $deliveryManId)
            ->whereIn('status', ['pending', 'active'])
            ->latest()
            ->first();
    }

    /**
     * Mark batch as started
     */
    public static function startBatch(int $batchId): bool
    {
        $batch = DeliveryBatch::find($batchId);
        if (!$batch || $batch->status !== 'pending') {
            return false;
        }

        $batch->update([
            'status' => 'active',
            'started_at' => now(),
        ]);

        // Update all orders to picked_up
        foreach ($batch->batchOrders as $bo) {
            $bo->order->update(['order_status' => 'picked_up']);
            $bo->update(['picked_up_at' => now()]);
        }

        return true;
    }

    /**
     * Mark a single order in batch as delivered
     */
    public static function deliverBatchOrder(int $batchId, int $orderId): bool
    {
        $bo = BatchOrder::where('batch_id', $batchId)
            ->where('order_id', $orderId)
            ->first();

        if (!$bo) {
            return false;
        }

        $bo->update(['delivered_at' => now()]);
        $bo->order->update(['order_status' => 'delivered', 'delivered' => now()]);

        // Check if all orders in batch are delivered
        $pending = BatchOrder::where('batch_id', $batchId)
            ->whereNull('delivered_at')
            ->count();

        if ($pending === 0) {
            $batch = DeliveryBatch::find($batchId);
            $batch->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            // Decrement delivery man current orders
            $dm = $batch->deliveryMan;
            if ($dm) {
                $dm->current_orders = max(0, $dm->current_orders - $batch->total_orders);
                $dm->save();
            }
        }

        return true;
    }
}
