<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStripeConnectFields extends Migration
{
    public function up()
    {
        if (Schema::hasTable('stores')) {
            Schema::table('stores', function (Blueprint $table) {
                if (!Schema::hasColumn('stores', 'stripe_account_id')) {
                    $table->string('stripe_account_id')->nullable()->after('driver_fixed_charge');
                }
                if (!Schema::hasColumn('stores', 'stripe_onboarding_complete')) {
                    $table->boolean('stripe_onboarding_complete')->default(0)->after('stripe_account_id');
                }
            });
        }

        if (Schema::hasTable('delivery_men')) {
            Schema::table('delivery_men', function (Blueprint $table) {
                if (!Schema::hasColumn('delivery_men', 'stripe_account_id')) {
                    $table->string('stripe_account_id')->nullable()->after('ref_by');
                }
                if (!Schema::hasColumn('delivery_men', 'stripe_onboarding_complete')) {
                    $table->boolean('stripe_onboarding_complete')->default(0)->after('stripe_account_id');
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('stores')) {
            Schema::table('stores', function (Blueprint $table) {
                $table->dropColumn(['stripe_account_id', 'stripe_onboarding_complete']);
            });
        }

        if (Schema::hasTable('delivery_men')) {
            Schema::table('delivery_men', function (Blueprint $table) {
                $table->dropColumn(['stripe_account_id', 'stripe_onboarding_complete']);
            });
        }
    }
}
