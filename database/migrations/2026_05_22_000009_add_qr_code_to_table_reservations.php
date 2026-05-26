<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddQrCodeToTableReservations extends Migration
{
    public function up()
    {
        Schema::table('table_reservations', function (Blueprint $table) {
            if (!Schema::hasColumn('table_reservations', 'qr_code')) {
                $table->string('qr_code', 191)->nullable()->after('status');
            }
            if (!Schema::hasColumn('table_reservations', 'checked_in_at')) {
                $table->timestamp('checked_in_at')->nullable()->after('qr_code');
            }
        });
    }

    public function down()
    {
        Schema::table('table_reservations', function (Blueprint $table) {
            $table->dropColumn(['qr_code', 'checked_in_at']);
        });
    }
}
