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
        Schema::table('bop_lainnyas', function (Blueprint $table) {
            // Drop old columns if they exist
            if (Schema::hasColumn('bop_lainnyas', 'nama_bop')) {
                $table->dropColumn('nama_bop');
            }
            if (Schema::hasColumn('bop_lainnyas', 'kategori')) {
                $table->dropColumn('kategori');
            }
            
            // Add new columns
            if (!Schema::hasColumn('bop_lainnyas', 'kode_akun')) {
                $table->string('kode_akun')->after('id');
            }
            if (!Schema::hasColumn('bop_lainnyas', 'nama_akun')) {
                $table->string('nama_akun')->after('kode_akun');
            }
            if (!Schema::hasColumn('bop_lainnyas', 'metode_pembebanan')) {
                $table->enum('metode_pembebanan', ['unit_produksi', 'jam_mesin', 'biaya_bahan', 'equal_distribution'])->after('periode');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bop_lainnyas', function (Blueprint $table) {
            // Restore old columns
            if (!Schema::hasColumn('bop_lainnyas', 'nama_bop')) {
                $table->string('nama_bop')->after('id');
            }
            if (!Schema::hasColumn('bop_lainnyas', 'kategori')) {
                $table->enum('kategori', ['overhead_pabrik', 'overhead_kantor', 'biaya_umum', 'lainnya'])->after('nama_bop');
            }
            
            // Drop new columns
            if (Schema::hasColumn('bop_lainnyas', 'kode_akun')) {
                $table->dropColumn('kode_akun');
            }
            if (Schema::hasColumn('bop_lainnyas', 'nama_akun')) {
                $table->dropColumn('nama_akun');
            }
            if (Schema::hasColumn('bop_lainnyas', 'metode_pembebanan')) {
                $table->dropColumn('metode_pembebanan');
            }
        });
    }
};