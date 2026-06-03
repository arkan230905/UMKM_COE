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
        // Convert existing English COA types to Indonesian
        DB::table('coas')->where('tipe_akun', 'Asset')->update(['tipe_akun' => 'Aset']);
        DB::table('coas')->where('tipe_akun', 'Liability')->update(['tipe_akun' => 'Kewajiban']);
        DB::table('coas')->where('tipe_akun', 'Equity')->update(['tipe_akun' => 'Modal']);
        DB::table('coas')->where('tipe_akun', 'Revenue')->update(['tipe_akun' => 'Pendapatan']);
        DB::table('coas')->where('tipe_akun', 'Expense')->update(['tipe_akun' => 'Beban']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Convert back to English if needed
        DB::table('coas')->where('tipe_akun', 'Aset')->update(['tipe_akun' => 'Asset']);
        DB::table('coas')->where('tipe_akun', 'Kewajiban')->update(['tipe_akun' => 'Liability']);
        DB::table('coas')->where('tipe_akun', 'Modal')->update(['tipe_akun' => 'Equity']);
        DB::table('coas')->where('tipe_akun', 'Pendapatan')->update(['tipe_akun' => 'Revenue']);
        DB::table('coas')->where('tipe_akun', 'Beban')->update(['tipe_akun' => 'Expense']);
    }
};
