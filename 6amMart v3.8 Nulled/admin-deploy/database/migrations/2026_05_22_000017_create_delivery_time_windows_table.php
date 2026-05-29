<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeliveryTimeWindowsTable extends Migration
{
    public function up()
    {
        Schema::create('delivery_time_windows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('zone_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('store_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('day_of_week', 10)->nullable(); // mon, tue, wed...
            $table->time('start_time');
            $table->time('end_time');
            $table->decimal('extra_charge', 10, 2)->default(0); // surcharge for this window
            $table->boolean('is_peak')->default(false);
            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->index(['zone_id', 'day_of_week']);
            $table->index(['store_id', 'day_of_week']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('delivery_time_windows');
    }
}
