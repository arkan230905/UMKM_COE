<?php

/**
 * Script untuk memperbaiki jurnal umum April 2026 secara langsung
 * Mengganti nilai yang salah dengan nilai yang benar
 */

require_once 'vendor/autoload.php';

use App\Models\JurnalUmum;
use App\Models\Coa;
use Illuminate\Support\Facades\DB;

echo "=== PERBAIKAN LANGSUNG JURNAL UMUM APRIL 2026 ===\n\n";

// Data yang perlu diperbaiki
$corrections = [
    [
        'nama_aset' => 'Mesin Produksi',
        'old_amount' => 1416667,
        'new_amount' => 1333333,
        'expense_coa' => '555',
        'accum_coa' => '126',
        'keywords' => ['Mesin', 'Produksi']
    ],
    [
        'nama_aset' => 'Peralatan Produksi', 
        'old_amount' => 2833333,
        'new_amount' => 659474,
        'expense_coa' => '553',
        'accum_coa' => '120',
        'keywords' => ['Peralatan', 'Produksi']
    ],
    [
        'nama_aset' => 'Kendaraan',
        'old_amount' => 2361111,
        'new_amount' => 888889,
        'expense_coa' => '554',
        'accum_coa' => '124',
        'keywords' => ['Kendaraan']
    ]
];

echo "LANGKAH 1: IDENTIFIKASI JURNAL YANG PERLU DIPERBAIKI\n\n";

$targetDate = '2026-04-30';
$jurnalsToFix = [];

foreach ($corrections as $correction) {
    echo "Mencari jurnal untuk {$correction['nama_aset']}...\n";
    
    // Cari jurnal berdasarkan tanggal, keterangan, dan nominal
    $journals = JurnalUmum::where('tanggal', $targetDate)
        ->where('keterangan', 'like', '%Penyusutan%')
        ->where(function($query) use ($correction) {
            foreach ($correction['keywords'] as $keyword) {
                $query->orWhere('keterangan', 'like', "%{$keyword}%");
            }
        })
        ->where(function($query) use ($correction) {
            $query->where('debit', $correction['old_amount'])
                  ->orWhere('kredit', $correction['old_amount']);
        })
        ->get();
    
    if ($journals->count() > 0) {
        echo "  Ditemukan {$journals->count()} jurnal dengan nilai lama: Rp " . number_format($correction['old_amount'], 0, ',', '.') . "\n";
        $jurnalsToFix[$correction['nama_aset']] = [
            'journals' => $journals,
            'correction' => $correction
        ];
    } else {
        echo "  ⚠ Tidak ditemukan jurnal dengan nilai lama\n";
    }
    
    echo "\n";
}

if (empty($jurnalsToFix)) {
    echo "Tidak ada jurnal yang perlu diperbaiki.\n";
    exit;
}

echo "LANGKAH 2: BACKUP JURNAL LAMA\n\n";

// Buat backup
$backupData = [];
foreach ($jurnalsToFix as $assetName => $data) {
    foreach ($data['journals'] as $journal) {
        $backupData[] = [
            'id' => $journal->id,
            'coa_id' => $journal->coa_id,
            'tanggal' => $journal->tanggal,
            'keterangan' => $journal->keterangan,
            'debit' => $journal->debit,
            'kredit' => $journal->kredit,
            'referensi' => $journal->referensi,
            'tipe_referensi' => $journal->tipe_referensi,
            'asset_name' => $assetName
        ];
    }
}

// Simpan backup ke file
file_put_contents('backup_jurnal_april_2026_' . date('Y-m-d_H-i-s') . '.json', json_encode($backupData, JSON_PRETTY_PRINT));
echo "Backup disimpan ke: backup_jurnal_april_2026_" . date('Y-m-d_H-i-s') . ".json\n\n";

echo "LANGKAH 3: UPDATE JURNAL DENGAN NILAI YANG BENAR\n\n";

DB::beginTransaction();

try {
    foreach ($jurnalsToFix as $assetName => $data) {
        $correction = $data['correction'];
        $journals = $data['journals'];
        
        echo "Memperbaiki jurnal {$assetName}...\n";
        
        foreach ($journals as $journal) {
            $oldDebit = (float)$journal->debit;
            $oldKredit = (float)$journal->kredit;
            
            // Update nilai
            if ($oldDebit == $correction['old_amount']) {
                // Ini jurnal debit (beban penyusutan)
                $journal->update(['debit' => $correction['new_amount']]);
                echo "  Updated debit: Rp " . number_format($correction['old_amount'], 0, ',', '.') . 
                     " → Rp " . number_format($correction['new_amount'], 0, ',', '.') . "\n";
            }
            
            if ($oldKredit == $correction['old_amount']) {
                // Ini jurnal kredit (akumulasi penyusutan)
                $journal->update(['kredit' => $correction['new_amount']]);
                echo "  Updated kredit: Rp " . number_format($correction['old_amount'], 0, ',', '.') . 
                     " → Rp " . number_format($correction['new_amount'], 0, ',', '.') . "\n";
            }
        }
        
        echo "\n";
    }
    
    DB::commit();
    echo "✓ Semua jurnal berhasil diperbaiki!\n\n";
    
} catch (Exception $e) {
    DB::rollback();
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "Transaksi dibatalkan, data tidak berubah.\n\n";
    exit;
}

