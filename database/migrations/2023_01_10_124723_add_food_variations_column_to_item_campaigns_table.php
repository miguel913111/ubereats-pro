<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFoodVariationsColumnToItemCampaignsTable extends Migration
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
            $table->text('food_variations')->nullable();
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
            $table->dropColumn('food_variations');
            });
        }
    }
}
