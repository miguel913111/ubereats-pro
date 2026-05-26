<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSlugToItemCampaignsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('item_campaigns')) {
            Schema::table('item_campaigns', function (Blueprint $table) {
            $table->string('slug')->nullable();
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
        if (Schema::hasTable('item_campaigns')) {
            Schema::table('item_campaigns', function (Blueprint $table) {
            $table->dropColumn('slug');
            });
        }
    }
}
