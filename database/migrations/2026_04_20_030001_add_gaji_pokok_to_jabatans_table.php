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
            }
            if (!Schema::hasColumn('jabatans', 'kode_jabatan')) {
                $table->string('kode_jabatan', 10)->nullable()->after('gaji_pokok');
            }
        });
    }

    public function down(): void
    {
        Schema::table('jabatans', function (Blueprint $table) {
            // Hapus kolom yang ditambahkan
            if (Schema::hasColumn('jabatans', 'gaji_pokok')) {
                $table->dropColumn('gaji_pokok');
            }
            if (Schema::hasColumn('jabatans', 'kode_jabatan')) {
                $table->dropColumn('kode_jabatan');
            }
        });
    }
};
