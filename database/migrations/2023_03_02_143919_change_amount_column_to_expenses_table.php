<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeAmountColumnToExpensesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('expenses')) {
            Schema::table('expenses', function (Blueprint $table) {
            if (!Schema::hasColumn('expenses', 'amount')) {
                $table->decimal('amount',23, 3)->default(0)->change();
            }
            if (!Schema::hasColumn('expenses', 'delivery_man_id')) {
                $table->foreignId('delivery_man_id')->nullable();
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
        if (Schema::hasTable('expenses')) {
            Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn('delivery_man_id');
            });
        }
    }
}
