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
        // Hapus tabel yang benar-benar tidak digunakan dan aman
        try {
            Schema::dropIfExists('presensis_new'); // Duplikat dari presensis
        } catch (\Exception $e) {
            \Log::info('Tidak bisa hapus presensis_new: ' . $e->getMessage());
        }
        
        try {
            Schema::dropIfExists('pegawai_produk_allocations'); // Tidak digunakan
        } catch (\Exception $e) {
            \Log::info('Tidak bisa hapus pegawai_produk_allocations: ' . $e->getMessage());
        }
        
        try {
            Schema::dropIfExists('favorites'); // Tidak digunakan
        } catch (\Exception $e) {
            \Log::info('Tidak bisa hapus favorites: ' . $e->getMessage());
        }
        
        try {
            Schema::dropIfExists('reviews'); // Tidak digunakan
        } catch (\Exception $e) {
            \Log::info('Tidak bisa hapus reviews: ' . $e->getMessage());
        }
        
        try {
            Schema::dropIfExists('ap_settlements'); // Tidak digunakan
        } catch (\Exception $e) {
            \Log::info('Tidak bisa hapus ap_settlements: ' . $e->getMessage());
        }
        
        try {
            Schema::dropIfExists('coa_period_balances'); // Tidak digunakan
        } catch (\Exception $e) {
            \Log::info('Tidak bisa hapus coa_period_balances: ' . $e->getMessage());
        }
        
        try {
            Schema::dropIfExists('coa_periods'); // Tidak digunakan
        } catch (\Exception $e) {
            \Log::info('Tidak bisa hapus coa_periods: ' . $e->getMessage());
        }
        
        try {
            Schema::dropIfExists('bop_budgets'); // Tidak digunakan
        } catch (\Exception $e) {
            \Log::info('Tidak bisa hapus bop_budgets: ' . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
