<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('laporan_rusak', function (Blueprint $table) {
            $table->string('status')->default('pending')->after('lokasi');
            $table->text('admin_keterangan')->nullable()->after('status');
        });
    }

    public function down()
    {
        Schema::table('laporan_rusak', function (Blueprint $table) {
            $table->dropColumn(['status', 'admin_keterangan']);
        });
    }
};
