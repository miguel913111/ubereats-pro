<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToCustomerAddressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customer_addresses', function (Blueprint $table) {
            if (!Schema::hasColumn('customer_addresses', 'floor')) {
                $table->string('floor')->nullable();
            }
            if (!Schema::hasColumn('customer_addresses', 'road')) {
                $table->string('road')->nullable();
            }
            if (!Schema::hasColumn('customer_addresses', 'house')) {
                $table->string('house')->nullable();
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
        Schema::table('customer_addresses', function (Blueprint $table) {
            $table->dropColumn('floor');
            $table->dropColumn('road');
            $table->dropColumn('house');
        });
    }
}
