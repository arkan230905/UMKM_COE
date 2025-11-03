<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asets', function (Blueprint $table) {
            // Add missing columns only if they don't exist
            if (!Schema::hasColumn('asets', 'kategori_aset_id')) {
                $table->foreignId('kategori_aset_id')->nullable()->after('nama_aset');
            }
            if (!Schema::hasColumn('asets', 'harga_perolehan')) {
                $table->decimal('harga_perolehan', 15, 2)->default(0)->after('kategori_aset_id');
            }
            if (!Schema::hasColumn('asets', 'biaya_perolehan')) {
                $table->decimal('biaya_perolehan', 15, 2)->default(0)->after('harga_perolehan');
            }
            if (!Schema::hasColumn('asets', 'nilai_residu')) {
                $table->decimal('nilai_residu', 15, 2)->default(0)->after('biaya_perolehan');
            }
            if (!Schema::hasColumn('asets', 'umur_manfaat')) {
                $table->integer('umur_manfaat')->default(1)->comment('Dalam tahun')->after('nilai_residu');
            }
            if (!Schema::hasColumn('asets', 'penyusutan_per_tahun')) {
                $table->decimal('penyusutan_per_tahun', 15, 2)->default(0)->after('umur_manfaat');
            }
            if (!Schema::hasColumn('asets', 'penyusutan_per_bulan')) {
                $table->decimal('penyusutan_per_bulan', 15, 2)->default(0)->after('penyusutan_per_tahun');
            }
            if (!Schema::hasColumn('asets', 'nilai_buku')) {
                $table->decimal('nilai_buku', 15, 2)->default(0)->after('penyusutan_per_bulan');
            }
            if (!Schema::hasColumn('asets', 'tanggal_beli')) {
                $table->date('tanggal_beli')->nullable()->after('nilai_buku');
            }
            if (!Schema::hasColumn('asets', 'tanggal_akuisisi')) {
                $table->date('tanggal_akuisisi')->nullable()->after('tanggal_beli');
            }
            if (!Schema::hasColumn('asets', 'status')) {
                $table->string('status', 20)->default('aktif')->after('tanggal_akuisisi');
            }
            if (!Schema::hasColumn('asets', 'keterangan')) {
                $table->text('keterangan')->nullable()->after('status');
            }
            // Drop legacy column 'kategori' if it exists
            if (Schema::hasColumn('asets', 'kategori')) {
                $table->dropColumn('kategori');
            }
        });

        // Add foreign key in a separate statement to avoid issues on some drivers
        Schema::table('asets', function (Blueprint $table) {
            if (Schema::hasColumn('asets', 'kategori_aset_id')) {
                $table->foreign('kategori_aset_id')->references('id')->on('kategori_asets')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('asets', function (Blueprint $table) {
            // Rollback: drop only columns we added if they exist
            $drops = [
                'kategori_aset_id','harga_perolehan','biaya_perolehan','nilai_residu','umur_manfaat',
                'penyusutan_per_tahun','penyusutan_per_bulan','nilai_buku','tanggal_beli','tanggal_akuisisi','status','keterangan'
            ];
            foreach ($drops as $col) {
                if (Schema::hasColumn('asets', $col)) {
                    $table->dropColumn($col);
                }
            }
            // Recreate legacy kategori column (nullable) if desired
            if (!Schema::hasColumn('asets', 'kategori')) {
                $table->string('kategori')->nullable();
            }
        });
    }
};
