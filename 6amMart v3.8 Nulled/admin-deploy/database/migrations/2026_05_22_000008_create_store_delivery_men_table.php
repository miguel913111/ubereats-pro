<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStoreDeliveryMenTable extends Migration
{
    public function up()
    {
        Schema::create('store_delivery_men', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            $table->string('f_name', 191);
            $table->string('l_name', 191)->nullable();
            $table->string('phone', 20)->unique();
            $table->string('email', 191)->nullable();
            $table->string('password', 191);
            $table->string('identity_type', 50)->nullable();
            $table->string('identity_number', 191)->nullable();
            $table->string('identity_image', 191)->nullable();
            $table->string('image', 191)->nullable();
            $table->string('fcm_token', 191)->nullable();
            $table->tinyInteger('status')->default(1);
            $table->tinyInteger('active')->default(0);
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('store_delivery_men');
    }
}
