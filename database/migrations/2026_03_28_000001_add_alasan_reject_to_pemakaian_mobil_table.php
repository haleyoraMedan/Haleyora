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
            if (!Schema::hasColumn('pemakaian_mobil', 'alasan_reject')) {
                $table->text('alasan_reject')->nullable()->after('status');
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
            if (Schema::hasColumn('pemakaian_mobil', 'alasan_reject')) {
                $table->dropColumn('alasan_reject');
            }
        });
    }
};
