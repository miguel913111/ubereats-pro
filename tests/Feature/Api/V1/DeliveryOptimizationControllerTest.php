<?php

namespace Tests\Feature\Api\V1;

use App\Models\DeliveryMan;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeliveryOptimizationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_suggest_batch_requires_token()
    {
        $response = $this->postJson('/api/v1/delivery-optimization/suggest-batch', [
            'zone_id' => 1,
        ]);

        $response->assertStatus(403)
            ->assertJsonStructure(['errors']);
    }

    public function test_suggest_batch_with_invalid_token()
    {
        $response = $this->postJson('/api/v1/delivery-optimization/suggest-batch', [
            'token' => 'invalid_token',
            'zone_id' => 1,
        ]);

        $response->assertStatus(401)
            ->assertJsonFragment(['code' => 'auth']);
    }

    public function test_suggest_batch_returns_empty_when_disabled()
    {
        $zone = Zone::factory()->create();
        $dm = DeliveryMan::factory()->create(['zone_id' => $zone->id, 'auth_token' => 'test_token_123']);

        \App\Models\BusinessSetting::create([
            'key' => 'batch_delivery_enabled',
            'value' => '0',
        ]);

        $response = $this->postJson('/api/v1/delivery-optimization/suggest-batch', [
            'token' => 'test_token_123',
            'zone_id' => $zone->id,
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'Batch delivery is currently disabled']);
    }

    public function test_estimate_window_requires_coordinates()
    {
        $response = $this->postJson('/api/v1/delivery-optimization/estimate-window', [
            'token' => 'some_token',
        ]);

        $response->assertStatus(403)
            ->assertJsonStructure(['errors']);
    }

    public function test_estimate_window_calculates_distance()
    {
        $zone = Zone::factory()->create();
        $dm = DeliveryMan::factory()->create(['zone_id' => $zone->id, 'auth_token' => 'test_token_456']);

        $response = $this->postJson('/api/v1/delivery-optimization/estimate-window', [
            'token' => 'test_token_456',
            'store_lat' => 40.7128,
            'store_lng' => -74.0060,
            'customer_lat' => 40.7300,
            'customer_lng' => -74.0200,
            'zone_id' => $zone->id,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['distance_km', 'estimated_time_min', 'time_windows']);
    }
}
