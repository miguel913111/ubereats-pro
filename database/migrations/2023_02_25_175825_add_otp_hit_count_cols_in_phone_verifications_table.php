<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOtpHitCountColsInPhoneVerificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('phone_verifications', function (Blueprint $table) {
            if (Schema::hasTable('phone_verifications') && !Schema::hasColumn('phone_verifications', 'otp_hit_count')) {
                $table->tinyInteger('otp_hit_count')->default('0');
            }
            if (Schema::hasTable('phone_verifications') && !Schema::hasColumn('phone_verifications', 'is_blocked')) {
                $table->boolean('is_blocked')->default('0');
            }
            if (Schema::hasTable('phone_verifications') && !Schema::hasColumn('phone_verifications', 'is_temp_blocked')) {
                $table->boolean('is_temp_blocked')->default('0');
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
        Schema::table('phone_verifications', function (Blueprint $table) {
            $table->dropColumn('otp_hit_count');
            $table->dropColumn('is_blocked');
            $table->dropColumn('is_temp_blocked');
        });
    }
}