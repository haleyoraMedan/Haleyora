<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // If the column already exists (from a previous partial run), ensure its type
        // matches the mobil.id column (INT UNSIGNED). Otherwise create it.
        if (Schema::hasColumn('users', 'mobil_id')) {
            DB::statement('ALTER TABLE `users` MODIFY `mobil_id` INT UNSIGNED NULL');
        } else {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedInteger('mobil_id')->nullable()->after('penempatan_id');
            });
        }

        // Add foreign key only if it does not already exist
        $fk = DB::select("SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND CONSTRAINT_TYPE = 'FOREIGN KEY' AND CONSTRAINT_NAME = 'users_mobil_id_foreign'");
        if (count($fk) === 0) {
            DB::statement('ALTER TABLE `users` ADD CONSTRAINT `users_mobil_id_foreign` FOREIGN KEY (`mobil_id`) REFERENCES `mobil`(`id`) ON DELETE SET NULL');
        }
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['mobil_id']);
            $table->dropColumn('mobil_id');
        });
    }
};
