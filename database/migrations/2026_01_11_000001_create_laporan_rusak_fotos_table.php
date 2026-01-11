<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('laporan_rusak_fotos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('laporan_rusak_id');
            $table->string('posisi')->nullable();
            $table->string('file_path');
            $table->timestamps();

            $table->index('laporan_rusak_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('laporan_rusak_fotos');
    }
};
