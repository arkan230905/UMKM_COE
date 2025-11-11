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
        Schema::table('asets', function (Blueprint $table) {
            // Drop kolom kategori string, ganti dengan foreign key
            if (Schema::hasColumn('asets', 'kategori')) {
                $table->dropColumn('kategori');
            }
            
            // Tambah kategori_aset_id jika belum ada
            if (!Schema::hasColumn('asets', 'kategori_aset_id')) {
                $table->foreignId('kategori_aset_id')->nullable()->after('nama_aset')->constrained('kategori_asets')->onDelete('set null');
            }
            
            // Tambah kolom yang hilang
            if (!Schema::hasColumn('asets', 'biaya_perolehan')) {
                $table->decimal('biaya_perolehan', 15, 2)->default(0)->after('harga_perolehan');
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
                $table->date('tanggal_beli')->nullable()->after('kategori_aset_id');
            }
            
            if (!Schema::hasColumn('asets', 'tanggal_akuisisi')) {
                $table->date('tanggal_akuisisi')->nullable()->after('tanggal_beli');
            }
            
            // Update status enum untuk menambahkan opsi baru
            $table->enum('status', ['aktif', 'tidak_aktif', 'dihapus', 'disewakan', 'dioperasikan'])->default('aktif')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asets', function (Blueprint $table) {
            $table->dropColumn([
                'kategori_aset_id',
                'biaya_perolehan',
                'nilai_residu',
                'umur_manfaat',
                'penyusutan_per_tahun',
                'penyusutan_per_bulan',
                'tanggal_beli',
                'tanggal_akuisisi'
            ]);
            
            $table->string('kategori')->nullable();
            $table->enum('status', ['aktif', 'tidak_aktif', 'dihapus'])->default('aktif')->change();
        });
    }
};
