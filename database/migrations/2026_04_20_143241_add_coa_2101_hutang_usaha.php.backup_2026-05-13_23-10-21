<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if COA 2101 already exists
        $exists = DB::table('coas')->where('kode_akun', '2101')->exists();
        
        if (!$exists) {
            DB::table('coas')->insert([
                'kode_akun' => '2101',
                'nama_akun' => 'Hutang Usaha',
                'tipe_akun' => 'Liability',
                'kategori_akun' => 'Liability',
                'is_akun_header' => false,
                'kode_induk' => null,
                'saldo_normal' => 'debit',
                'saldo_awal' => 0.00,
                'tanggal_saldo_awal' => now(),
                'posted_saldo_awal' => false,
                'keterangan' => 'Hutang Usaha - Digunakan untuk jurnal pembelian kredit',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('coas')->where('kode_akun', '2101')->delete();
    }
};
