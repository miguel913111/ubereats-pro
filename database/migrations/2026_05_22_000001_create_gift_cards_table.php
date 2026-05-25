<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGiftCardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gift_cards', function (Blueprint $table) {
            $table->id();
            $table->string('title', 191);
            $table->text('description')->nullable();
            $table->string('code', 50)->unique();
            $table->decimal('amount', 24, 3)->default(0);
            $table->decimal('min_purchase', 24, 3)->default(0);
            $table->decimal('max_discount', 24, 3)->default(0);
            $table->date('start_date');
            $table->date('expire_date');
            $table->integer('total_uses')->default(0);
            $table->integer('used_count')->default(0);
            $table->integer('limit')->nullable();
            $table->boolean('status')->default(1);
            $table->string('image', 191)->nullable();
            $table->string('created_by', 191)->default('admin');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('gift_cards');
    }
}
