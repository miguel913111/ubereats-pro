<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStoreDeliveryZonesTable extends Migration
{
    public function up()
    {
        Schema::create('store_delivery_zones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            $table->string('name', 191)->nullable();
            $table->text('coordinates'); // GeoJSON Polygon string
            $table->decimal('delivery_charge', 24, 3)->default(0);
            $table->decimal('minimum_order_amount', 24, 3)->default(0);
            $table->boolean('status')->default(1);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('store_delivery_zones');
    }
}
