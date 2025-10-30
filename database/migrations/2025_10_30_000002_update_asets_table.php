<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asets', function (Blueprint $table) {
            // Rename and modify existing columns
            $table->renameColumn('nama', 'nama_aset');
            $table->renameColumn('harga', 'harga_perolehan');
            
            // Add new columns
            $table->string('kode_aset')->unique()->after('id');
            $table->foreignId('kategori_aset_id')->nullable()->after('kategori');
            $table->decimal('biaya_perolehan', 15, 2)->default(0)->after('harga_perolehan');
            $table->decimal('total_perolehan', 15, 2)->virtualAs('harga_perolehan + biaya_perolehan')->after('biaya_perolehan');
            $table->decimal('nilai_residu', 15, 2)->default(0)->after('total_perolehan');
            $table->integer('umur_manfaat')->default(1)->comment('Dalam tahun')->after('nilai_residu');
            $table->decimal('penyusutan_per_tahun', 15, 2)->default(0)->after('umur_manfaat');
            $table->decimal('penyusutan_per_bulan', 15, 2)->default(0)->after('penyusutan_per_tahun');
            $table->decimal('nilai_buku', 15, 2)->default(0)->after('penyusutan_per_bulan');
            $table->date('tanggal_akuisisi')->after('tanggal_beli');
            $table->string('status', 20)->default('aktif')->after('nilai_buku');
            $table->text('keterangan')->nullable()->after('status');
            
            // Add foreign key constraint
            $table->foreign('kategori_aset_id')->references('id')->on('kategori_asets')->onDelete('set null');
            
            // Drop old kategori column after migration
            $table->dropColumn('kategori');
        });
    }

    public function down(): void
    {
        Schema::table('asets', function (Blueprint $table) {
            // Revert changes
            $table->renameColumn('nama_aset', 'nama');
            $table->renameColumn('harga_perolehan', 'harga');
            
            // Add back kategori column
            $table->string('kategori')->after('nama');
            
            // Drop added columns
            $table->dropColumn([
                'kode_aset',
                'kategori_aset_id',
                'biaya_perolehan',
                'total_perolehan',
                'nilai_residu',
                'umur_manfaat',
                'penyusutan_per_tahun',
                'penyusutan_per_bulan',
                'nilai_buku',
                'tanggal_akuisisi',
                'status',
                'keterangan'
            ]);
        });
    }
};
