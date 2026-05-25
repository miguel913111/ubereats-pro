<?php

namespace Tests\Feature\Api\V1;

use App\Models\Store;
use App\Models\StoreTable;
use App\Models\TableReservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DineInControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_stores_returns_dine_in_enabled_stores()
    {
        $store = Store::factory()->create(['dine_in' => 1, 'status' => 1]);
        Store::factory()->create(['dine_in' => 0, 'status' => 1]);

        $response = $this->getJson('/api/v1/customer/dine-in/stores');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'stores')
            ->assertJsonFragment(['id' => $store->id]);
    }

    public function test_get_tables_returns_tables_for_store()
    {
        $store = Store::factory()->create(['dine_in' => 1]);
        $table = StoreTable::factory()->create(['store_id' => $store->id, 'status' => 'available']);

        $response = $this->getJson("/api/v1/customer/dine-in/tables?store_id={$store->id}");

        $response->assertStatus(200)
            ->assertJsonCount(1, 'tables')
            ->assertJsonFragment(['id' => $table->id]);
    }

    public function test_book_table_creates_reservation()
    {
        $user = User::factory()->create();
        $store = Store::factory()->create(['dine_in' => 1]);
        $table = StoreTable::factory()->create(['store_id' => $store->id]);

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/v1/customer/dine-in/book', [
                'store_id' => $store->id,
                'store_table_id' => $table->id,
                'reservation_date' => now()->addDay()->toDateString(),
                'reservation_time' => '19:00',
                'number_of_guests' => 4,
                'special_request' => 'Window seat',
            ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['success' => true]);

        $this->assertDatabaseHas('table_reservations', [
            'store_id' => $store->id,
            'user_id' => $user->id,
            'status' => 'pending',
        ]);
    }

    public function test_cancel_reservation_updates_status()
    {
        $user = User::factory()->create();
        $store = Store::factory()->create(['dine_in' => 1]);
        $table = StoreTable::factory()->create(['store_id' => $store->id]);
        $reservation = TableReservation::factory()->create([
            'store_id' => $store->id,
            'store_table_id' => $table->id,
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user, 'api')
            ->postJson("/api/v1/customer/dine-in/cancel/{$reservation->id}", [
                'cancellation_reason' => 'Changed plans',
            ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['success' => true]);

        $this->assertDatabaseHas('table_reservations', [
            'id' => $reservation->id,
            'status' => 'cancelled',
        ]);
    }
}
