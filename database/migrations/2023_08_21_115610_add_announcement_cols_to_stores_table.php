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
        if (Schema::hasTable('stores')) {
            Schema::table('stores', function (Blueprint $table) {
            if (!Schema::hasColumn('stores', 'announcement')) {
                $table->boolean('announcement')->default(0);
            }
            if (!Schema::hasColumn('stores', 'announcement_message')) {
                $table->string('announcement_message')->nullable();
            }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('stores')) {
            Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn('announcement');
            $table->dropColumn('announcement_message');
            });
        }
    }
};
