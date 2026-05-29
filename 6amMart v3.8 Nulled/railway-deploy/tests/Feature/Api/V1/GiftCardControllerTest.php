<?php

namespace Tests\Feature\Api\V1;

use App\Models\GiftCard;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GiftCardControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_list_returns_active_gift_cards()
    {
        $user = User::factory()->create();
        $giftCard = GiftCard::factory()->create(['status' => 1]);

        $response = $this->actingAs($user, 'api')
            ->getJson('/api/v1/customer/gift-card/list');

        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $giftCard->id]);
    }

    public function test_apply_valid_gift_card()
    {
        $user = User::factory()->create();
        $giftCard = GiftCard::factory()->create([
            'code' => 'TEST2025',
            'discount' => 10.00,
            'status' => 1,
            'expiry_date' => now()->addMonth(),
        ]);

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/v1/customer/gift-card/apply', [
                'code' => 'TEST2025',
            ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['success' => true]);
    }

    public function test_apply_expired_gift_card_fails()
    {
        $user = User::factory()->create();
        $giftCard = GiftCard::factory()->create([
            'code' => 'EXPIRED2025',
            'status' => 1,
            'expiry_date' => now()->subDay(),
        ]);

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/v1/customer/gift-card/apply', [
                'code' => 'EXPIRED2025',
            ]);

        $response->assertStatus(400)
            ->assertJsonFragment(['success' => false]);
    }

    public function test_apply_invalid_code_fails()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/v1/customer/gift-card/apply', [
                'code' => 'INVALID',
            ]);

        $response->assertStatus(404)
            ->assertJsonFragment(['success' => false]);
    }
}
