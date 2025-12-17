<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Tambahkan kolom role jika belum ada
        if (!Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('role')->default('pelanggan')->after('email');
            });
        }

        // Tambahkan kolom perusahaan_id jika belum ada
        if (!Schema::hasColumn('users', 'perusahaan_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedBigInteger('perusahaan_id')->nullable()->after('role');
            });

            // Tambahkan foreign key secara terpisah
            Schema::table('users', function (Blueprint $table) {
                $table->foreign('perusahaan_id')
                      ->references('id')
                      ->on('perusahaan')
                      ->onDelete('set null');
            });
        }
    }

    public function down()
    {
        // Hapus foreign key dulu
        if (Schema::hasColumn('users', 'perusahaan_id')) {
            Schema::table('users', function (Blueprint $table) {
                // Dapatkan nama foreign key
                $foreignKeys = DB::select("
                    SELECT CONSTRAINT_NAME 
                    FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                    WHERE TABLE_NAME = 'users' 
                    AND COLUMN_NAME = 'perusahaan_id' 
                    AND CONSTRAINT_NAME <> 'PRIMARY'
                    AND REFERENCED_TABLE_NAME IS NOT NULL
                ");

                if (count($foreignKeys) > 0) {
                    $table->dropForeign([$foreignKeys[0]->CONSTRAINT_NAME]);
                }

                $table->dropColumn('perusahaan_id');
            });
        }

        // Hapus kolom role
        if (Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('role');
            });
        }
    }
};