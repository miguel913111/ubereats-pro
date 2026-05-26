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
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'order_attachment')) {
                $table->text('order_attachment')->nullable()->change();
            }
            if (!Schema::hasColumn('orders', 'order_proof')) {
                $table->text('order_proof')->nullable()->change();
            }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'order_attachment')) {
                $table->string('order_attachment',191)->nullable()->change();
            }
            if (!Schema::hasColumn('orders', 'order_proof')) {
                $table->string('order_proof',255)->nullable()->change();
            }
            });
        }
    }
};
