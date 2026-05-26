<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDefaultLinkColumnToBannersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('banners')) {
            Schema::table('banners', function (Blueprint $table) {
            if (!Schema::hasColumn('banners', 'default_link')) {
                $table->string('default_link')->nullable();
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
        if (Schema::hasTable('banners')) {
            Schema::table('banners', function (Blueprint $table) {
            $table->dropColumn('default_link');
            });
        }
    }
}
