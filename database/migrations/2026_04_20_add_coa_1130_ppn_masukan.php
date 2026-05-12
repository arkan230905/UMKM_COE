<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Coa;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add COA 1130 - PPN Masukan if it doesn't exist
        $exists = Coa::where('kode_akun', '1130')->exists();
        
        if (!$exists) {
            Coa::create([
                'kode_akun' => '1130',
                'nama_akun' => 'PPN Masukan',
                'tipe_akun' => 'Asset',
                'kategori_akun' => 'Asset',
                'is_akun_header' => false,
                'kode_induk' => null,
                'saldo_normal' => 'debit',
                'saldo_awal' => 0.00,
                'tanggal_saldo_awal' => now()->format('Y-m-d H:i:s'),
                'posted_saldo_awal' => false,
                'keterangan' => 'PPN Masukan dari pembelian',
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Delete COA 1130 if it exists
        Coa::where('kode_akun', '1130')->delete();
    }
};
