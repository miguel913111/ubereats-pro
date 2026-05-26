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
        Schema::table('order_transactions', function (Blueprint $table) {
            if (Schema::hasTable('order_transactions') && !Schema::hasColumn('order_transactions', 'commission_percentage')) {
                $table->double('commission_percentage',16, 3)->default(0)->nullable();
            }
            if (Schema::hasTable('order_transactions') && !Schema::hasColumn('order_transactions', 'is_subscribed')) {
                $table->boolean('is_subscribed')->default(0);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_transactions', function (Blueprint $table) {
            $table->dropColumn('commission_percentage');
            $table->dropColumn('is_subscribed');
        });
    }
};
