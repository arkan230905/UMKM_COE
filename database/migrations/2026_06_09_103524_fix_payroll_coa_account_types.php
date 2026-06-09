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
     * Fix tipe akun untuk akun-akun beban penggajian yang salah tercatat sebagai "Modal"
     * Seharusnya: Biaya/Beban
     */
    public function up(): void
    {
        // Update tipe akun untuk akun-akun beban penggajian yang spesifik
        // Hanya update yang nama akunnya mengandung kata kunci beban penggajian
        $payrollMappings = [
            '513' => 'Beban Tunjangan',
            '514' => 'Beban Asuransi', 
            '515' => 'Beban Bonus',
            '516' => 'Potongan Gaji'
        ];
        
        foreach ($payrollMappings as $kodeAkun => $namaAkun) {
            $updated = DB::table('coas')
                ->where('kode_akun', $kodeAkun)
                ->where('nama_akun', $namaAkun)
                ->whereIn('tipe_akun', ['Modal', 'modal', 'MODAL', 'Equity', 'equity', 'EQUITY'])
                ->update([
                    'tipe_akun' => 'Beban',
                    'updated_at' => now()
                ]);
            
            if ($updated > 0) {
                \Log::info("Fixed COA account type: {$kodeAkun} - {$namaAkun} => Beban");
            }
        }
    }

    /**
     * Reverse the migrations.
     * 
     * Kembalikan ke kondisi sebelumnya (untuk rollback)
     */
    public function down(): void
    {
        // Rollback: kembalikan ke Equity (kondisi sebelumnya)
        $payrollMappings = [
            '513' => 'Beban Tunjangan',
            '514' => 'Beban Asuransi', 
            '515' => 'Beban Bonus',
            '516' => 'Potongan Gaji'
        ];
        
        foreach ($payrollMappings as $kodeAkun => $namaAkun) {
            DB::table('coas')
                ->where('kode_akun', $kodeAkun)
                ->where('nama_akun', $namaAkun)
                ->where('tipe_akun', 'Beban')
                ->update([
                    'tipe_akun' => 'Equity',
                    'updated_at' => now()
                ]);
        }
    }
};
