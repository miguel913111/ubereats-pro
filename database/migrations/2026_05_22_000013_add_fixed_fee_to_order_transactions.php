<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFixedFeeToOrderTransactions extends Migration
{
    public function up()
    {
        if (Schema::hasTable('order_transactions')) {
            Schema::table('order_transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('order_transactions', 'fixed_delivery_fee')) {
                $table->decimal('fixed_delivery_fee', 24, 3)->default(0)->after('delivery_fee_comission');
            }
            if (!Schema::hasColumn('order_transactions', 'driver_km_charge')) {
                $table->decimal('driver_km_charge', 24, 3)->default(0)->after('fixed_delivery_fee');
            }
            if (!Schema::hasColumn('order_transactions', 'driver_fixed_charge')) {
                $table->decimal('driver_fixed_charge', 24, 3)->default(0)->after('driver_km_charge');
            }
            if (!Schema::hasColumn('order_transactions', 'platform_earnings')) {
                $table->decimal('platform_earnings', 24, 3)->default(0)->after('driver_fixed_charge');
            }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('order_transactions')) {
            Schema::table('order_transactions', function (Blueprint $table) {
            $table->dropColumn(['fixed_delivery_fee', 'driver_km_charge', 'driver_fixed_charge', 'platform_earnings']);
            });
        }
    }
}
