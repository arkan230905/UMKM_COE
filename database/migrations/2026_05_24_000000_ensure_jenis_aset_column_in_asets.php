<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Memastikan kolom jenis_aset ada di tabel asets untuk membedakan:
     * - Aset Tetap (disusutkan)
     * - Aset Tidak Tetap (tidak disusutkan, langsung biaya)
     * - Aset Tidak Berwujud (diamortisasi)
     */
    public function up(): void
    {
        Schema::table('asets', function (Blueprint $table) {
            if (!Schema::hasColumn('asets', 'jenis_aset')) {
                $table->enum('jenis_aset', ['Aset Tetap', 'Aset Tidak Tetap', 'Aset Tidak Berwujud'])
                    ->default('Aset Tetap')
                    ->after('kategori_aset_id')
                    ->comment('Jenis aset: Tetap (disusutkan), Tidak Tetap (langsung biaya), Tidak Berwujud (amortisasi)');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asets', function (Blueprint $table) {
            if (Schema::hasColumn('asets', 'jenis_aset')) {
                $table->dropColumn('jenis_aset');
            }
        });
    }
};
