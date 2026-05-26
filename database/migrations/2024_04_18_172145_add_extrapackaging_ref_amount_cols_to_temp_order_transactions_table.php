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
        if (Schema::hasTable('order_transactions')) {
            Schema::table('order_transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('order_transactions', 'extra_packaging_amount')) {
                $table->double('extra_packaging_amount',23, 3)->default(0);
            }
            if (!Schema::hasColumn('order_transactions', 'ref_bonus_amount')) {
                $table->double('ref_bonus_amount',23, 3)->default(0);
            }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('order_transactions')) {
            Schema::table('order_transactions', function (Blueprint $table) {
            $table->dropColumn('extra_packaging_amount');
            $table->dropColumn('ref_bonus_amount');
            });
        }
    }
};
