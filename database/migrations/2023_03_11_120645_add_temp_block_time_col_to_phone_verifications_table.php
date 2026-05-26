<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTempBlockTimeColToPhoneVerificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('phone_verifications')) {
            Schema::table('phone_verifications', function (Blueprint $table) {
            if (!Schema::hasColumn('phone_verifications', 'temp_block_time')) {
                $table->timestamp('temp_block_time')->nullable();
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
        if (Schema::hasTable('phone_verifications')) {
            Schema::table('phone_verifications', function (Blueprint $table) {
            $table->dropColumn('temp_block_time');
            });
        }
    }
}
