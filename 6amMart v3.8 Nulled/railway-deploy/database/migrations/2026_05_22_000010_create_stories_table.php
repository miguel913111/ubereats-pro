<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStoriesTable extends Migration
{
    public function up()
    {
        Schema::create('stories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            $table->string('title', 191)->nullable();
            $table->text('image')->nullable();
            $table->text('video')->nullable();
            $table->enum('type', ['image', 'video'])->default('image');
            $table->integer('duration')->default(5); // seconds
            $table->timestamp('expires_at');
            $table->boolean('status')->default(1);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('stories');
    }
}
