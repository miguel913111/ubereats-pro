<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDineInToStoresTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('stores')) {
            Schema::table('stores', function (Blueprint $table) {
            $table->boolean('dine_in')->default(0)->after('take_away');
            $table->boolean('table_reservation')->default(0)->after('dine_in');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('stores')) {
            Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn(['dine_in', 'table_reservation']);
            });
        }
    }
}
