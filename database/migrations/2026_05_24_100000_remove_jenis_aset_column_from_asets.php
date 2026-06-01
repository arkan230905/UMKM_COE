<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * REFACTOR: Menghapus kolom jenis_aset dari tabel asets
     * Alasan: Sistem hanya mengelola aset tetap, tidak ada lagi aset tidak tetap
     * Semua aset dianggap sebagai aset tetap yang perlu disusutkan
     */
    public function up(): void
    {
        Schema::table('asets', function (Blueprint $table) {
            if (Schema::hasColumn('asets', 'jenis_aset')) {
                $table->dropColumn('jenis_aset');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asets', function (Blueprint $table) {
            if (!Schema::hasColumn('asets', 'jenis_aset')) {
                $table->enum('jenis_aset', ['Aset Tetap', 'Aset Tidak Berwujud'])
                    ->default('Aset Tetap')
                    ->after('kategori_aset_id')
                    ->comment('Jenis aset: Tetap (disusutkan), Tidak Berwujud (amortisasi)');
            }
        });
    }
};
