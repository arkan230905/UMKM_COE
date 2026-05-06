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
        // Add COA 536 - BOP Air & Kebersihan for all users
        $users = DB::table('users')->get();
        
        foreach ($users as $user) {
            // Check if COA 536 already exists for this user
            $exists = DB::table('coas')
                ->where('kode_akun', '536')
                ->where('user_id', $user->id)
                ->exists();
            
            if (!$exists) {
                DB::table('coas')->insert([
                    'kode_akun' => '536',
                    'nama_akun' => 'BOP Air & Kebersihan',
                    'tipe_akun' => 'Biaya',
                    'kategori_akun' => 'BOP',
                    'saldo_normal' => 'debit',
                    'posisi' => 'Debit',
                    'saldo_awal' => 0,
                    'user_id' => $user->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                \Log::info("COA 536 added for user {$user->id}");
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove COA 536 from all users
        DB::table('coas')
            ->where('kode_akun', '536')
            ->delete();
    }
};
