<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pemakaian_mobil', function (Blueprint $table) {
            $table->string('sim_foto')->nullable()->after('alasan_reject');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pemakaian_mobil', function (Blueprint $table) {
            if (Schema::hasColumn('pemakaian_mobil', 'sim_foto')) {
                $table->dropColumn('sim_foto');
            }
        });
    }
};
