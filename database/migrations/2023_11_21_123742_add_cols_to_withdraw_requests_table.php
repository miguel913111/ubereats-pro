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
        Schema::table('withdraw_requests', function (Blueprint $table) {
            if (Schema::hasTable('withdraw_requests') && !Schema::hasColumn('withdraw_requests', 'delivery_man_id')) {
                $table->foreignId('delivery_man_id')->nullable();
            }
            if (Schema::hasTable('withdraw_requests') && !Schema::hasColumn('withdraw_requests', 'withdrawal_method_id')) {
                $table->foreignId('withdrawal_method_id')->nullable();
            }
            if (Schema::hasTable('withdraw_requests') && !Schema::hasColumn('withdraw_requests', 'withdrawal_method_fields')) {
                $table->json('withdrawal_method_fields')->nullable();
            }
            if (Schema::hasTable('withdraw_requests') && !Schema::hasColumn('withdraw_requests', 'vendor_id')) {
                $table->foreignId('vendor_id')->nullable()->change();
            }
            if (Schema::hasTable('withdraw_requests') && !Schema::hasColumn('withdraw_requests', 'type')) {
                $table->string('type',20)->default('manual');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('withdraw_requests', function (Blueprint $table) {
            $table->dropColumn('delivery_man_id');
            $table->dropColumn('withdrawal_method_fields');
            $table->dropColumn('withdrawal_method_id');
            $table->dropColumn('type');
        });
    }
};
