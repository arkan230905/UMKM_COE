<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Tambahkan kolom gaji jika belum ada
        if (!Schema::hasColumn('jabatans', 'gaji')) {
            Schema::table('jabatans', function (Blueprint $table) {
                $table->decimal('gaji', 15, 2)->default(0)->after('asuransi');
            });
        }

        // Tambahkan kolom tarif jika belum ada
        if (!Schema::hasColumn('jabatans', 'tarif')) {
            Schema::table('jabatans', function (Blueprint $table) {
                $table->decimal('tarif', 15, 2)->default(0)->after('gaji');
            });
        }

        // Update kolom gaji dengan nilai dari gaji_pokok jika ada
        if (Schema::hasColumn('jabatans', 'gaji_pokok')) {
            DB::statement('UPDATE jabatans SET gaji = gaji_pokok WHERE gaji = 0');
        }

        // Update kolom tarif dengan nilai dari tarif_lembur jika ada
        if (Schema::hasColumn('jabatans', 'tarif_lembur')) {
            DB::statement('UPDATE jabatans SET tarif = tarif_lembur WHERE tarif = 0');
        }
    }

    public function down()
    {
        // Tidak perlu melakukan apa-apa di sini karena kita tidak ingin kehilangan data
        // Jika ingin rollback, buat migrasi terpisah
    }
};
