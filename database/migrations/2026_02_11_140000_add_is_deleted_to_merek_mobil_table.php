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
        if (!Schema::hasColumn('merek_mobil', 'is_deleted')) {
            Schema::table('merek_mobil', function (Blueprint $table) {
                $table->timestamp('is_deleted')->nullable()->after('nama_merek');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('merek_mobil', 'is_deleted')) {
            Schema::table('merek_mobil', function (Blueprint $table) {
                $table->dropColumn('is_deleted');
            });
        }
    }
};
