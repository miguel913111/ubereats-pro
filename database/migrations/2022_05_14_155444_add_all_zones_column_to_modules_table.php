<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAllZonesColumnToModulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('modules')) {
            Schema::table('modules', function (Blueprint $table) {
            if (!Schema::hasColumn('modules', 'all_zone_service')) {
                $table->boolean('all_zone_service')->default(false);
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
        if (Schema::hasTable('modules')) {
            Schema::table('modules', function (Blueprint $table) {
            $table->dropColumn('all_zone_service');
            });
        }
    }
}
