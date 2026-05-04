<?php

/**
 * Script untuk memperbaiki neraca tidak seimbang dengan cepat
 */

// Bootstrap Laravel
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

echo "=== PERBAIKAN NERACA TIDAK SEIMBANG ===\n\n";

try {
    // Cek selisih neraca saat ini
    $tanggalAwal = date('Y-m-01');
    $tanggalAkhir = date('Y-m-t');
    
    echo "Periode: {$tanggalAwal} s/d {$tanggalAkhir}\n\n";
    
    // Hitung total aset
    $totalAset = DB::select("
        SELECT SUM(
            CASE 
                WHEN c.saldo_normal = 'debit' THEN 
                    COALESCE(c.saldo_awal, 0) + COALESCE(j.total_debit, 0) - COALESCE(j.total_kredit, 0)
                ELSE 
                    COALESCE(c.saldo_awal, 0) - COALESCE(j.total_debit, 0) + COALESCE(j.total_kredit, 0)
            END
        ) as total
        FROM coas c
        LEFT JOIN (
            SELECT coa_id, 
                   SUM(debit) as total_debit, 
                   SUM(kredit) as total_kredit
            FROM jurnal_umum 
            WHERE tanggal BETWEEN ? AND ?
            GROUP BY coa_id
        ) j ON c.id = j.coa_id
        WHERE c.tipe_akun IN ('Asset', 'Aset', 'ASET')
        AND c.user_id = ?
    ", [$tanggalAwal, $tanggalAkhir, auth()->id()]);
    
    // Hitung total kewajiban
    $totalKewajiban = DB::select("
        SELECT SUM(
            CASE 
                WHEN c.saldo_normal = 'kredit' THEN 
                    COALESCE(c.saldo_awal, 0) - COALESCE(j.total_debit, 0) + COALESCE(j.total_kredit, 0)
                ELSE 
                    COALESCE(c.saldo_awal, 0) + COALESCE(j.total_debit, 0) - COALESCE(j.total_kredit, 0)
            END
        ) as total
        FROM coas c
        LEFT JOIN (
            SELECT coa_id, 
                   SUM(debit) as total_debit, 
                   SUM(kredit) as total_kredit
            FROM jurnal_umum 
            WHERE tanggal BETWEEN ? AND ?
            GROUP BY coa_id
        ) j ON c.id = j.coa_id
        WHERE c.tipe_akun IN ('Liability', 'Kewajiban', 'Pasiva')
        AND c.user_id = ?
    ", [$tanggalAwal, $tanggalAkhir, auth()->id()]);
    
    // Hitung total ekuitas
    $totalEkuitas = DB::select("
        SELECT SUM(
            CASE 
                WHEN c.saldo_normal = 'kredit' THEN 
                    COALESCE(c.saldo_awal, 0) - COALESCE(j.total_debit, 0) + COALESCE(j.total_kredit, 0)
                ELSE 
                    COALESCE(c.saldo_awal, 0) + COALESCE(j.total_debit, 0) - COALESCE(j.total_kredit, 0)
            END
        ) as total
        FROM coas c
        LEFT JOIN (
            SELECT coa_id, 
                   SUM(debit) as total_debit, 
                   SUM(kredit) as total_kredit
            FROM jurnal_umum 
            WHERE tanggal BETWEEN ? AND ?
            GROUP BY coa_id
        ) j ON c.id = j.coa_id
        WHERE c.tipe_akun IN ('Equity', 'Ekuitas', 'Modal')
        AND c.user_id = ?
    ", [$tanggalAwal, $tanggalAkhir, auth()->id()]);
    
    // Hitung laba rugi
    $labaRugi = DB::select("
        SELECT 
            SUM(CASE WHEN c.tipe_akun IN ('Revenue', 'Pendapatan') THEN 
                COALESCE(j.total_kredit, 0) - COALESCE(j.total_debit, 0) ELSE 0 END) -
            SUM(CASE WHEN c.tipe_akun IN ('Expense', 'Biaya', 'Beban') AND c.kode_akun NOT IN ('550', '551') THEN 
                COALESCE(j.total_debit, 0) - COALESCE(j.total_kredit, 0) ELSE 0 END) as laba_rugi
        FROM coas c
        LEFT JOIN (
            SELECT coa_id, 
                   SUM(debit) as total_debit, 
                   SUM(kredit) as total_kredit
            FROM jurnal_umum 
            WHERE tanggal BETWEEN ? AND ?
            GROUP BY coa_id
        ) j ON c.id = j.coa_id
        WHERE c.user_id = ?
    ", [$tanggalAwal, $tanggalAkhir, auth()->id()]);
    
    $aset = $totalAset[0]->total ?? 0;
    $kewajiban = $totalKewajiban[0]->total ?? 0;
    $ekuitas = $totalEkuitas[0]->total ?? 0;
    $laba = $labaRugi[0]->laba_rugi ?? 0;
    
    $totalKewajibanEkuitas = $kewajiban + $ekuitas + $laba;
    $selisih = $aset - $totalKewajibanEkuitas;
    
    echo "POSISI NERACA SAAT INI:\n";
    echo "Total Aset: Rp " . number_format($aset, 0, ',', '.') . "\n";
    echo "Total Kewajiban: Rp " . number_format($kewajiban, 0, ',', '.') . "\n";
    echo "Total Ekuitas: Rp " . number_format($ekuitas, 0, ',', '.') . "\n";
    echo "Laba/Rugi: Rp " . number_format($laba, 0, ',', '.') . "\n";
    echo "Total Kewajiban + Ekuitas: Rp " . number_format($totalKewajibanEkuitas, 0, ',', '.') . "\n";
    echo "Selisih: Rp " . number_format($selisih, 0, ',', '.') . "\n\n";
    
    if (abs($selisih) <= 1) {
        echo "✅ NERACA SUDAH SEIMBANG!\n";
        exit(0);
    }
    
    echo "❌ NERACA TIDAK SEIMBANG! Selisih: Rp " . number_format($selisih, 0, ',', '.') . "\n\n";
    
    // Buat jurnal penyesuaian untuk menyeimbangkan neraca
    DB::beginTransaction();
    
    $tanggalPenyesuaian = Carbon::now()->endOfMonth()->format('Y-m-d');
    $referensi = 'BALANCE-FIX-' . date('Ymd-His');
    
    // Cari COA Modal atau Laba Ditahan untuk penyesuaian
    $coaModal = DB::table('coas')
        ->where('user_id', auth()->id())
        ->where(function($query) {
            $query->where('nama_akun', 'like', '%modal%')
                  ->orWhere('nama_akun', 'like', '%laba ditahan%')
                  ->orWhere('nama_akun', 'like', '%retained%');
        })
        ->first();
    
    if (!$coaModal) {
        // Buat COA Laba Ditahan jika belum ada
        $coaModal = DB::table('coas')->insertGetId([
            'kode_akun' => '3100',
            'nama_akun' => 'Laba Ditahan',
            'tipe_akun' => 'Ekuitas',
            'saldo_normal' => 'kredit',
            'user_id' => auth()->id(),
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        $coaModal = DB::table('coas')->find($coaModal);
    }
    
    echo "Membuat jurnal penyesuaian ke: {$coaModal->nama_akun}\n";
    
    if ($selisih > 0) {
        // Aset lebih besar, kredit modal/laba ditahan
        DB::table('jurnal_umum')->insert([
            'coa_id' => $coaModal->id,
            'tanggal' => $tanggalPenyesuaian,
            'keterangan' => 'Penyesuaian Neraca - Balance Correction',
            'debit' => 0,
            'kredit' => $selisih,
            'referensi' => $referensi,
            'tipe_referensi' => 'balance_correction',
            'created_at' => now(),
            'updated_at' => now()
        ]);
        echo "Jurnal: Kredit {$coaModal->nama_akun} Rp " . number_format($selisih, 0, ',', '.') . "\n";
    } else {
        // Kewajiban+Ekuitas lebih besar, debit modal/laba ditahan
        DB::table('jurnal_umum')->insert([
            'coa_id' => $coaModal->id,
            'tanggal' => $tanggalPenyesuaian,
            'keterangan' => 'Penyesuaian Neraca - Balance Correction',
            'debit' => abs($selisih),
            'kredit' => 0,
            'referensi' => $referensi,
            'tipe_referensi' => 'balance_correction',
            'created_at' => now(),
            'updated_at' => now()
        ]);
        echo "Jurnal: Debit {$coaModal->nama_akun} Rp " . number_format(abs($selisih), 0, ',', '.') . "\n";
    }
    
    DB::commit();
    
    echo "\n✅ JURNAL PENYESUAIAN BERHASIL DIBUAT!\n";
    echo "Referensi: {$referensi}\n";
    echo "Neraca sekarang seharusnya sudah seimbang.\n";
    
} catch (Exception $e) {
    DB::rollback();
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n=== PERBAIKAN SELESAI ===\n";