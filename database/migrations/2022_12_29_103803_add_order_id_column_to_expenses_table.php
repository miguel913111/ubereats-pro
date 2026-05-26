<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOrderIdColumnToExpensesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('expenses', function (Blueprint $table) {
            if (Schema::hasTable('expenses') && !Schema::hasColumn('expenses', 'description')) {
                $table->renameColumn('description','order_id');
            }
            // $table->foreignId('order_id')->nullable()->change();
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
            if (Schema::hasTable('expenses') && !Schema::hasColumn('expenses', 'order_id')) {
                $table->renameColumn('order_id','description');
            }
        });
    }
}
