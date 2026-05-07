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
        // Get all users to add COA for each
        $users = DB::table('users')->whereIn('role', ['admin', 'owner'])->get();
        
        foreach ($users as $user) {
            // 1. Akun Penjualan (Revenue) - Kode 411
            $existingPenjualan = DB::table('coas')
                ->where('user_id', $user->id)
                ->where('kode_akun', '411')
                ->first();
            
            if (!$existingPenjualan) {
                DB::table('coas')->insert([
                    'user_id' => $user->id,
                    'kode_akun' => '411',
                    'nama_akun' => 'Penjualan',
                    'tipe_akun' => 'Revenue',
                    'kategori' => 'Pendapatan Usaha',
                    'saldo_normal' => 'kredit',
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            
            // 2. Akun PPN Keluaran (Liability) - Kode 211
            $existingPPN = DB::table('coas')
                ->where('user_id', $user->id)
                ->where('kode_akun', '211')
                ->first();
            
            if (!$existingPPN) {
                DB::table('coas')->insert([
                    'user_id' => $user->id,
                    'kode_akun' => '211',
                    'nama_akun' => 'PPN Keluaran',
                    'tipe_akun' => 'Liability',
                    'kategori' => 'Kewajiban Lancar',
                    'saldo_normal' => 'kredit',
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            
            // 3. Akun Harga Pokok Penjualan (Expense) - Kode 554
            $existingHPP = DB::table('coas')
                ->where('user_id', $user->id)
                ->where('kode_akun', '554')
                ->first();
            
            if (!$existingHPP) {
                DB::table('coas')->insert([
                    'user_id' => $user->id,
                    'kode_akun' => '554',
                    'nama_akun' => 'Harga Pokok Penjualan',
                    'tipe_akun' => 'Expense',
                    'kategori' => 'Beban Pokok Penjualan',
                    'saldo_normal' => 'debit',
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            
            // 4. Akun Persediaan Barang Jadi (Asset) - Kode 115
            $existingPersediaan = DB::table('coas')
                ->where('user_id', $user->id)
                ->where('kode_akun', '115')
                ->first();
            
            if (!$existingPersediaan) {
                DB::table('coas')->insert([
                    'user_id' => $user->id,
                    'kode_akun' => '115',
                    'nama_akun' => 'Persediaan Barang Jadi',
                    'tipe_akun' => 'Asset',
                    'kategori' => 'Aset Lancar',
                    'saldo_normal' => 'debit',
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Get all users
        $users = DB::table('users')->whereIn('role', ['admin', 'owner'])->get();
        
        foreach ($users as $user) {
            // Delete the COA entries created by this migration
            DB::table('coas')
                ->where('user_id', $user->id)
                ->whereIn('kode_akun', ['411', '211', '554', '115'])
                ->delete();
        }
    }
};
