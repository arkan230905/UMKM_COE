<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // CEK DULU apakah tabel coas ada
        if (Schema::hasTable('coas')) {

            Schema::table('coas', function (Blueprint $table) {

                // Drop foreign key kalau ada
                try {
                    $table->dropForeign(['kode_induk']);
                } catch (\Exception $e) {
                    // skip kalau tidak ada
                }

                // Drop kolom kalau ada
                if (Schema::hasColumn('coas', 'kode_induk')) {
                    $table->dropColumn('kode_induk');
                }

                if (Schema::hasColumn('coas', 'is_akun_header')) {
                    $table->dropColumn('is_akun_header');
                }
            });
        }
    }

    public function down(): void
    {
        // CEK DULU juga saat rollback
        if (Schema::hasTable('coas')) {

            Schema::table('coas', function (Blueprint $table) {

                if (!Schema::hasColumn('coas', 'kode_induk')) {
                    $table->string('kode_induk')->nullable();
                }

                if (!Schema::hasColumn('coas', 'is_akun_header')) {
                    $table->boolean('is_akun_header')->default(false);
                }
            });
        }
    }
};