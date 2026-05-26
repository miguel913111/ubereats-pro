<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddQrCodeToTableReservations extends Migration
{
    public function up()
    {
        if (Schema::hasTable('table_reservations')) {
            Schema::table('table_reservations', function (Blueprint $table) {
            $table->string('qr_code', 191)->nullable()->after('status');
            $table->timestamp('checked_in_at')->nullable()->after('qr_code');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('table_reservations')) {
            Schema::table('table_reservations', function (Blueprint $table) {
            $table->dropColumn(['qr_code', 'checked_in_at']);
            });
        }
    }
}
