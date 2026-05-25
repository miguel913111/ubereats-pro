<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeliveryBatchesTable extends Migration
{
    public function up()
    {
        Schema::create('delivery_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_man_id')->constrained()->onDelete('cascade');
            $table->foreignId('zone_id')->nullable()->constrained()->onDelete('set null');
            $table->string('status', 50)->default('pending'); // pending, active, completed, cancelled
            $table->decimal('total_distance_km', 10, 2)->default(0);
            $table->decimal('estimated_duration_min', 10, 2)->default(0);
            $table->integer('total_orders')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['delivery_man_id', 'status']);
            $table->index(['zone_id', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('delivery_batches');
    }
}
