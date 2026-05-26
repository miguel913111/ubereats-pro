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
        if (Schema::hasTable('account_transactions')) {
            Schema::table('account_transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('account_transactions', 'type')) {
                $table->string('type',20)->default('collected');
            }
            if (!Schema::hasColumn('account_transactions', 'created_by')) {
                $table->string('created_by',20)->default('admin');
            }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('account_transactions')) {
            Schema::table('account_transactions', function (Blueprint $table) {
            $table->dropColumn('type');
            $table->dropColumn('created_by');
            });
        }
    }
};
