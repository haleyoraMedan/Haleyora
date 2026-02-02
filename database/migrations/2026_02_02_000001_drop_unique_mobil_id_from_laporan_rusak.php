<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('laporan_rusak', function (Blueprint $table) {
            // drop unique constraint on mobil_id to allow multiple laporan history
            $table->dropUnique(['mobil_id']);
        });
    }

    public function down()
    {
        Schema::table('laporan_rusak', function (Blueprint $table) {
            $table->unique('mobil_id');
        });
    }
};
