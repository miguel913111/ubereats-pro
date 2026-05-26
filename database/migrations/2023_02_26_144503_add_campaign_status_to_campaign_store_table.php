<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCampaignStatusToCampaignStoreTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('campaign_store')) {
            Schema::table('campaign_store', function (Blueprint $table) {
            $table->string('campaign_status',10)->default('pending')->nullable();
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
        if (Schema::hasTable('campaign_store')) {
            Schema::table('campaign_store', function (Blueprint $table) {
            $table->dropColumn('campaign_status');
            });
        }
    }
}
