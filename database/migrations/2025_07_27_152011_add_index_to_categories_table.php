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
        Schema::table('categories', function (Blueprint $table) {
            if (Schema::hasTable('categories') && !Schema::hasColumn('categories', 'parent_id')) {
                $table->index('parent_id');
            }
            if (Schema::hasTable('categories') && !Schema::hasColumn('categories', 'name')) {
                $table->index('name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            if (Schema::hasTable('categories') && !Schema::hasColumn('categories', 'parent_id')) {
                $table->dropIndex('parent_id');
            }
            if (Schema::hasTable('categories') && !Schema::hasColumn('categories', 'name')) {
                $table->dropIndex('name');
            }
        });
    }
};
