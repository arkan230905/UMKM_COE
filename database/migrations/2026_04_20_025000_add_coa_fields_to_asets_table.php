<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asets', function (Blueprint $table) {
            // Drop existing columns if they exist to avoid conflicts
            if (Schema::hasColumn('asets', 'nama')) {
                $table->dropColumn('nama');
            }
            if (Schema::hasColumn('asets', 'kategori')) {
                $table->dropColumn('kategori');
            }
            if (Schema::hasColumn('asets', 'harga')) {
                $table->dropColumn('harga');
            }
        });

        Schema::table('asets', function (Blueprint $table) {
            // Basic information
            $table->string('kode_aset')->unique()->after('id');
            $table->string('nama_aset')->after('kode_aset');
            $table->unsignedBigInteger('kategori_aset_id')->nullable()->after('nama_aset');
            $table->decimal('harga_perolehan', 15, 2)->default(0)->after('kategori_aset_id');
            $table->decimal('biaya_perolehan', 15, 2)->default(0)->after('harga_perolehan');
            $table->decimal('nilai_residu', 15, 2)->default(0)->after('biaya_perolehan');
            $table->integer('umur_manfaat')->nullable()->after('nilai_residu');
            
            // Depreciation fields
            $table->decimal('penyusutan_per_tahun', 15, 2)->default(0)->after('umur_manfaat');
            $table->decimal('penyusutan_per_bulan', 15, 2)->default(0)->after('penyusutan_per_tahun');
            $table->decimal('nilai_buku', 15, 2)->default(0)->after('penyusutan_per_bulan');
            $table->string('metode_penyusutan')->default('garis_lurus')->after('nilai_buku');
            $table->decimal('tarif_penyusutan', 5, 2)->default(0)->after('metode_penyusutan');
            $table->integer('bulan_mulai')->nullable()->after('tarif_penyusutan');
            
            // Date fields
            $table->date('tanggal_beli')->nullable()->after('bulan_mulai');
            $table->date('tanggal_akuisisi')->nullable()->after('tanggal_beli');
            $table->date('tanggal_perolehan')->nullable()->after('tanggal_akuisisi');
            
            // Status and other fields
            $table->string('status')->default('aktif')->after('tanggal_perolehan');
            $table->decimal('akumulasi_penyusutan', 15, 2)->default(0)->after('status');
            $table->text('keterangan')->nullable()->after('akumulasi_penyusutan');
            $table->boolean('locked')->default(false)->after('keterangan');
            
            // COA fields for journal integration
            $table->unsignedBigInteger('asset_coa_id')->nullable()->after('locked');
            $table->unsignedBigInteger('accum_depr_coa_id')->nullable()->after('asset_coa_id');
            $table->unsignedBigInteger('expense_coa_id')->nullable()->after('accum_depr_coa_id');
            
            // User tracking
            $table->unsignedBigInteger('created_by')->nullable()->after('expense_coa_id');
            $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');
            
            // Foreign keys
            $table->foreign('kategori_aset_id')->references('id')->on('kategori_asets')->onDelete('set null');
            $table->foreign('asset_coa_id')->references('id')->on('coas')->onDelete('set null');
            $table->foreign('accum_depr_coa_id')->references('id')->on('coas')->onDelete('set null');
            $table->foreign('expense_coa_id')->references('id')->on('coas')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('asets', function (Blueprint $table) {
            // Drop foreign keys first
            $table->dropForeign(['kategori_aset_id']);
            $table->dropForeign(['asset_coa_id']);
            $table->dropForeign(['accum_depr_coa_id']);
            $table->dropForeign(['expense_coa_id']);
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            
            // Drop all new columns
            $table->dropColumn([
                'kode_aset',
                'nama_aset',
                'kategori_aset_id',
                'harga_perolehan',
                'biaya_perolehan',
                'nilai_residu',
                'umur_manfaat',
                'penyusutan_per_tahun',
                'penyusutan_per_bulan',
                'nilai_buku',
                'metode_penyusutan',
                'tarif_penyusutan',
                'bulan_mulai',
                'tanggal_beli',
                'tanggal_akuisisi',
                'tanggal_perolehan',
                'status',
                'akumulasi_penyusutan',
                'keterangan',
                'locked',
                'asset_coa_id',
                'accum_depr_coa_id',
                'expense_coa_id',
                'created_by',
                'updated_by'
            ]);
        });
        
        // Add back original columns
        Schema::table('asets', function (Blueprint $table) {
            $table->string('nama')->after('id');
            $table->string('kategori')->after('nama');
            $table->integer('harga')->after('kategori');
        });
    }
};
