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
            // Change the enum to include both English and Indonesian terms
            $table->enum('tipe_akun', [
                'Asset', 'Aset',
                'Liability', 'Kewajiban', 
                'Equity', 'Ekuitas', 'Modal',
                'Revenue', 'Pendapatan',
                'Expense', 'Beban', 'Biaya'
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
