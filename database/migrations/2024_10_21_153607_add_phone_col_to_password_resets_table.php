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
        Schema::table('password_resets', function (Blueprint $table) {
            if (Schema::hasTable('password_resets') && !Schema::hasColumn('password_resets', 'phone')) {
                $table->string('phone',50)->nullable();
            }
            if (Schema::hasTable('password_resets') && !Schema::hasColumn('password_resets', 'email')) {
                $table->string('email')->nullable()->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('password_resets', function (Blueprint $table) {
            $table->dropColumn('phone');
            if (Schema::hasTable('password_resets') && !Schema::hasColumn('password_resets', 'email')) {
                $table->string('email')->nullable(false)->change();
            }
        });
    }
};
