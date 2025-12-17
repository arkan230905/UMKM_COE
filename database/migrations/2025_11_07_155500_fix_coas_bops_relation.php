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
        // Skip migration - kolom kode_akun sudah ada dengan tipe data yang tepat
        return;
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
