<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('laporan_rusak', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('mobil_id');
            $table->string('kondisi')->nullable();
            $table->text('catatan')->nullable();
            $table->string('lokasi')->nullable();
            $table->timestamps();
            $table->unique('mobil_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('laporan_rusak');
    }
};
