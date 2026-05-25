<?php

namespace App\Http\Controllers\Api\V1;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Services\HybridPricingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VendorHybridPricingController extends Controller
{
    /**
     * Obter modelos de monetização atuais da loja
     */
    public function getMyPricingModels(Request $request)
    {
        $store = Store::find($request->user()->id);

        if (!$store) {
            return response()->json(['errors' => [['code' => 'not_found', 'message' => translate('messages.Store not found')]]], 404);
        }

        $activeModels = HybridPricingService::getActiveModels($store);

        return response()->json([
            'store_id' => $store->id,
            'store_business_model' => $store->store_business_model,
            'active_models' => $activeModels,
            'commission' => [
                'active' => (bool) $store->commission_active,
                'percentage' => $store->comission,
            ],
            'subscription' => [
                'active' => (bool) $store->subscription_active,
                'current_plan' => $store->store_sub?->package?->title ?? null,
                'expiry_date' => $store->store_sub?->expiry_date ?? null,
            ],
            'fixed_delivery_fee' => [
                'active' => (bool) $store->fixed_delivery_fee_active,
                'amount' => $store->fixed_delivery_fee,
            ],
            'driver_rates' => [
                'per_km_charge' => $store->driver_per_km_charge,
                'fixed_charge' => $store->driver_fixed_charge,
            ],
            'shipping_charges' => [
                'minimum' => $store->minimum_shipping_charge,
                'per_km' => $store->per_km_shipping_charge,
                'maximum' => $store->maximum_shipping_charge,
            ],
        ], 200);
    }

    /**
     * Atualizar modelos de monetização da loja
     */
    public function updatePricingModels(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'commission_active' => 'required|in:0,1',
            'subscription_active' => 'required|in:0,1',
            'fixed_delivery_fee_active' => 'required|in:0,1',
            'fixed_delivery_fee' => 'nullable|numeric|min:0',
            'comission' => 'nullable|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $store = Store::find($request->user()->id);

        if (!$store) {
            return response()->json(['errors' => [['code' => 'not_found', 'message' => translate('messages.Store not found')]]], 404);
        }

        // Verificar se pelo menos um modelo está ativo
        if (!$request->commission_active && !$request->subscription_active && !$request->fixed_delivery_fee_active) {
            return response()->json(['errors' => [['code' => 'no_model', 'message' => translate('messages.At least one pricing model must be active')]]], 403);
        }

        $store->commission_active = $request->commission_active;
        $store->subscription_active = $request->subscription_active;
        $store->fixed_delivery_fee_active = $request->fixed_delivery_fee_active;
        $store->fixed_delivery_fee = $request->fixed_delivery_fee ?? $store->fixed_delivery_fee;
        $store->comission = $request->comission ?? $store->comission;

        // Atualizar o store_business_model principal
        $activeModels = [];
        if ($store->commission_active) $activeModels[] = 'commission';
        if ($store->subscription_active) $activeModels[] = 'subscription';
        if ($store->fixed_delivery_fee_active) $activeModels[] = 'fixed_fee';

        $store->store_business_model = count($activeModels) > 1 ? 'hybrid' : ($activeModels[0] ?? 'commission');
        $store->save();

        return response()->json([
            'message' => translate('messages.Pricing models updated successfully'),
            'active_models' => $activeModels,
            'store_business_model' => $store->store_business_model,
        ], 200);
    }

    /**
     * Atualizar taxas de entregador
     */
    public function updateDriverRates(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'driver_per_km_charge' => 'nullable|numeric|min:0',
            'driver_fixed_charge' => 'nullable|numeric|min:0',
            'minimum_shipping_charge' => 'nullable|numeric|min:0',
            'per_km_shipping_charge' => 'nullable|numeric|min:0',
            'maximum_shipping_charge' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $store = Store::find($request->user()->id);

        if (!$store) {
            return response()->json(['errors' => [['code' => 'not_found', 'message' => translate('messages.Store not found')]]], 404);
        }

        $store->driver_per_km_charge = $request->driver_per_km_charge ?? $store->driver_per_km_charge;
        $store->driver_fixed_charge = $request->driver_fixed_charge ?? $store->driver_fixed_charge;
        $store->minimum_shipping_charge = $request->minimum_shipping_charge ?? $store->minimum_shipping_charge;
        $store->per_km_shipping_charge = $request->per_km_shipping_charge ?? $store->per_km_shipping_charge;
        $store->maximum_shipping_charge = $request->maximum_shipping_charge ?? $store->maximum_shipping_charge;
        $store->save();

        return response()->json([
            'message' => translate('messages.Driver rates updated successfully'),
            'driver_rates' => [
                'per_km_charge' => $store->driver_per_km_charge,
                'fixed_charge' => $store->driver_fixed_charge,
            ],
        ], 200);
    }

    /**
     * Calcular simulação de ganhos com modelo híbrido
     */
    public function simulateEarnings(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_amount' => 'required|numeric|min:0',
            'distance' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $store = Store::find($request->user()->id);

        if (!$store) {
            return response()->json(['errors' => [['code' => 'not_found', 'message' => translate('messages.Store not found')]]], 404);
        }

        $orderAmount = $request->order_amount;
        $distance = $request->distance ?? 0;

        $commissionAmount = $store->commission_active ? ($orderAmount / 100) * $store->comission : 0;
        $fixedFeeAmount = $store->fixed_delivery_fee_active ? $store->fixed_delivery_fee : 0;
        $driverKmCharge = $store->driver_per_km_charge > 0 ? $distance * $store->driver_per_km_charge : 0;
        $driverFixedCharge = $store->driver_fixed_charge > 0 ? $store->driver_fixed_charge : 0;

        $platformEarnings = $commissionAmount + $fixedFeeAmount;
        $storeEarnings = $orderAmount - $commissionAmount - $fixedFeeAmount;
        $driverEarnings = $driverKmCharge + $driverFixedCharge;

        return response()->json([
            'order_amount' => $orderAmount,
            'distance' => $distance,
            'breakdown' => [
                'commission' => [
                    'active' => (bool) $store->commission_active,
                    'percentage' => $store->comission,
                    'amount' => round($commissionAmount, 2),
                ],
                'fixed_delivery_fee' => [
                    'active' => (bool) $store->fixed_delivery_fee_active,
                    'amount' => round($fixedFeeAmount, 2),
                ],
                'subscription' => [
                    'active' => (bool) $store->subscription_active,
                    'note' => 'Already paid in advance',
                ],
                'driver_km_charge' => round($driverKmCharge, 2),
                'driver_fixed_charge' => round($driverFixedCharge, 2),
            ],
            'totals' => [
                'platform_earnings' => round($platformEarnings, 2),
                'store_earnings' => round($storeEarnings, 2),
                'driver_earnings' => round($driverEarnings, 2),
            ],
        ], 200);
    }
}
