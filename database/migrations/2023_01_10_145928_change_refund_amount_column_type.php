<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeRefundAmountColumnType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('refunds')) {
            Schema::table('refunds', function (Blueprint $table) {
            $table->decimal('refund_amount',23,3)->default(0)->change();
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
        if (Schema::hasTable('refunds')) {
            Schema::table('refunds', function (Blueprint $table) {
            $table->decimal('refund_amount')->default(0)->change();
            });
        }
    }
}
