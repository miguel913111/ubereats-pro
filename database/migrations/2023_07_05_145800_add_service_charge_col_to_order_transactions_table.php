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
            $table->double('additional_charge',23, 3)->default(0);
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
            $table->dropColumn('additional_charge');
            });
        }
    }
};
