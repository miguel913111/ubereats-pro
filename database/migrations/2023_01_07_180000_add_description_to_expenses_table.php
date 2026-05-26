<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDescriptionToExpensesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('expenses', function (Blueprint $table) {
            if (!Schema::hasColumn('expenses', 'description')) {
                $table->text('description')->nullable();
            }
            if (!Schema::hasColumn('expenses', 'order_id')) {
                $table->foreignId('order_id')->nullable()->change();
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
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn('description');
            if (!Schema::hasColumn('expenses', 'order_id')) {
                $table->text('order_id')->nullable()->change();
            }
        });
    }
}
