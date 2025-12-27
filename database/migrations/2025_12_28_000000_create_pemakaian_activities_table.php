<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pemakaian_activities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pemakaian_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('action');
            $table->json('data')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pemakaian_activities');
    }
};
