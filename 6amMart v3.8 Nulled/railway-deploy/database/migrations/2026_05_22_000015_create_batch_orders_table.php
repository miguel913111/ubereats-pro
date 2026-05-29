<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBatchOrdersTable extends Migration
{
    public function up()
    {
        Schema::create('batch_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('delivery_batches')->onDelete('cascade');
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->integer('delivery_sequence')->default(1); // 1st, 2nd, 3rd stop
            $table->decimal('distance_from_prev_km', 10, 2)->default(0);
            $table->decimal('estimated_time_min', 10, 2)->default(0);
            $table->timestamp('picked_up_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();

            $table->unique(['batch_id', 'order_id']);
            $table->index(['batch_id', 'delivery_sequence']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('batch_orders');
    }
}
