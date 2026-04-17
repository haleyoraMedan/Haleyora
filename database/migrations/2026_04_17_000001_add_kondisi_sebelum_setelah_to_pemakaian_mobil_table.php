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
            if (!Schema::hasColumn('pemakaian_mobil', 'kondisi_sebelum_setelah')) {
                $table->string('kondisi_sebelum_setelah')->nullable()->after('catatan');
            }
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
            if (Schema::hasColumn('pemakaian_mobil', 'kondisi_sebelum_setelah')) {
                $table->dropColumn('kondisi_sebelum_setelah');
            }
        });
    }
};
