<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('asets')) return;

        Schema::table('asets', function (Blueprint $table) {
            // Tambah kolom yang dibutuhkan controller tapi belum ada di DB
            if (!Schema::hasColumn('asets', 'kategori_aset_id')) {
                $table->unsignedBigInteger('kategori_aset_id')->nullable()->after('id');
            }
            if (!Schema::hasColumn('asets', 'biaya_perolehan')) {
                $table->decimal('biaya_perolehan', 15, 2)->default(0)->after('kategori_aset_id');
            }
            if (!Schema::hasColumn('asets', 'nilai_residu')) {
                $table->decimal('nilai_residu', 15, 2)->default(0)->after('biaya_perolehan');
            }
            if (!Schema::hasColumn('asets', 'umur_manfaat')) {
                $table->integer('umur_manfaat')->default(5)->after('nilai_residu');
            }
            if (!Schema::hasColumn('asets', 'penyusutan_per_tahun')) {
                $table->decimal('penyusutan_per_tahun', 15, 2)->default(0)->after('umur_manfaat');
            }
            if (!Schema::hasColumn('asets', 'penyusutan_per_bulan')) {
                $table->decimal('penyusutan_per_bulan', 15, 2)->default(0)->after('penyusutan_per_tahun');
            }
            if (!Schema::hasColumn('asets', 'tanggal_beli')) {
                $table->date('tanggal_beli')->nullable()->after('penyusutan_per_bulan');
            }
            if (!Schema::hasColumn('asets', 'tanggal_akuisisi')) {
                $table->date('tanggal_akuisisi')->nullable()->after('tanggal_beli');
            }
        });
    }

    public function down(): void
    {
        // SQLite tidak mendukung DROP COLUMN dengan mudah; skip rollback
    }
};
