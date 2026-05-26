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
        if (Schema::hasTable('ecommerce_item_details')) {
            Schema::table('ecommerce_item_details', function (Blueprint $table) {
            if (!Schema::hasColumn('ecommerce_item_details', 'temp_product_id')) {
                $table->foreignId('temp_product_id')->nullable();
            }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('ecommerce_item_details')) {
            Schema::table('ecommerce_item_details', function (Blueprint $table) {
            $table->dropColumn('temp_product_id');
            });
        }
    }
};
