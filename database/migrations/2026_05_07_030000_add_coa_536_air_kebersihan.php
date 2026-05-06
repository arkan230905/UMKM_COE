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
        // Add COA 536 - BOP Air & Kebersihan for all users
        $users = DB::table('users')->get();
        
        foreach ($users as $user) {
            // Use updateOrInsert to avoid duplicate key error
            DB::table('coas')->updateOrInsert(
                [
                    'kode_akun' => '536',
                    'user_id' => $user->id
                ],
                [
                    'kode_akun' => '536',
                    'nama_akun' => 'BOP Air & Kebersihan',
                    'tipe_akun' => 'Biaya',
                    'kategori_akun' => 'BOP',
                    'saldo_normal' => 'Debit',
                    'posisi' => 'Debit',
                    'saldo_awal' => 0,
                    'user_id' => $user->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
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
