<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBaseTables extends Migration
{
    public function up()
    {
        // Users
        if (!Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->string('f_name')->nullable();
                $table->string('l_name')->nullable();
                $table->string('email')->nullable()->unique();
                $table->string('phone')->nullable()->unique();
                $table->string('password', 100)->nullable();
                $table->string('image')->nullable();
                $table->rememberToken();
                $table->boolean('status')->default(1);
                $table->integer('order_count')->default(0);
                $table->decimal('wallet_balance', 24, 3)->default(0);
                $table->decimal('loyalty_point', 24, 3)->default(0);
                $table->string('ref_code', 10)->nullable()->unique();
                $table->string('current_language_key')->default('en')->nullable();
                $table->unsignedBigInteger('ref_by')->nullable();
                $table->string('temp_token')->nullable();
                $table->string('module_ids')->nullable();
                $table->boolean('is_email_verified')->default(0);
                $table->boolean('is_from_pos')->default(0);
                $table->timestamp('email_verified_at')->nullable();
                $table->boolean('is_phone_verified')->default(0);
                $table->timestamps();
            });
        }

        // Password Resets
        if (!Schema::hasTable('password_resets')) {
            Schema::create('password_resets', function (Blueprint $table) {
                $table->string('email')->index();
                $table->string('token');
                $table->timestamp('created_at')->nullable();
            });
        }

        // Admins
        if (!Schema::hasTable('admins')) {
            Schema::create('admins', function (Blueprint $table) {
                $table->id();
                $table->string('f_name')->nullable();
                $table->string('l_name')->nullable();
                $table->string('email')->unique();
                $table->string('phone')->nullable();
                $table->string('image')->nullable();
                $table->string('password')->nullable();
                $table->rememberToken();
                $table->string('login_remember_token')->nullable();
                $table->unsignedBigInteger('role_id')->nullable();
                $table->unsignedBigInteger('zone_id')->nullable();
                $table->boolean('is_logged_in')->default(1);
                $table->timestamps();
            });
        }

        // Admin Roles
        if (!Schema::hasTable('admin_roles')) {
            Schema::create('admin_roles', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('modules')->nullable();
                $table->boolean('status')->default(1);
                $table->timestamps();
            });
        }

        // Vendors
        if (!Schema::hasTable('vendors')) {
            Schema::create('vendors', function (Blueprint $table) {
                $table->id();
                $table->string('f_name')->nullable();
                $table->string('l_name')->nullable();
                $table->string('email')->unique();
                $table->string('phone')->nullable();
                $table->string('password')->nullable();
                $table->string('image')->nullable();
                $table->boolean('status')->default(1);
                $table->rememberToken();
                $table->string('auth_token', 120)->nullable();
                $table->timestamps();
            });
        }

        // Stores
        if (!Schema::hasTable('stores')) {
            Schema::create('stores', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('phone');
                $table->string('email')->nullable();
                $table->string('logo')->nullable();
                $table->string('latitude')->nullable();
                $table->string('longitude')->nullable();
                $table->text('address')->nullable();
                $table->string('footer_text')->nullable();
                $table->decimal('minimum_order', 24, 3)->default(0);
                $table->decimal('comission', 24, 3)->nullable();
                $table->boolean('commission_active')->default(0);
                $table->boolean('subscription_active')->default(0);
                $table->boolean('fixed_delivery_fee_active')->default(0);
                $table->decimal('fixed_delivery_fee', 24, 3)->default(0);
                $table->decimal('driver_per_km_charge', 24, 3)->default(0);
                $table->decimal('driver_fixed_charge', 24, 3)->default(0);
                $table->boolean('schedule_order')->default(0);
                $table->boolean('status')->default(1);
                $table->unsignedBigInteger('vendor_id');
                $table->boolean('free_delivery')->default(0);
                $table->string('rating')->nullable();
                $table->string('cover_photo')->nullable();
                $table->boolean('delivery')->default(1);
                $table->boolean('take_away')->default(1);
                $table->boolean('item_section')->default(1);
                $table->decimal('tax', 24, 3)->default(0);
                $table->unsignedBigInteger('zone_id')->nullable();
                $table->boolean('reviews_section')->default(1);
                $table->boolean('active')->default(1);
                $table->string('off_day')->nullable();
                $table->string('gst')->nullable();
                $table->boolean('self_delivery_system')->default(0);
                $table->boolean('pos_system')->default(0);
                $table->decimal('minimum_shipping_charge', 24, 3)->default(0);
                $table->string('delivery_time')->nullable();
                $table->boolean('veg')->default(0);
                $table->boolean('non_veg')->default(0);
                $table->integer('order_count')->default(0);
                $table->integer('total_order')->default(0);
                $table->unsignedBigInteger('module_id');
                $table->string('pickup_zone_id')->nullable();
                $table->integer('order_place_to_schedule_interval')->default(0);
                $table->boolean('featured')->default(0);
                $table->decimal('per_km_shipping_charge', 24, 3)->default(0);
                $table->boolean('prescription_order')->default(0);
                $table->string('slug')->nullable();
                $table->decimal('maximum_shipping_charge', 24, 3)->nullable();
                $table->boolean('cutlery')->default(0);
                $table->string('meta_title')->nullable();
                $table->string('meta_description')->nullable();
                $table->string('meta_image')->nullable();
                $table->boolean('announcement')->default(0);
                $table->text('announcement_message')->nullable();
                $table->text('comment')->nullable();
                $table->string('tin')->nullable();
                $table->date('tin_expire_date')->nullable();
                $table->string('tin_certificate_image')->nullable();
                $table->timestamps();
            });
        }

        // Vendor Employees
        if (!Schema::hasTable('vendor_employees')) {
            Schema::create('vendor_employees', function (Blueprint $table) {
                $table->id();
                $table->string('f_name')->nullable();
                $table->string('l_name')->nullable();
                $table->string('email')->unique();
                $table->string('phone')->nullable();
                $table->string('image')->nullable();
                $table->string('password')->nullable();
                $table->rememberToken();
                $table->string('login_remember_token')->nullable();
                $table->unsignedBigInteger('employee_role_id')->nullable();
                $table->unsignedBigInteger('vendor_id')->nullable();
                $table->unsignedBigInteger('store_id')->nullable();
                $table->boolean('status')->default(1);
                $table->boolean('is_logged_in')->default(1);
                $table->string('auth_token', 120)->nullable();
                $table->timestamps();
            });
        }

        // Employee Roles
        if (!Schema::hasTable('employee_roles')) {
            Schema::create('employee_roles', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('modules')->nullable();
                $table->boolean('status')->default(1);
                $table->timestamps();
            });
        }

        // Delivery Men
        if (!Schema::hasTable('delivery_men')) {
            Schema::create('delivery_men', function (Blueprint $table) {
                $table->id();
                $table->string('f_name')->nullable();
                $table->string('l_name')->nullable();
                $table->string('email')->nullable();
                $table->string('phone');
                $table->string('password')->nullable();
                $table->string('image')->nullable();
                $table->string('identity_image')->nullable();
                $table->string('identity_type')->nullable();
                $table->string('identity_number')->nullable();
                $table->boolean('status')->default(1);
                $table->integer('active')->default(1);
                $table->integer('available')->default(1);
                $table->decimal('earning', 24, 3)->default(0);
                $table->unsignedBigInteger('store_id')->nullable();
                $table->integer('current_orders')->default(0);
                $table->unsignedBigInteger('vehicle_id')->nullable();
                $table->unsignedBigInteger('zone_id')->nullable();
                $table->unsignedBigInteger('ref_by')->nullable();
                $table->decimal('loyalty_point', 24, 3)->default(0);
                $table->string('ref_code', 10)->nullable();
                $table->string('auth_token', 120)->nullable();
                $table->string('application_status')->default('pending');
                $table->boolean('is_delivery')->default(1);
                $table->string('type')->default('zone_wise');
                $table->timestamps();
            });
        }

        // Zones
        if (!Schema::hasTable('zones')) {
            Schema::create('zones', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('display_name')->nullable();
                $table->geometry('coordinates')->nullable();
                $table->integer('status')->default(1);
                $table->string('store_wise_topic')->nullable();
                $table->string('customer_wise_topic')->nullable();
                $table->string('deliveryman_wise_topic')->nullable();
                $table->boolean('cash_on_delivery')->default(1);
                $table->boolean('digital_payment')->default(1);
                $table->integer('increased_delivery_fee')->default(0);
                $table->integer('increased_delivery_fee_status')->default(0);
                $table->string('increase_delivery_charge_message')->nullable();
                $table->boolean('offline_payment')->default(0);
                $table->boolean('is_default')->default(0);
                $table->timestamps();
            });
        }

        // Modules
        if (!Schema::hasTable('modules')) {
            Schema::create('modules', function (Blueprint $table) {
                $table->id();
                $table->string('module_name');
                $table->string('module_type');
                $table->string('thumbnail')->nullable();
                $table->boolean('status')->default(1);
                $table->integer('stores_count')->default(0);
                $table->string('icon')->nullable();
                $table->integer('theme_id')->default(1);
                $table->text('description')->nullable();
                $table->boolean('all_zone_service')->default(0);
                $table->timestamps();
            });
        }

        // Business Settings
        if (!Schema::hasTable('business_settings')) {
            Schema::create('business_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key');
                $table->text('value')->nullable();
                $table->timestamps();
            });
        }

        // Email Verifications
        if (!Schema::hasTable('email_verifications')) {
            Schema::create('email_verifications', function (Blueprint $table) {
                $table->id();
                $table->string('email');
                $table->string('token');
                $table->timestamps();
            });
        }

        // Phone Verifications
        if (!Schema::hasTable('phone_verifications')) {
            Schema::create('phone_verifications', function (Blueprint $table) {
                $table->id();
                $table->string('phone');
                $table->string('token');
                $table->timestamps();
            });
        }

        // Carts
        if (!Schema::hasTable('carts')) {
            Schema::create('carts', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->unsignedBigInteger('module_id')->nullable();
                $table->unsignedBigInteger('item_id')->nullable();
                $table->boolean('is_guest')->default(0);
                $table->string('add_on_ids')->nullable();
                $table->string('add_on_qtys')->nullable();
                $table->string('item_type')->nullable();
                $table->decimal('price', 24, 3)->default(0);
                $table->integer('quantity')->default(1);
                $table->string('variation')->nullable();
                $table->timestamps();
            });
        }

        // Customer Addresses
        if (!Schema::hasTable('customer_addresses')) {
            Schema::create('customer_addresses', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->string('contact_person_name')->nullable();
                $table->string('contact_person_number')->nullable();
                $table->string('address')->nullable();
                $table->string('address_type')->nullable();
                $table->string('latitude')->nullable();
                $table->string('longitude')->nullable();
                $table->string('road')->nullable();
                $table->string('house')->nullable();
                $table->string('floor')->nullable();
                $table->timestamps();
            });
        }

        // Storages
        if (!Schema::hasTable('storages')) {
            Schema::create('storages', function (Blueprint $table) {
                $table->id();
                $table->string('data_type');
                $table->unsignedBigInteger('data_id');
                $table->string('key');
                $table->string('value')->nullable();
                $table->timestamps();
            });
        }

        // Translations
        if (!Schema::hasTable('translations')) {
            Schema::create('translations', function (Blueprint $table) {
                $table->id();
                $table->string('translationable_type');
                $table->unsignedBigInteger('translationable_id');
                $table->string('locale');
                $table->string('key');
                $table->text('value')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('translations');
        Schema::dropIfExists('storages');
        Schema::dropIfExists('customer_addresses');
        Schema::dropIfExists('carts');
        Schema::dropIfExists('phone_verifications');
        Schema::dropIfExists('email_verifications');
        Schema::dropIfExists('business_settings');
        Schema::dropIfExists('modules');
        Schema::dropIfExists('zones');
        Schema::dropIfExists('delivery_men');
        Schema::dropIfExists('employee_roles');
        Schema::dropIfExists('vendor_employees');
        Schema::dropIfExists('stores');
        Schema::dropIfExists('vendors');
        Schema::dropIfExists('admin_roles');
        Schema::dropIfExists('admins');
        Schema::dropIfExists('password_resets');
        Schema::dropIfExists('users');
    }
}
