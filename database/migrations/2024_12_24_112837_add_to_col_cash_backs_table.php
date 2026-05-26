<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddToColCashBacksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('cash_backs')) {
            Schema::table('cash_backs', function (Blueprint $table) {
            if (!Schema::hasColumn('cash_backs', 'is_rental')) {
                $table->boolean('is_rental')->default(false);
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
        if (Schema::hasTable('cash_backs')) {
            Schema::table('cash_backs', function (Blueprint $table) {
            $table->dropColumn('is_rental');

            });
        }
    }
}
