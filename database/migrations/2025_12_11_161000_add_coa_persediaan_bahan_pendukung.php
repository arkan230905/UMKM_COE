<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Cek apakah COA 1105 sudah ada
        $exists = DB::table('accounts')->where('kode_akun', '1105')->exists();
        
        if (!$exists) {
            DB::table('accounts')->insert([
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
    }

    public function down(): void
    {
        DB::table('accounts')->where('kode_akun', '1105')->delete();
    }
};
