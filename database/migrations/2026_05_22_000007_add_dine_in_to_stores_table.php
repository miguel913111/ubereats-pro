<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDineInToStoresTable extends Migration
{
    public function up()
    {
        Schema::table('stores', function (Blueprint $table) {
            if (!Schema::hasColumn('stores', 'dine_in')) {
                $table->boolean('dine_in')->default(0)->after('take_away');
            }
            if (!Schema::hasColumn('stores', 'table_reservation')) {
                $table->boolean('table_reservation')->default(0)->after('dine_in');
            }
        });
    }

    public function down()
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn(['dine_in', 'table_reservation']);
        });
    }
}
