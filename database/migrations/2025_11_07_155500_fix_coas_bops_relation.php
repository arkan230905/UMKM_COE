<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Nonaktifkan foreign key check
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Hapus foreign key constraint yang bermasalah
        if (Schema::hasTable('bops')) {
            Schema::table('bops', function (Blueprint $table) {
                if (DB::getDriverName() !== 'sqlite') {
                    $sm = Schema::getConnection()->getDoctrineSchemaManager();
                    $foreignKeys = $sm->listTableForeignKeys('bops');
                    
                    foreach ($foreignKeys as $foreignKey) {
                        if (in_array('kode_akun', $foreignKey->getLocalColumns())) {
                            $table->dropForeign([$foreignKey->getLocalColumns()[0]]);
                            break;
                        }
                    }
                }
            });
        }

        // Modifikasi kolom kode_akun di coas
        if (Schema::hasTable('coas')) {
            Schema::table('coas', function (Blueprint $table) {
                $table->string('kode_akun', 20)->change();
            });
        }

        // Aktifkan kembali foreign key check
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Nonaktifkan foreign key check
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Modifikasi kolom kode_akun di coas kembali ke semula
        if (Schema::hasTable('coas')) {
            Schema::table('coas', function (Blueprint $table) {
                $table->string('kode_akun', 10)->change();
            });
        }

        // Aktifkan kembali foreign key check
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
};
