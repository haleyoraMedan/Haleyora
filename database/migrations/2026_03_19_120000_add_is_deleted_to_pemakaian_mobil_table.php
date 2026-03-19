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
        if (!Schema::hasColumn('pemakaian_mobil', 'is_deleted')) {
            Schema::table('pemakaian_mobil', function (Blueprint $table) {
                $table->timestamp('is_deleted')->nullable()->after('status');
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
        if (Schema::hasColumn('pemakaian_mobil', 'is_deleted')) {
            Schema::table('pemakaian_mobil', function (Blueprint $table) {
                $table->dropColumn('is_deleted');
            });
        }
    }
};
