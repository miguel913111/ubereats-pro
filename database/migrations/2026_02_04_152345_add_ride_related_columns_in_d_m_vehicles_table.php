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
        Schema::table('d_m_vehicles', function (Blueprint $table) {
            if (!Schema::hasColumn('d_m_vehicles', 'name')) {
                $table->string('name')->nullable();
            }
            if (!Schema::hasColumn('d_m_vehicles', 'description')) {
                $table->text('description')->nullable();
            }
            if (!Schema::hasColumn('d_m_vehicles', 'image')) {
                $table->string('image')->nullable();
            }
            if (!Schema::hasColumn('d_m_vehicles', 'is_delivery')) {
                $table->boolean('is_delivery')->default(1);
            }
            if (!Schema::hasColumn('d_m_vehicles', 'is_ride')) {
                $table->boolean('is_ride')->default(0);
            }
            if (!Schema::hasColumn('d_m_vehicles', 'starting_coverage_area')) {
                $table->double('starting_coverage_area',16,2)->default(0)->change();
            }
            if (!Schema::hasColumn('d_m_vehicles', 'maximum_coverage_area')) {
                $table->double('maximum_coverage_area',16,2)->default(0)->change();
            }
            if (!Schema::hasColumn('d_m_vehicles', 'extra_charges')) {
                $table->double('extra_charges',16,2)->default(0)->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('d_m_vehicles', function (Blueprint $table) {
            $table->dropColumn('name');
            $table->dropColumn('description');
            $table->dropColumn('image');
            $table->dropColumn('is_delivery');
            $table->dropColumn('is_ride');
            if (!Schema::hasColumn('d_m_vehicles', 'starting_coverage_area')) {
                $table->double('starting_coverage_area',16,2)->change();
            }
            if (!Schema::hasColumn('d_m_vehicles', 'maximum_coverage_area')) {
                $table->double('maximum_coverage_area',16,2)->change();
            }
            if (!Schema::hasColumn('d_m_vehicles', 'extra_charges')) {
                $table->double('extra_charges',16,2)->change();
            }
        });
    }
};
