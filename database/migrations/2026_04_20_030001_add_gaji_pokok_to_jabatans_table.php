<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jabatans', function (Blueprint $table) {
            // Tambahkan kolom yang hilang
            if (!Schema::hasColumn('jabatans', 'gaji_pokok')) {
                $table->decimal('gaji_pokok', 15, 2)->default(0)->after('tarif');
                $table->decimal('tarif_per_jam', 15, 2)->default(0)->after('gaji_pokok');
                $table->string('kode_jabatan', 10)->nullable()->after('tarif_per_jam');
            }
        });
    }

    public function down(): void
    {
        Schema::table('jabatans', function (Blueprint $table) {
            // Hapus kolom yang ditambahkan
            $table->dropColumn(['gaji_pokok', 'tarif_per_jam', 'kode_jabatan']);
        });
    }
};
