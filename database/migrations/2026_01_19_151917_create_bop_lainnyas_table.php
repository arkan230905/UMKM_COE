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
            // Drop old columns
            $table->dropColumn(['nama_bop', 'kategori']);
            
            // Add new columns
            $table->string('kode_akun')->after('id');
            $table->string('nama_akun')->after('kode_akun');
            $table->enum('metode_pembebanan', ['unit_produksi', 'jam_mesin', 'biaya_bahan', 'equal_distribution'])->after('periode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bop_lainnyas', function (Blueprint $table) {
            // Restore old columns
            $table->string('nama_bop')->after('id');
            $table->enum('kategori', ['overhead_pabrik', 'overhead_kantor', 'biaya_umum', 'lainnya'])->after('nama_bop');
            
            // Drop new columns
            $table->dropColumn(['kode_akun', 'nama_akun', 'metode_pembebanan']);
        });
    }
};