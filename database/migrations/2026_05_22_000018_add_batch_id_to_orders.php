<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBatchIdToOrders extends Migration
{
    public function up()
    {
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'batch_id')) {
                $table->foreignId('batch_id')->nullable()->constrained('delivery_batches')->onDelete('set null')->after('delivery_man_id');
            }
            if (!Schema::hasColumn('orders', 'batch_id')) {
                $table->index('batch_id');
            }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['batch_id']);
            $table->dropColumn('batch_id');
            });
        }
    }
}
