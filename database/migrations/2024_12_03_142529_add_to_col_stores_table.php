<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddToColStoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('stores')) {
            Schema::table('stores', function (Blueprint $table) {
            $table->json('pickup_zone_id')->nullable();
            $table->text('comment')->nullable();
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
        if (Schema::hasTable('stores')) {
            Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn('pickup_zone_id');
            $table->dropColumn('comment');
            });
        }
    }
}
