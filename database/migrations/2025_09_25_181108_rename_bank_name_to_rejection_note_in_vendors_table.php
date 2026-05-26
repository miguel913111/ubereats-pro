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
        if (Schema::hasTable('vendors')) {
            Schema::table('vendors', function (Blueprint $table) {
             if (!Schema::hasColumn('vendors', 'bank_name')) {
                 $table->renameColumn('bank_name', 'rejection_note');
             }
            });
        }
        if (Schema::hasTable('vendors')) {
            Schema::table('vendors', function (Blueprint $table) {
            if (!Schema::hasColumn('vendors', 'rejection_note')) {
                $table->text('rejection_note')->nullable()->change();
            }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         if (Schema::hasTable('vendors')) {
             Schema::table('vendors', function (Blueprint $table) {
            if (!Schema::hasColumn('vendors', 'rejection_note')) {
                $table->string('rejection_note', 255)->change();
            }
            if (!Schema::hasColumn('vendors', 'rejection_note')) {
                $table->renameColumn('rejection_note', 'bank_name');
            }
             });
         }
    }
};
