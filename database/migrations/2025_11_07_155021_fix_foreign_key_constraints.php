<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Skip migration - kolom kode_akun sudah ada dengan tipe data yang tepat
        return;
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Nonaktifkan pengecekan foreign key sementara
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Kembalikan ke tipe data semula jika diperlukan
        if (Schema::hasColumn('coas', 'kode_akun')) {
            Schema::table('coas', function (Blueprint $table) {
                $table->string('kode_akun', 10)->change();
            });
        }

        if (Schema::hasTable('bops') && Schema::hasColumn('bops', 'kode_akun')) {
            Schema::table('bops', function (Blueprint $table) {
                $table->string('kode_akun', 10)->change();
            });
        }

        // Aktifkan kembali pengecekan foreign key
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
};
