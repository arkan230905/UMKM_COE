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
        // Hapus duplikasi akun Kas (101) jika ada 1101
        $has1101 = DB::table('coas')->where('kode_akun', '1101')->exists();
        $has101 = DB::table('coas')->where('kode_akun', '101')->exists();
        
        if ($has1101 && $has101) {
            echo "Deleting duplicate account 101 (Kas)...\n";
            DB::table('coas')->where('kode_akun', '101')->delete();
        } elseif ($has101 && !$has1101) {
            echo "Renaming account 101 to 1101...\n";
            DB::table('coas')->where('kode_akun', '101')->update(['kode_akun' => '1101']);
        }
        
        // Hapus akun header yang tidak valid
        DB::table('coas')->where('is_akun_header', 1)
            ->where(function($query) {
                $query->whereNull('nama_akun')
                      ->orWhere('nama_akun', '');
            })
            ->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tidak perlu reverse karena ini cleanup
    }
};
