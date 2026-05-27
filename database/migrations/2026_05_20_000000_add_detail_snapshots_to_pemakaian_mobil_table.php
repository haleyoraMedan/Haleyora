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
            if (!Schema::hasColumn('pemakaian_mobil', 'detail_sebelum')) {
                $table->json('detail_sebelum')->nullable()->after('catatan');
            }
            if (!Schema::hasColumn('pemakaian_mobil', 'detail_sesudah')) {
                $table->json('detail_sesudah')->nullable()->after('detail_sebelum');
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
            if (Schema::hasColumn('pemakaian_mobil', 'detail_sesudah')) {
                $table->dropColumn('detail_sesudah');
            }
            if (Schema::hasColumn('pemakaian_mobil', 'detail_sebelum')) {
                $table->dropColumn('detail_sebelum');
            }
        });
    }
};
