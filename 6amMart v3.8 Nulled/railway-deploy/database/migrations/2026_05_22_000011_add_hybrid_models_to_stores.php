<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHybridModelsToStores extends Migration
{
    public function up()
    {
        Schema::table('stores', function (Blueprint $table) {
            // Flags para ativar/desativar cada modelo
            $table->boolean('commission_active')->default(1)->after('comission');
            $table->boolean('subscription_active')->default(0)->after('commission_active');
            $table->boolean('fixed_delivery_fee_active')->default(0)->after('subscription_active');
            
            // Taxa fixa por entrega (valor que loja paga à plataforma por pedido)
            $table->decimal('fixed_delivery_fee', 24, 3)->default(0)->after('fixed_delivery_fee_active');
            
            // Taxas para entregador (delivery man)
            $table->decimal('driver_per_km_charge', 24, 3)->default(0)->after('per_km_shipping_charge');
            $table->decimal('driver_fixed_charge', 24, 3)->default(0)->after('driver_per_km_charge');
        });
    }

    public function down()
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn([
                'commission_active',
                'subscription_active',
                'fixed_delivery_fee_active',
                'fixed_delivery_fee',
                'driver_per_km_charge',
                'driver_fixed_charge',
            ]);
        });
    }
}
