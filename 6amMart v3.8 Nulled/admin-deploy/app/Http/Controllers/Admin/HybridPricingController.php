<?php

namespace App\Http\Controllers\Admin;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Models\BusinessSetting;
use App\Models\DMVehicle;
use App\Models\Store;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;

class HybridPricingController extends Controller
{
    /**
     * Página de configuração de modelos híbridos
     */
    public function index()
    {
        if (!Helpers::module_permission_check('hybrid_pricing')) {
            Toastr::error(translate('messages.access_denied'));
            return back();
        }
        $stores = Store::with('store_sub')->latest()->paginate(25);
        return view('admin-views.hybrid-pricing.index', compact('stores'));
    }

    /**
     * Atualizar modelos de monetização de uma loja
     */
    public function updateStoreModels(Request $request, $id)
    {
        if (!Helpers::module_permission_check('hybrid_pricing')) {
            Toastr::error(translate('messages.access_denied'));
            return back();
        }
        $store = Store::findOrFail($id);

        $store->commission_active = $request->has('commission_active') ? 1 : 0;
        $store->subscription_active = $request->has('subscription_active') ? 1 : 0;
        $store->fixed_delivery_fee_active = $request->has('fixed_delivery_fee_active') ? 1 : 0;
        $store->fixed_delivery_fee = $request->fixed_delivery_fee ?? 0;
        $store->comission = $request->comission ?? $store->comission;
        $store->save();

        Toastr::success(translate('messages.Pricing models updated successfully'));
        return redirect()->back();
    }

    /**
     * Configurar taxas de entregador
     */
    public function updateDriverRates(Request $request, $id)
    {
        if (!Helpers::module_permission_check('hybrid_pricing')) {
            Toastr::error(translate('messages.access_denied'));
            return back();
        }
        $store = Store::findOrFail($id);

        $store->driver_per_km_charge = $request->driver_per_km_charge ?? 0;
        $store->driver_fixed_charge = $request->driver_fixed_charge ?? 0;
        $store->minimum_shipping_charge = $request->minimum_shipping_charge ?? $store->minimum_shipping_charge;
        $store->per_km_shipping_charge = $request->per_km_shipping_charge ?? $store->per_km_shipping_charge;
        $store->maximum_shipping_charge = $request->maximum_shipping_charge ?? $store->maximum_shipping_charge;
        $store->save();

        Toastr::success(translate('messages.Driver rates updated successfully'));
        return redirect()->back();
    }

    /**
     * Página de configuração de tarifas de motorista (Ride-Share)
     */
    public function riderFares()
    {
        if (!Helpers::module_permission_check('hybrid_pricing')) {
            Toastr::error(translate('messages.access_denied'));
            return back();
        }
        $vehicles = DMVehicle::latest()->paginate(25);
        return view('admin-views.hybrid-pricing.rider-fares', compact('vehicles'));
    }

    /**
     * Atualizar tarifas de motorista
     */
    public function updateRiderFare(Request $request, $id)
    {
        if (!Helpers::module_permission_check('hybrid_pricing')) {
            Toastr::error(translate('messages.access_denied'));
            return back();
        }
        $vehicle = DMVehicle::findOrFail($id);

        $vehicle->rider_base_fare = $request->rider_base_fare ?? 0;
        $vehicle->rider_per_km_fare = $request->rider_per_km_fare ?? 0;
        $vehicle->rider_fixed_fee = $request->rider_fixed_fee ?? 0;
        $vehicle->rider_minimum_fare = $request->rider_minimum_fare ?? 0;
        $vehicle->save();

        Toastr::success(translate('messages.Rider fares updated successfully'));
        return redirect()->back();
    }

    /**
     * Configurações globais de modelo de negócio
     */
    public function globalSettings()
    {
        if (!Helpers::module_permission_check('hybrid_pricing')) {
            Toastr::error(translate('messages.access_denied'));
            return back();
        }
        $commissionModel = BusinessSetting::where('key', 'commission_business_model')->first()?->value ?? 1;
        $subscriptionModel = BusinessSetting::where('key', 'subscription_business_model')->first()?->value ?? 0;
        $fixedFeeModel = BusinessSetting::where('key', 'fixed_fee_business_model')->first()?->value ?? 0;

        return view('admin-views.hybrid-pricing.global-settings', compact('commissionModel', 'subscriptionModel', 'fixedFeeModel'));
    }

    /**
     * Atualizar configurações globais
     */
    public function updateGlobalSettings(Request $request)
    {
        if (!Helpers::module_permission_check('hybrid_pricing')) {
            Toastr::error(translate('messages.access_denied'));
            return back();
        }
        BusinessSetting::updateOrCreate(['key' => 'commission_business_model'], ['value' => $request->has('commission_business_model') ? 1 : 0]);
        BusinessSetting::updateOrCreate(['key' => 'subscription_business_model'], ['value' => $request->has('subscription_business_model') ? 1 : 0]);
        BusinessSetting::updateOrCreate(['key' => 'fixed_fee_business_model'], ['value' => $request->has('fixed_fee_business_model') ? 1 : 0]);

        Toastr::success(translate('messages.Global settings updated successfully'));
        return redirect()->back();
    }
}
