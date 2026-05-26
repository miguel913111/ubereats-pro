<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTempTokenColumnToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasTable('users') && !Schema::hasColumn('users', 'temp_token')) {
                $table->string('temp_token')->nullable();
            }
            if (Schema::hasTable('users') && !Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->nullable()->change();
            }
            if (Schema::hasTable('users') && !Schema::hasColumn('users', 'password')) {
                $table->string('password',100)->nullable()->change();
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
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('temp_token');
            if (Schema::hasTable('users') && !Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->change();
            }
            if (Schema::hasTable('users') && !Schema::hasColumn('users', 'password')) {
                $table->string('password',100)->change();
            }
        });
    }
}
