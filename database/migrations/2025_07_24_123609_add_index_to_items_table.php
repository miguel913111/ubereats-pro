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
        if (Schema::hasTable('items')) {
            Schema::table('items', function (Blueprint $table) {
            if (!Schema::hasColumn('items', 'category_id')) {
                $table->index('category_id');
            }
            if (!Schema::hasColumn('items', 'store_id')) {
                $table->index('store_id');
            }
            if (!Schema::hasColumn('items', 'name')) {
                $table->index('name');
            }
            if (!Schema::hasColumn('items', 'slug')) {
                $table->index('slug');
            }
            if (!Schema::hasColumn('items', 'price')) {
                $table->index('price');
            }
            if (!Schema::hasColumn('items', 'created_at')) {
                $table->index('created_at');
            }
            if (!Schema::hasColumn('items', 'order_count')) {
                $table->index('order_count');
            }
            if (!Schema::hasColumn('items', 'avg_rating')) {
                $table->index('avg_rating');
            }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('items')) {
            Schema::table('items', function (Blueprint $table) {
            if (!Schema::hasColumn('items', 'category_id')) {
                $table->dropIndex('category_id');
            }
            if (!Schema::hasColumn('items', 'store_id')) {
                $table->dropIndex('store_id');
            }
            if (!Schema::hasColumn('items', 'name')) {
                $table->dropIndex('name');
            }
            if (!Schema::hasColumn('items', 'slug')) {
                $table->dropIndex('slug');
            }
            if (!Schema::hasColumn('items', 'price')) {
                $table->dropIndex('price');
            }
            if (!Schema::hasColumn('items', 'created_at')) {
                $table->dropIndex('created_at');
            }
            if (!Schema::hasColumn('items', 'order_count')) {
                $table->dropIndex('order_count');
            }
            if (!Schema::hasColumn('items', 'avg_rating')) {
                $table->dropIndex('avg_rating');
            }
            });
        }
    }
};
