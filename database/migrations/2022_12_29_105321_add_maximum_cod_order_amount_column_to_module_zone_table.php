<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMaximumCodOrderAmountColumnToModuleZoneTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('module_zone')) {
            Schema::table('module_zone', function (Blueprint $table) {
            if (!Schema::hasColumn('module_zone', 'maximum_cod_order_amount')) {
                $table->double('maximum_cod_order_amount', 23, 2)->nullable();
            }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('module_zone')) {
            Schema::table('module_zone', function (Blueprint $table) {
            $table->dropColumn('maximum_cod_order_amount');
            });
        }
    }
}
