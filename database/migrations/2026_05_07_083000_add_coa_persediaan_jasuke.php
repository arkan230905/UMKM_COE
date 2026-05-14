<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add COA Persediaan Barang Jadi Jasuke (1161)
        $existingJasuke = DB::table('accounts')
            ->where('kode_akun', '1161')
            ->first();
        
        if (!$existingJasuke) {
            DB::table('accounts')->insert([
                'kode_akun' => '1161',
                'nama_akun' => 'Pers. Barang Jadi Jasuke',
                'tipe_akun' => 'Asset',
                'kategori_akun' => 'Aktiva Lancar',
                'saldo_normal' => 'debit',
                'saldo_awal' => 0,
                'is_akun_header' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        
        // Update produk Jasuke untuk menggunakan COA ini (gunakan kode_akun, bukan id)
        DB::table('produks')
            ->where(function($query) {
                $query->where('nama_produk', 'like', '%Jasuke%')
                      ->orWhere('nama_produk', 'like', '%jasuke%')
                      ->orWhere('nama_produk', 'like', '%JASUKE%');
            })
            ->update(['coa_persediaan_id' => '1161']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Delete the COA entry created by this migration
        DB::table('accounts')
            ->where('kode_akun', '1161')
            ->delete();
        
        // Reset coa_persediaan_id for Jasuke products
        DB::table('produks')
            ->where(function($query) {
                $query->where('nama_produk', 'like', '%Jasuke%')
                      ->orWhere('nama_produk', 'like', '%jasuke%')
                      ->orWhere('nama_produk', 'like', '%JASUKE%');
            })
            ->update(['coa_persediaan_id' => null]);
    }
};
