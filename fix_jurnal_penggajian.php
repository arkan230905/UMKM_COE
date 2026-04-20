<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Penggajian;
use App\Models\JurnalUmum;
use App\Models\Coa;
use Illuminate\Support\Facades\DB;

echo "=== FIXING PENGGAJIAN JOURNAL ENTRIES ===\n";

try {
    DB::beginTransaction();
    
    // Get all penggajian that haven't been posted to journal
    $penggajianList = Penggajian::where('status_pembayaran', 'belum_lunas')->get();
    
    echo "Found " . $penggajianList->count() . " unpaid penggajian records\n";
    
    foreach ($penggajianList as $penggajian) {
        echo "\nProcessing Penggajian ID: {$penggajian->id}\n";
        echo "Date: {$penggajian->tanggal_penggajian}\n";
        echo "Total Gaji: " . number_format($penggajian->total_gaji, 0, ',', '.') . "\n";
        
        // Get pegawai info
        $pegawai = $penggajian->pegawai;
        if (!$pegawai) {
            echo "ERROR: Pegawai not found for penggajian ID {$penggajian->id}\n";
            continue;
        }
        
        echo "Pegawai: {$pegawai->nama}\n";
        
        // Check if journal entries already exist
        $existingJournal = JurnalUmum::where('tipe_referensi', 'penggajian')
            ->where('referensi', $penggajian->id)
            ->exists();
            
        if ($existingJournal) {
            echo "Journal entries already exist, skipping...\n";
            continue;
        }
        
        // Get required COA accounts
        $coaBebanGaji = Coa::where('kode_akun', '52')->first(); // BTKL
        if (!$coaBebanGaji) {
            $coaBebanGaji = Coa::where('kode_akun', '54')->first(); // BOP TENAGA KERJA TIDAK LANGSUNG
        }
        
        $coaKasBank = Coa::where('kode_akun', $penggajian->coa_kasbank)->first();
        
        if (!$coaBebanGaji) {
            echo "ERROR: COA Beban Gaji not found\n";
            continue;
        }
        
        if (!$coaKasBank) {
            echo "ERROR: COA Kas/Bank not found for code: {$penggajian->coa_kasbank}\n";
            continue;
        }
        
        echo "Using Beban Account: {$coaBebanGaji->kode_akun} - {$coaBebanGaji->nama_akun}\n";
        echo "Using Kas/Bank Account: {$coaKasBank->kode_akun} - {$coaKasBank->nama_akun}\n";
        
        // Create journal entries
        $keterangan = "Penggajian {$pegawai->nama}";
        
        // DEBIT: Beban Gaji
        JurnalUmum::create([
            'coa_id' => $coaBebanGaji->id,
            'tanggal' => $penggajian->tanggal_penggajian,
            'keterangan' => $keterangan,
            'debit' => $penggajian->total_gaji,
            'kredit' => 0,
            'referensi' => $penggajian->id,
            'tipe_referensi' => 'penggajian',
            'created_by' => 1,
        ]);
        
        // CREDIT: Kas/Bank
        JurnalUmum::create([
            'coa_id' => $coaKasBank->id,
            'tanggal' => $penggajian->tanggal_penggajian,
            'keterangan' => $keterangan,
            'debit' => 0,
            'kredit' => $penggajian->total_gaji,
            'referensi' => $penggajian->id,
            'tipe_referensi' => 'penggajian',
            'created_by' => 1,
        ]);
        
        // Update penggajian status
        $penggajian->status_pembayaran = 'lunas';
        $penggajian->tanggal_dibayar = $penggajian->tanggal_penggajian;
        $penggajian->save();
        
        echo "✓ Journal entries created successfully\n";
        echo "✓ Penggajian marked as paid\n";
    }
    
    DB::commit();
    echo "\n=== ALL DONE SUCCESSFULLY ===\n";
    
    // Show summary
    $totalJurnalPenggajian = JurnalUmum::where('tipe_referensi', 'penggajian')->count();
    echo "Total penggajian journal entries now: {$totalJurnalPenggajian}\n";
    
} catch (Exception $e) {
    DB::rollBack();
    echo "\nERROR: " . $e->getMessage() . "\n";
    echo "Transaction rolled back.\n";
}