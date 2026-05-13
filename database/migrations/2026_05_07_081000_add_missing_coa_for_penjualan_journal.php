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
        // 1. Akun Penjualan (Revenue) - Kode 411
        $existingPenjualan = DB::table('accounts')
            ->where('kode_akun', '411')
            ->first();
        
        if (!$existingPenjualan) {
            DB::table('accounts')->insert([
                'kode_akun' => '411',
                'nama_akun' => 'Penjualan',
                'tipe_akun' => 'Revenue',
                'kategori_akun' => 'Pendapatan',
                'saldo_normal' => 'kredit',
                'saldo_awal' => 0,
                'is_akun_header' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        
        // 2. Akun PPN Keluaran (Liability) - Kode 211
        $existingPPN = DB::table('accounts')
            ->where('kode_akun', '211')
            ->first();
        
        if (!$existingPPN) {
            DB::table('accounts')->insert([
                'kode_akun' => '211',
                'nama_akun' => 'PPN Keluaran',
                'tipe_akun' => 'Liability',
                'kategori_akun' => 'Kewajiban Lancar',
                'saldo_normal' => 'kredit',
                'saldo_awal' => 0,
                'is_akun_header' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        
        // 3. Akun Harga Pokok Penjualan (Expense) - Kode 554
        $existingHPP = DB::table('accounts')
            ->where('kode_akun', '554')
            ->first();
        
        if (!$existingHPP) {
            DB::table('accounts')->insert([
                'kode_akun' => '554',
                'nama_akun' => 'Harga Pokok Penjualan',
                'tipe_akun' => 'Expense',
                'kategori_akun' => 'Beban Pokok Penjualan',
                'saldo_normal' => 'debit',
                'saldo_awal' => 0,
                'is_akun_header' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        
        // 4. Akun Persediaan Barang Jadi (Asset) - Kode 115
        $existingPersediaan = DB::table('accounts')
            ->where('kode_akun', '115')
            ->first();
        
        if (!$existingPersediaan) {
            DB::table('accounts')->insert([
                'kode_akun' => '115',
                'nama_akun' => 'Persediaan Barang Jadi',
                'tipe_akun' => 'Asset',
                'kategori_akun' => 'Aktiva Lancar',
                'saldo_normal' => 'debit',
                'saldo_awal' => 0,
                'is_akun_header' => 0,
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
        // Delete the COA entries created by this migration
        DB::table('accounts')
            ->whereIn('kode_akun', ['411', '211', '554', '115'])
            ->delete();
    }
};