echo "LANGKAH 4: VALIDASI HASIL\n\n";

foreach ($corrections as $correction) {
    echo "Validasi {$correction['nama_aset']}:\n";
    
    $validationJournals = JurnalUmum::where('tanggal', $targetDate)
        ->where('keterangan', 'like', '%Penyusutan%')
        ->where(function($query) use ($correction) {
            foreach ($correction['keywords'] as $keyword) {
                $query->orWhere('keterangan', 'like', "%{$keyword}%");
            }
        })
        ->with('coa')
        ->get();
    
    foreach ($validationJournals as $journal) {
        $amount = $journal->debit > 0 ? $journal->debit : $journal->kredit;
        $type = $journal->debit > 0 ? 'Debit' : 'Kredit';
        
        echo "  {$type} - {$journal->coa->nama_akun}: Rp " . number_format($amount, 0, ',', '.') . "\n";
        
        if ($amount == $correction['new_amount']) {
            echo "    ✓ Nilai sudah benar\n";
        } else {
            echo "    ✗ Nilai masih salah (seharusnya: Rp " . number_format($correction['new_amount'], 0, ',', '.') . ")\n";
        }
    }
    
    echo "\n";
}

echo "LANGKAH 5: UPDATE AKUMULASI PENYUSUTAN DI TABEL ASET\n\n";

// Update akumulasi penyusutan berdasarkan jurnal yang sudah diperbaiki
foreach ($corrections as $correction) {
    echo "Update akumulasi penyusutan {$correction['nama_aset']}...\n";
    
    // Hitung total akumulasi dari jurnal
    $totalAccumulated = JurnalUmum::join('coa', 'jurnal_umum.coa_id', '=', 'coa.id')
        ->where('coa.kode_akun', $correction['expense_coa'])
        ->where('jurnal_umum.keterangan', 'like', '%Penyusutan%')
        ->where('jurnal_umum.debit', '>', 0)
        ->sum('jurnal_umum.debit');
    
    echo "  Total akumulasi dari jurnal: Rp " . number_format($totalAccumulated, 0, ',', '.') . "\n";
    
    // Update tabel aset
    $updated = DB::table('asets')
        ->where(function($query) use ($correction) {
            foreach ($correction['keywords'] as $keyword) {
                $query->orWhere('nama_aset', 'like', "%{$keyword}%");
            }
        })
        ->update([
            'akumulasi_penyusutan' => $totalAccumulated,
            'penyusutan_per_bulan' => $correction['new_amount'],
            'penyusutan_per_tahun' => $correction['new_amount'] * 12
        ]);
    
    if ($updated > 0) {
        echo "  ✓ {$updated} aset berhasil diupdate\n";
    } else {
        echo "  ⚠ Tidak ada aset yang diupdate\n";
    }
    
    echo "\n";
}

echo "=== PERBAIKAN SELESAI ===\n\n";

echo "RINGKASAN PERUBAHAN:\n";
foreach ($corrections as $correction) {
    $selisih = $correction['old_amount'] - $correction['new_amount'];
    echo "- {$correction['nama_aset']}: ";
    echo "Rp " . number_format($correction['old_amount'], 0, ',', '.') . " → ";
    echo "Rp " . number_format($correction['new_amount'], 0, ',', '.') . " ";
    echo "(selisih: -Rp " . number_format($selisih, 0, ',', '.') . ")\n";
}

$totalOld = array_sum(array_column($corrections, 'old_amount'));
$totalNew = array_sum(array_column($corrections, 'new_amount'));
$totalDifference = $totalOld - $totalNew;

echo "\nTOTAL KOREKSI: -Rp " . number_format($totalDifference, 0, ',', '.') . "\n";

echo "\nCATATAN:\n";
echo "- Backup jurnal lama tersimpan dalam file JSON\n";
echo "- Jurnal umum sudah diperbaiki dengan nilai yang benar\n";
echo "- Akumulasi penyusutan di tabel aset sudah diupdate\n";
echo "- Silakan cek kembali jurnal umum di aplikasi\n\n";

echo "Jika masih ada masalah, restore dari backup dan hubungi developer.\n";