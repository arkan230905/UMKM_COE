<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Cek apakah COA 1105 sudah ada
        $exists = DB::table('coas')->where('kode_akun', '1105')->exists();
        
        if (!$exists) {
            DB::table('coas')->insert([
                'kode_akun' => '1105',
                'nama_akun' => 'Persediaan Bahan Pendukung',
                'tipe_akun' => 'Asset',
                'kategori_akun' => 'Aset Lancar',
                'saldo_normal' => 'debit',
                'saldo_awal' => 0,
                'is_akun_header' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        
        // Cek apakah Account 1105 sudah ada
        $accExists = DB::table('accounts')->where('code', '1105')->exists();
        
        if (!$accExists) {
            DB::table('accounts')->insert([
                'code' => '1105',
                'name' => 'Persediaan Bahan Pendukung',
                'type' => 'asset',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('coas')->where('kode_akun', '1105')->delete();
        DB::table('accounts')->where('code', '1105')->delete();
    }
};
