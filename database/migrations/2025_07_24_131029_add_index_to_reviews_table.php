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
        if (Schema::hasTable('reviews')) {
            Schema::table('reviews', function (Blueprint $table) {
            if (!Schema::hasColumn('reviews', 'item_id')) {
                $table->index('item_id');
            }
            if (!Schema::hasColumn('reviews', 'item_campaign_id')) {
                $table->index('item_campaign_id');
            }
            if (!Schema::hasColumn('reviews', 'user_id')) {
                $table->index('user_id');
            }
            if (!Schema::hasColumn('reviews', 'order_id')) {
                $table->index('order_id');
            }
            if (!Schema::hasColumn('reviews', 'store_id')) {
                $table->index('store_id');
            }
            if (!Schema::hasColumn('reviews', 'review_id')) {
                $table->index('review_id');
            }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('reviews')) {
            Schema::table('reviews', function (Blueprint $table) {
            if (!Schema::hasColumn('reviews', 'item_id')) {
                $table->dropIndex('item_id');
            }
            if (!Schema::hasColumn('reviews', 'item_campaim_id')) {
                $table->dropIndex('item_campaim_id');
            }
            if (!Schema::hasColumn('reviews', 'user_id')) {
                $table->dropIndex('user_id');
            }
            if (!Schema::hasColumn('reviews', 'order_id')) {
                $table->dropIndex('order_id');
            }
            if (!Schema::hasColumn('reviews', 'store_id')) {
                $table->dropIndex('store_id');
            }
            if (!Schema::hasColumn('reviews', 'review_id')) {
                $table->dropIndex('review_id');
            }
            });
        }
    }
};
