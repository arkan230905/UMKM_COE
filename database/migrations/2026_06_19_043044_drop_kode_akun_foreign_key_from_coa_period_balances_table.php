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
        // Drop foreign key constraint using raw SQL
        // kode_akun di coa_period_balances tidak perlu merujuk ke accounts
        // karena accounts hanya master global, sementara coa_period_balances bisa menyimpan 
        // kode_akun dari coas yang user-specific
        
        try {
            DB::statement('ALTER TABLE coa_period_balances DROP FOREIGN KEY coa_period_balances_kode_akun_foreign');
        } catch (\Exception $e) {
            // Foreign key mungkin sudah di-drop atau tidak ada
            \Log::info('Foreign key drop attempt: ' . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tidak perlu restore foreign key karena itu bukan solusi jangka panjang
        // Solusi yang tepat adalah menyimpan kode_akun sebagai string saja
    }
};
