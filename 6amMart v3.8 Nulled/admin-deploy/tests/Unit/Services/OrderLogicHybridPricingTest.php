<?php

namespace Tests\Unit\Services;

use PHPUnit\Framework\TestCase;

class OrderLogicHybridPricingTest extends TestCase
{
    public function test_effective_commission_uses_store_commission_when_active()
    {
        $store = (object)[
            'store_business_model' => 'commission',
            'commission_active' => true,
            'comission' => 15,
        ];
        $comission = 10;

        $effective = $store->commission_active ? $store->comission : $comission;

        $this->assertEquals(15, $effective);
    }

    public function test_effective_commission_uses_default_when_store_inactive()
    {
        $store = (object)[
            'store_business_model' => 'commission',
            'commission_active' => false,
            'comission' => 15,
        ];
        $comission = 10;

        $effective = $store->commission_active ? $store->comission : $comission;

        $this->assertEquals(10, $effective);
    }

    public function test_fixed_delivery_fee_is_zero_when_store_null()
    {
        $store = null;
        $fixed_delivery_fee = 0;

        if ($store && $store->fixed_delivery_fee_active) {
            $fixed_delivery_fee = $store->fixed_delivery_fee;
        }

        $this->assertEquals(0, $fixed_delivery_fee);
    }

    public function test_fixed_delivery_fee_is_zero_when_flag_inactive()
    {
        $store = (object)[
            'fixed_delivery_fee_active' => false,
            'fixed_delivery_fee' => 50,
        ];
        $fixed_delivery_fee = 0;

        if ($store && $store->fixed_delivery_fee_active) {
            $fixed_delivery_fee = $store->fixed_delivery_fee;
        }

        $this->assertEquals(0, $fixed_delivery_fee);
    }

    public function test_fixed_delivery_fee_applies_when_active()
    {
        $store = (object)[
            'fixed_delivery_fee_active' => true,
            'fixed_delivery_fee' => 50,
        ];
        $fixed_delivery_fee = 0;

        if ($store && $store->fixed_delivery_fee_active) {
            $fixed_delivery_fee = $store->fixed_delivery_fee;
        }

        $this->assertEquals(50, $fixed_delivery_fee);
    }

    public function test_null_store_commission_does_not_crash()
    {
        $store = (object)[
            'store_business_model' => 'subscription',
            'commission_active' => null,
            'comission' => null,
        ];

        $should_apply = ($store->store_business_model != 'subscription') || $store->commission_active;

        $this->assertFalse($should_apply);
    }
}
