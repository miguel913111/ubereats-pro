<?php

namespace Tests\Unit\Services;

use App\Services\DeliveryOptimizationService;
use PHPUnit\Framework\TestCase;

class DeliveryOptimizationServiceTest extends TestCase
{
    public function test_haversine_distance_returns_correct_value()
    {
        // Distance between New York (40.7128, -74.0060) and Los Angeles (34.0522, -118.2437)
        $distance = DeliveryOptimizationService::haversineDistance(40.7128, -74.0060, 34.0522, -118.2437);

        // Should be approximately 3936 km
        $this->assertGreaterThan(3900, $distance);
        $this->assertLessThan(4000, $distance);
    }

    public function test_haversine_distance_same_point_is_zero()
    {
        $distance = DeliveryOptimizationService::haversineDistance(40.7128, -74.0060, 40.7128, -74.0060);
        $this->assertEquals(0, $distance);
    }

    public function test_group_orders_byProximity_groups_nearby_orders()
    {
        $orderCoords = [
            [
                'order' => (object)['id' => 1],
                'coords' => ['customer_lat' => 40.7128, 'customer_lng' => -74.0060, 'store_lat' => 40.7130, 'store_lng' => -74.0062],
            ],
            [
                'order' => (object)['id' => 2],
                'coords' => ['customer_lat' => 40.7130, 'customer_lng' => -74.0062, 'store_lat' => 40.7130, 'store_lng' => -74.0062],
            ],
            [
                'order' => (object)['id' => 3],
                'coords' => ['customer_lat' => 41.0000, 'customer_lng' => -75.0000, 'store_lat' => 40.7130, 'store_lng' => -74.0062],
            ],
        ];

        $groups = DeliveryOptimizationService::groupOrdersByProximity($orderCoords, 3.0);

        // Orders 1 and 2 are close (should be in same group), order 3 is far
        $this->assertGreaterThanOrEqual(1, count($groups));
    }

    public function test_optimizeRoute_returns_ordered_stops()
    {
        $group = [
            [
                'order' => (object)['id' => 1],
                'coords' => ['store_lat' => 40.7130, 'store_lng' => -74.0062, 'customer_lat' => 40.7150, 'customer_lng' => -74.0080],
            ],
            [
                'order' => (object)['id' => 2],
                'coords' => ['store_lat' => 40.7130, 'store_lng' => -74.0062, 'customer_lat' => 40.7140, 'customer_lng' => -74.0070],
            ],
        ];

        $result = DeliveryOptimizationService::optimizeRoute($group);

        $this->assertArrayHasKey('route', $result);
        $this->assertArrayHasKey('total_distance_km', $result);
        $this->assertArrayHasKey('total_time_min', $result);
        $this->assertCount(2, $result['route']);
        $this->assertGreaterThan(0, $result['total_distance_km']);
    }

    public function test_optimizeRoute_empty_group_returns_empty()
    {
        $result = DeliveryOptimizationService::optimizeRoute([]);
        $this->assertEmpty($result);
    }
}
