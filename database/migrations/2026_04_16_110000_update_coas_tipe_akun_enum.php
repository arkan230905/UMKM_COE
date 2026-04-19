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
        Schema::table('coas', function (Blueprint $table) {
            $table->enum('tipe_akun', [
                'Asset', 'Aset',
                'Liability', 'Kewajiban', 
                'Equity', 'Modal',
                'Revenue', 'Pendapatan',
                'Expense', 'Biaya Bahan Baku', 'Biaya Tenaga Kerja Langsung', 
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
            $table->enum('tipe_akun', ['Asset','Liability','Equity','Revenue','Expense'])->change();
        });
    }
};
