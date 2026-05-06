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
        // Add missing COA accounts for HPP functionality
        // IMPORTANT: Check if COA already exists before inserting
        
        // Get all users
        $users = DB::table('users')->get();
        
        foreach ($users as $user) {
            // COA 56 - Harga Pokok Penjualan
            $this->createCoaIfNotExists([
                'kode_akun' => '56',
                'nama_akun' => 'Harga Pokok Penjualan',
                'tipe_akun' => 'Biaya',
                'kategori_akun' => 'Biaya',
                'saldo_awal' => 0,
                'saldo_normal' => 'Debit',
                'user_id' => $user->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            // COA 552 - HPP (general)
            $this->createCoaIfNotExists([
                'kode_akun' => '552',
                'nama_akun' => 'HPP',
                'tipe_akun' => 'Biaya',
                'kategori_akun' => 'Biaya',
                'saldo_awal' => 0,
                'saldo_normal' => 'Debit',
                'user_id' => $user->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        
        echo "Added missing HPP COA accounts for all users\n";
    }
    
    private function createCoaIfNotExists($coaData)
    {
        // Use updateOrInsert to avoid duplicate key error
        DB::table('coas')->updateOrInsert(
            [
                'kode_akun' => $coaData['kode_akun'],
                'user_id' => $coaData['user_id']
            ],
            $coaData
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove the created COA accounts
        DB::table('coas')
            ->whereIn('kode_akun', ['56', '552'])
            ->where('nama_akun', 'Harga Pokok Penjualan')
            ->orWhere('nama_akun', 'HPP')
            ->delete();
    }
};
