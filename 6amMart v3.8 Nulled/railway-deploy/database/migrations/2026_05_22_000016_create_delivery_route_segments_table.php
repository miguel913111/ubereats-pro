<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeliveryRouteSegmentsTable extends Migration
{
    public function up()
    {
        Schema::create('delivery_route_segments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('delivery_batches')->onDelete('cascade');
            $table->integer('sequence')->default(1);
            $table->decimal('from_lat', 12, 8)->nullable();
            $table->decimal('from_lng', 12, 8)->nullable();
            $table->decimal('to_lat', 12, 8)->nullable();
            $table->decimal('to_lng', 12, 8)->nullable();
            $table->string('from_type', 50)->nullable(); // store, customer
            $table->string('to_type', 50)->nullable(); // store, customer
            $table->foreignId('order_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('distance_km', 10, 2)->default(0);
            $table->decimal('estimated_minutes', 10, 2)->default(0);
            $table->timestamps();

            $table->index(['batch_id', 'sequence']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('delivery_route_segments');
    }
}
