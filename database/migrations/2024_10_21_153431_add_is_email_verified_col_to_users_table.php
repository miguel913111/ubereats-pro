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
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasTable('users') && !Schema::hasColumn('users', 'is_email_verified')) {
                $table->boolean('is_email_verified')->default(0);
            }
            if (Schema::hasTable('users') && !Schema::hasColumn('users', 'is_from_pos')) {
                $table->boolean('is_from_pos')->default(0);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_email_verified');
            $table->dropColumn('is_from_pos');
        });
    }
};
