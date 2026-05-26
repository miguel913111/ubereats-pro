<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRiderFaresToDmVehicles extends Migration
{
    public function up()
    {
        Schema::table('d_m_vehicles', function (Blueprint $table) {
            if (Schema::hasTable('d_m_vehicles') && !Schema::hasColumn('d_m_vehicles', 'rider_base_fare')) {
                $table->decimal('rider_base_fare', 24, 3)->default(0);
            }
            if (Schema::hasTable('d_m_vehicles') && !Schema::hasColumn('d_m_vehicles', 'rider_per_km_fare')) {
                $table->decimal('rider_per_km_fare', 24, 3)->default(0);
            }
            if (Schema::hasTable('d_m_vehicles') && !Schema::hasColumn('d_m_vehicles', 'rider_fixed_fee')) {
                $table->decimal('rider_fixed_fee', 24, 3)->default(0);
            }
            if (Schema::hasTable('d_m_vehicles') && !Schema::hasColumn('d_m_vehicles', 'rider_minimum_fare')) {
                $table->decimal('rider_minimum_fare', 24, 3)->default(0);
            }
        });
    }

    public function down()
    {
        Schema::table('d_m_vehicles', function (Blueprint $table) {
            $table->dropColumn(['rider_base_fare', 'rider_per_km_fare', 'rider_fixed_fee', 'rider_minimum_fare']);
        });
    }
}
