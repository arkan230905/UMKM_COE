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
        // First, update any existing 'BEBAN' values to 'Expense'
        DB::table('coas')->where('tipe_akun', 'BEBAN')->update(['tipe_akun' => 'Expense']);
        
        // Then update the enum to include all possible values
        Schema::table('coas', function (Blueprint $table) {
            $table->enum('tipe_akun', [
                'Asset', 'Aset',
                'Liability', 'Kewajiban', 
                'Equity', 'Ekuitas', 'Modal',
                'Revenue', 'Pendapatan',
                'Expense', 'Beban', 'Biaya',
                'Biaya Bahan Baku', 'Biaya Tenaga Kerja Langsung', 
                'Biaya Overhead Pabrik', 'Biaya Tenaga Kerja Tidak Langsung', 
                'BOP Tidak Langsung Lainnya'
            ])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('coas', function (Blueprint $table) {
            $table->enum('tipe_akun', [
                'Asset', 'Aset',
                'Liability', 'Kewajiban', 
                'Equity', 'Ekuitas', 'Modal',
                'Revenue', 'Pendapatan',
                'Expense', 'Beban', 'Biaya',
                'Biaya Bahan Baku', 'Biaya Tenaga Kerja Langsung', 
                'Biaya Overhead Pabrik', 'Biaya Tenaga Kerja Tidak Langsung', 
                'BOP Tidak Langsung Lainnya'
            ])->change();
        });
    }
};