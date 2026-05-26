<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsLoggedColumnToVendorEmployeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('vendor_employees')) {
            Schema::table('vendor_employees', function (Blueprint $table) {
            $table->boolean('is_logged_in')->default(true);
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
        if (Schema::hasTable('vendor_employees')) {
            Schema::table('vendor_employees', function (Blueprint $table) {
            $table->dropColumn('is_logged_in');
            });
        }
    }
}
