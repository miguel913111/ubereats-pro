<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPaymentMethodColumnsToZonesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('zones', function (Blueprint $table) {
            if (Schema::hasTable('zones') && !Schema::hasColumn('zones', 'cash_on_delivery')) {
                $table->boolean('cash_on_delivery')->default(false);
            }
            if (Schema::hasTable('zones') && !Schema::hasColumn('zones', 'digital_payment')) {
                $table->boolean('digital_payment')->default(false);
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('zones', function (Blueprint $table) {
            $table->dropColumn('cash_on_delivery');
            $table->dropColumn('digital_payment');
        });
    }
}
