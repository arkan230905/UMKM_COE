<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migrasi.
     */
    public function up(): void
    {
        Schema::table('pegawais', function (Blueprint $table) {
            // Tambah kolom baru setelah kolom 'jabatan' (opsional urutannya)
            if (!Schema::hasColumn('pegawais', 'gaji_pokok')) {
                $table->decimal('gaji_pokok', 15, 2)->default(0)->after('jabatan');
            }
            if (!Schema::hasColumn('pegawais', 'tunjangan')) {
                $table->decimal('tunjangan', 15, 2)->default(0)->after('gaji_pokok');
            }
        });
    }

    /**
     * Balikkan migrasi.
     */
    public function down(): void
    {
        Schema::table('pegawais', function (Blueprint $table) {
            $table->dropColumn(['gaji_pokok', 'tunjangan']);
        });
    }
};
