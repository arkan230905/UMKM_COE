<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * CLEANUP: Hapus entry depreciation yang salah untuk aset "Saldo Awal (Aset Lama)"
     * 
     * Aset jenis "Saldo Awal" seharusnya TIDAK punya entry ke Jurnal Umum
     * (penyusutan hanya di Jurnal Penyesuaian)
     * 
     * Entry yang dihapus: 
     * - Debit akun 555 (BOP - Beban Penyusutan) 
     * - Kredit akun 126 (Akumulasi Penyusutan)
     * - Keterangan: "Beban Penyusutan - Mesin produksi"
     * - Tipe referensi: depreciation atau adjustment_depreciation
     */
    public function up(): void
    {
        // Step 1: Identify asets with jenis_perolehan = 'saldo_awal'
        $saldoAwalAsets = DB::table('asets')
            ->where('jenis_perolehan', 'saldo_awal')
            ->pluck('id')
            ->toArray();

        if (empty($saldoAwalAsets)) {
            return; // Tidak ada aset saldo awal
        }

        // Step 2: Get COA IDs untuk akun 555 dan 126
        $coa555 = DB::table('coas')->where('kode_akun', '555')->first();
        $coa126 = DB::table('coas')->where('kode_akun', '126')->first();

        if (!$coa555 || !$coa126) {
            // Jika COA tidak ditemukan dengan kode spesifik, coba dengan nama
            $coa555 = DB::table('coas')
                ->where('nama_akun', 'like', '%Beban Penyusutan%')
                ->orWhere('nama_akun', 'like', '%BOP%')
                ->first();
            
            $coa126 = DB::table('coas')
                ->where('nama_akun', 'like', '%Akumulasi Penyusutan%')
                ->first();
        }

        // Step 3: Delete incorrect entries
        // Hapus entries yang terkait dengan aset saldo_awal
        foreach ($saldoAwalAsets as $asetId) {
            DB::table('jurnal_umum')
                ->where('tipe_referensi', 'depreciation')
                ->where('referensi', 'ASET-' . $asetId)
                ->delete();

            DB::table('jurnal_umum')
                ->where('tipe_referensi', 'adjustment_depreciation')
                ->where('referensi', 'ASET-' . $asetId)
                ->orWhere(function($q) use ($asetId) {
                    $q->where('keterangan', 'like', '%Beban Penyusutan%')
                      ->where('referensi', 'like', '%ASET-' . $asetId . '%');
                })
                ->delete();
        }

        // Step 4: Optional - Delete entries with specific keterangan if they exist
        DB::table('jurnal_umum')
            ->where('keterangan', 'like', '%Beban Penyusutan - Mesin%')
            ->where(function($q) {
                $q->where('tipe_referensi', 'depreciation')
                  ->orWhere('tipe_referensi', 'adjustment_depreciation');
            })
            ->delete();
    }

    /**
     * Reverse the migrations.
     * 
     * NOTE: This is a one-way cleanup. Data cannot be automatically restored.
     * If you need to restore, you must manually re-enter the correct depreciation entries.
     */
    public function down(): void
    {
        // No rollback - this is a cleanup operation
        // Manual restoration would be required
    }
};
