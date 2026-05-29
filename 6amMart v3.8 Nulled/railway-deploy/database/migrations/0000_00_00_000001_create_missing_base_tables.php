<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMissingBaseTables extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('orders')) {
            Schema::create('orders', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->unsignedBigInteger('store_id')->nullable();
                $table->unsignedBigInteger('zone_id')->nullable();
                $table->unsignedBigInteger('module_id')->nullable();
                $table->unsignedBigInteger('delivery_man_id')->nullable();
                $table->unsignedBigInteger('delivery_address_id')->nullable();
                $table->unsignedBigInteger('dm_vehicle_id')->nullable();
                $table->decimal('order_amount', 24, 3)->default(0);
                $table->decimal('coupon_discount_amount', 24, 3)->default(0);
                $table->decimal('total_tax_amount', 24, 3)->default(0);
                $table->decimal('store_discount_amount', 24, 3)->default(0);
                $table->decimal('flash_admin_discount_amount', 24, 3)->default(0);
                $table->decimal('flash_store_discount_amount', 24, 3)->default(0);
                $table->decimal('delivery_charge', 24, 3)->default(0);
                $table->decimal('additional_charge', 24, 3)->default(0);
                $table->decimal('original_delivery_charge', 24, 3)->default(0);
                $table->decimal('extra_packaging_amount', 24, 3)->default(0);
                $table->decimal('dm_tips', 24, 3)->default(0);
                $table->decimal('distance', 24, 3)->default(0);
                $table->decimal('tax_percentage', 24, 3)->default(0);
                $table->decimal('ref_bonus_amount', 24, 3)->default(0);
                $table->integer('bring_change_amount')->default(0);
                $table->integer('scheduled')->default(0);
                $table->integer('processing_time')->default(0);
                $table->integer('details_count')->default(0);
                $table->string('order_status')->default('pending');
                $table->string('order_type')->default('delivery');
                $table->string('payment_method')->default('cash_on_delivery');
                $table->string('payment_status')->default('unpaid');
                $table->text('delivery_address')->nullable();
                $table->text('order_note')->nullable();
                $table->text('order_attachment')->nullable();
                $table->text('order_proof')->nullable();
                $table->text('receiver_details')->nullable();
                $table->boolean('prescription_order')->default(0);
                $table->boolean('cutlery')->default(0);
                $table->boolean('is_guest')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('order_details')) {
            Schema::create('order_details', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('order_id');
                $table->unsignedBigInteger('item_id')->nullable();
                $table->unsignedBigInteger('item_campaign_id')->nullable();
                $table->string('item_details')->nullable();
                $table->string('variation')->nullable();
                $table->decimal('price', 24, 3)->default(0);
                $table->decimal('discount_on_item', 24, 3)->default(0);
                $table->string('discount_type')->nullable();
                $table->integer('quantity')->default(1);
                $table->decimal('tax_amount', 24, 3)->default(0);
                $table->string('add_ons')->nullable();
                $table->string('add_on_qtys')->nullable();
                $table->decimal('total_add_on_price', 24, 3)->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('items')) {
            Schema::create('items', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->nullable();
                $table->text('description')->nullable();
                $table->string('image')->nullable();
                $table->unsignedBigInteger('category_id')->nullable();
                $table->unsignedBigInteger('category_ids')->nullable();
                $table->unsignedBigInteger('sub_category_id')->nullable();
                $table->unsignedBigInteger('store_id')->nullable();
                $table->unsignedBigInteger('module_id')->nullable();
                $table->decimal('price', 24, 3)->default(0);
                $table->decimal('discount', 24, 3)->default(0);
                $table->string('discount_type')->default('percent');
                $table->string('unit_type')->nullable();
                $table->integer('stock')->default(0);
                $table->boolean('status')->default(1);
                $table->boolean('veg')->default(0);
                $table->boolean('recommended')->default(0);
                $table->integer('order_count')->default(0);
                $table->decimal('tax', 24, 3)->default(0);
                $table->string('available_time_starts')->nullable();
                $table->string('available_time_ends')->nullable();
                $table->string('attribute_id')->nullable();
                $table->text('add_ons')->nullable();
                $table->text('variations')->nullable();
                $table->text('choice_options')->nullable();
                $table->text('food_variations')->nullable();
                $table->boolean('organic')->default(0);
                $table->boolean('halal')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('categories')) {
            Schema::create('categories', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->nullable();
                $table->unsignedBigInteger('parent_id')->nullable();
                $table->integer('position')->default(0);
                $table->string('image')->nullable();
                $table->unsignedBigInteger('module_id')->nullable();
                $table->unsignedBigInteger('store_id')->nullable();
                $table->integer('priority')->default(0);
                $table->boolean('status')->default(1);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('add_ons')) {
            Schema::create('add_ons', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->decimal('price', 24, 3)->default(0);
                $table->unsignedBigInteger('store_id')->nullable();
                $table->unsignedBigInteger('module_id')->nullable();
                $table->boolean('status')->default(1);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('campaigns')) {
            Schema::create('campaigns', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->text('description')->nullable();
                $table->string('image')->nullable();
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();
                $table->time('start_time')->nullable();
                $table->time('end_time')->nullable();
                $table->boolean('status')->default(1);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('campaign_store')) {
            Schema::create('campaign_store', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('campaign_id');
                $table->unsignedBigInteger('store_id');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('coupons')) {
            Schema::create('coupons', function (Blueprint $table) {
                $table->id();
                $table->string('title')->nullable();
                $table->string('code')->unique();
                $table->string('coupon_type')->default('default');
                $table->decimal('discount', 24, 3)->default(0);
                $table->string('discount_type')->default('percent');
                $table->decimal('min_purchase', 24, 3)->default(0);
                $table->decimal('max_discount', 24, 3)->default(0);
                $table->integer('limit')->default(0);
                $table->date('start_date')->nullable();
                $table->date('expire_date')->nullable();
                $table->unsignedBigInteger('store_id')->nullable();
                $table->unsignedBigInteger('module_id')->nullable();
                $table->unsignedBigInteger('zone_id')->nullable();
                $table->boolean('status')->default(1);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('reviews')) {
            Schema::create('reviews', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->unsignedBigInteger('item_id')->nullable();
                $table->unsignedBigInteger('order_id')->nullable();
                $table->unsignedBigInteger('store_id')->nullable();
                $table->unsignedBigInteger('delivery_man_id')->nullable();
                $table->integer('rating')->default(5);
                $table->text('comment')->nullable();
                $table->string('attachment')->nullable();
                $table->boolean('status')->default(1);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('account_transactions')) {
            Schema::create('account_transactions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('store_id')->nullable();
                $table->unsignedBigInteger('delivery_man_id')->nullable();
                $table->string('type')->nullable();
                $table->string('method')->nullable();
                $table->decimal('amount', 24, 3)->default(0);
                $table->text('ref')->nullable();
                $table->text('payment_info')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('admin_wallets')) {
            Schema::create('admin_wallets', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('admin_id')->nullable();
                $table->decimal('balance', 24, 3)->default(0);
                $table->decimal('withdrawn', 24, 3)->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('banners')) {
            Schema::create('banners', function (Blueprint $table) {
                $table->id();
                $table->string('title')->nullable();
                $table->string('image')->nullable();
                $table->string('type')->nullable();
                $table->unsignedBigInteger('store_id')->nullable();
                $table->unsignedBigInteger('module_id')->nullable();
                $table->unsignedBigInteger('zone_id')->nullable();
                $table->integer('priority')->default(0);
                $table->boolean('status')->default(1);
                $table->string('default_link')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('banners');
        Schema::dropIfExists('admin_wallets');
        Schema::dropIfExists('account_transactions');
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('coupons');
        Schema::dropIfExists('campaign_store');
        Schema::dropIfExists('campaigns');
        Schema::dropIfExists('add_ons');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('items');
        Schema::dropIfExists('order_details');
        Schema::dropIfExists('orders');
    }
}
