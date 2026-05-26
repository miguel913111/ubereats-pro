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
        if (Schema::hasTable('zones')) {
            Schema::table('zones', function (Blueprint $table) {
            if (!Schema::hasColumn('zones', 'increased_delivery_fee')) {
                $table->double('increased_delivery_fee',8,2)->default('0');
            }
            if (!Schema::hasColumn('zones', 'increased_delivery_fee_status')) {
                $table->boolean('increased_delivery_fee_status')->default('0');
            }
            if (!Schema::hasColumn('zones', 'increase_delivery_charge_message')) {
                $table->string('increase_delivery_charge_message')->nullable();
            }
            if (!Schema::hasColumn('zones', 'offline_payment')) {
                $table->boolean('offline_payment')->default(false);
            }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('zones')) {
            Schema::table('zones', function (Blueprint $table) {
            $table->dropColumn('increased_delivery_fee_status');
            $table->dropColumn('increased_delivery_fee');
            $table->dropColumn('increase_delivery_charge_message');
            $table->dropColumn('offline_payment');
            });
        }
    }
};
