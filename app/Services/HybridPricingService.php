<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Store;

class HybridPricingService
{
    /**
     * Calcular todos os valores financeiros de um pedido
     * considerando múltiplos modelos de monetização ativos
     */
    public static function calculateOrderPricing(Order $order, Store $store): array
    {
        $result = [
            'commission_amount' => 0,
            'subscription_amount' => 0,
            'fixed_delivery_fee' => 0,
            'driver_km_charge' => 0,
            'driver_fixed_charge' => 0,
            'platform_earnings' => 0,
            'store_earnings' => 0,
            'delivery_man_earnings' => 0,
        ];

        $orderAmount = $order->order_amount;
        $deliveryCharge = $order->delivery_charge ?? 0;

        // 1. COMISSÃO (se ativa)
        if ($store->commission_active && $store->comission > 0) {
            $result['commission_amount'] = ($orderAmount / 100) * $store->comission;
        }

        // 2. ASSINATURA (se ativa)
        if ($store->subscription_active && $store->store_sub) {
            // A assinatura já foi paga antecipadamente, não cobra por pedido
            // Mas registra como zero no pedido individual
            $result['subscription_amount'] = 0;
        }

        // 3. TAXA FIXA POR ENTREGA (se ativa)
        if ($store->fixed_delivery_fee_active && $store->fixed_delivery_fee > 0) {
            $result['fixed_delivery_fee'] = $store->fixed_delivery_fee;
        }

        // 4. TAXA DO ENTREGADOR POR KM (se configurada)
        if ($store->driver_per_km_charge > 0 && $order->distance > 0) {
            $result['driver_km_charge'] = $order->distance * $store->driver_per_km_charge;
        }

        // 5. TAXA FIXA DO ENTREGADOR (se configurada)
        if ($store->driver_fixed_charge > 0) {
            $result['driver_fixed_charge'] = $store->driver_fixed_charge;
        }

        // Total de ganhos do entregador
        $result['delivery_man_earnings'] = $result['driver_km_charge'] + $result['driver_fixed_charge'] + $deliveryCharge;

        // Total que a plataforma ganha
        $result['platform_earnings'] = $result['commission_amount'] + $result['fixed_delivery_fee'];

        // Total que a loja ganha
        $result['store_earnings'] = $orderAmount - $result['commission_amount'] - $result['fixed_delivery_fee'];

        return $result;
    }

    /**
     * Calcular tarifa de ride-share/motorista
     */
    public static function calculateRideFare(float $distance, array $vehicleConfig): array
    {
        $baseFare = $vehicleConfig['rider_base_fare'] ?? 0;
        $perKmFare = $vehicleConfig['rider_per_km_fare'] ?? 0;
        $fixedFee = $vehicleConfig['rider_fixed_fee'] ?? 0;
        $minimumFare = $vehicleConfig['rider_minimum_fare'] ?? 0;

        $distanceFare = $distance * $perKmFare;
        $totalFare = $baseFare + $distanceFare + $fixedFee;

        if ($minimumFare > 0 && $totalFare < $minimumFare) {
            $totalFare = $minimumFare;
        }

        return [
            'base_fare' => $baseFare,
            'distance_fare' => $distanceFare,
            'fixed_fee' => $fixedFee,
            'minimum_fare' => $minimumFare,
            'total_fare' => $totalFare,
            'platform_earnings' => $fixedFee,
            'driver_earnings' => $baseFare + $distanceFare,
        ];
    }

    /**
     * Verificar quais modelos estão ativos para uma loja
     */
    public static function getActiveModels(Store $store): array
    {
        return [
            'commission' => (bool) $store->commission_active,
            'subscription' => (bool) $store->subscription_active,
            'fixed_delivery_fee' => (bool) $store->fixed_delivery_fee_active,
        ];
    }
}
