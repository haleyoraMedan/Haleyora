<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('laporan_rusak', function (Blueprint $table) {
            if (!Schema::hasColumn('laporan_rusak', 'kategori')) {
                $table->string('kategori')->nullable()->after('kondisi');
            }
            if (!Schema::hasColumn('laporan_rusak', 'odo_meter')) {
                $table->string('odo_meter')->nullable()->after('lokasi');
            }
            if (!Schema::hasColumn('laporan_rusak', 'sim_foto')) {
                $table->string('sim_foto')->nullable()->after('status');
            }
            if (!Schema::hasColumn('laporan_rusak', 'stnk_foto')) {
                $table->string('stnk_foto')->nullable()->after('sim_foto');
            }
        });
    }

    public function down()
    {
        Schema::table('laporan_rusak', function (Blueprint $table) {
            if (Schema::hasColumn('laporan_rusak', 'kategori')) {
                $table->dropColumn('kategori');
            }
            if (Schema::hasColumn('laporan_rusak', 'odo_meter')) {
                $table->dropColumn('odo_meter');
            }
            if (Schema::hasColumn('laporan_rusak', 'sim_foto')) {
                $table->dropColumn('sim_foto');
            }
            if (Schema::hasColumn('laporan_rusak', 'stnk_foto')) {
                $table->dropColumn('stnk_foto');
            }
        });
    }
};
