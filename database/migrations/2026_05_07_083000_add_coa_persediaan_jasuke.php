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
            // Add COA Persediaan Barang Jadi Jasuke (1161)
            $existingJasuke = DB::table('coas')
                ->where('user_id', $user->id)
                ->where('kode_akun', '1161')
                ->first();
            
            if (!$existingJasuke) {
                DB::table('coas')->insert([
                    'user_id' => $user->id,
                    'kode_akun' => '1161',
                    'nama_akun' => 'Pers. Barang Jadi Jasuke',
                    'tipe_akun' => 'Asset',
                    'kategori' => 'Aset Lancar',
                    'saldo_normal' => 'debit',
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            
            // Update produk Jasuke untuk menggunakan COA ini
            $coaJasuke = DB::table('coas')
                ->where('user_id', $user->id)
                ->where('kode_akun', '1161')
                ->first();
            
            if ($coaJasuke) {
                // Update produk yang namanya mengandung "Jasuke" atau "jasuke"
                DB::table('produks')
                    ->where('user_id', $user->id)
                    ->where(function($query) {
                        $query->where('nama_produk', 'like', '%Jasuke%')
                              ->orWhere('nama_produk', 'like', '%jasuke%')
                              ->orWhere('nama_produk', 'like', '%JASUKE%');
                    })
                    ->update(['coa_persediaan_id' => $coaJasuke->id]);
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
            // Delete the COA entry created by this migration
            DB::table('coas')
                ->where('user_id', $user->id)
                ->where('kode_akun', '1161')
                ->delete();
            
            // Reset coa_persediaan_id for Jasuke products
            DB::table('produks')
                ->where('user_id', $user->id)
                ->where(function($query) {
                    $query->where('nama_produk', 'like', '%Jasuke%')
                          ->orWhere('nama_produk', 'like', '%jasuke%')
                          ->orWhere('nama_produk', 'like', '%JASUKE%');
                })
                ->update(['coa_persediaan_id' => null]);
        }
    }
};
