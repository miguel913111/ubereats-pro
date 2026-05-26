<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('cash_back_id')->nullable();
            $table->double('extra_packaging_amount',23, 3)->default(0);
            $table->double('ref_bonus_amount',23, 3)->default(0);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('cash_back_id');
            $table->dropColumn('extra_packaging_amount');
            $table->dropColumn('ref_bonus_amount');
            });
        }
    }
};
