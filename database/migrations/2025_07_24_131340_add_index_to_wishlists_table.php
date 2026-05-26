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
        if (Schema::hasTable('wishlists')) {
            Schema::table('wishlists', function (Blueprint $table) {
            if (!Schema::hasColumn('wishlists', 'user_id')) {
                $table->index('user_id');
            }
            if (!Schema::hasColumn('wishlists', 'item_id')) {
                $table->index('item_id');
            }
            if (!Schema::hasColumn('wishlists', 'store_id')) {
                $table->index('store_id');
            }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('wishlists')) {
            Schema::table('wishlists', function (Blueprint $table) {
            if (!Schema::hasColumn('wishlists', 'user_id')) {
                $table->dropIndex('user_id');
            }
            if (!Schema::hasColumn('wishlists', 'item_id')) {
                $table->dropIndex('item_id');
            }
            if (!Schema::hasColumn('wishlists', 'store_id')) {
                $table->dropIndex('store_id');
            }
            });
        }
    }
};
