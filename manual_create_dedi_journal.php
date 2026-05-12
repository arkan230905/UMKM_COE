<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Manual Create Dedi Gunawan Journal ===" . PHP_EOL;

// Get Dedi Gunawan's penggajian data
echo PHP_EOL . "Mendapatkan data penggajian Dedi Gunawan..." . PHP_EOL;

$penggajian = DB::table('penggajians')
    ->join('pegawais', 'penggajians.pegawai_id', '=', 'pegawais.id')
    ->join('jabatans', 'pegawais.jabatan_id', '=', 'jabatans.id')
    ->where('pegawais.nama', 'like', '%Dedi Gunawan%')
    ->whereMonth('penggajians.tanggal_penggajian', 4)
    ->whereYear('penggajians.tanggal_penggajian', 2026)
    ->select('penggajians.*', 'pegawais.nama as nama_pegawai', 'pegawais.jabatan', 'pegawais.jenis_pegawai', 'jabatans.nama as nama_jabatan', 'jabatans.kategori as kategori_jabatan')
    ->first();

if ($penggajian) {
    echo "Data Penggajian Dedi Gunawan:" . PHP_EOL;
    echo "- ID: " . $penggajian->id . PHP_EOL;
    echo "- Tanggal: " . $penggajian->tanggal_penggajian . PHP_EOL;
    echo "- Total Gaji: Rp " . number_format($penggajian->total_gaji, 0) . PHP_EOL;
    echo "- Status: " . $penggajian->status_pembayaran . PHP_EOL;
    echo "- Nama: " . $penggajian->nama_pegawai . PHP_EOL;
    echo "- Jabatan: " . $penggajian->jabatan . PHP_EOL;
    echo "- Nama Jabatan: " . $penggajian->nama_jabatan . PHP_EOL;
    echo "- Jenis Pegawai: " . $penggajian->jenis_pegawai . PHP_EOL;
    echo "- Kategori Jabatan: " . $penggajian->kategori_jabatan . PHP_EOL;
    
    // Simulate the logic for COA selection
    $jenisPegawai = strtolower($penggajian->kategori_jabatan ?? $penggajian->jenis_pegawai ?? 'btktl');
    
    if (strpos(strtolower($penggajian->nama_jabatan), 'gudang') !== false) {
        $coaKode = '54'; // BTKL untuk Bagian Gudang
        $coaNama = 'BIAYA TENAGA KERJA TIDAK LANGSUNG';
        echo PHP_EOL . "Logic: Bagian Gudang -> COA " . $coaKode . " (" . $coaNama . ")" . PHP_EOL;
    } else if ($jenisPegawai === 'btkl') {
        $coaKode = '52'; // BTKL untuk lainnya
        $coaNama = 'BIAYA TENAGA KERJA LANGSUNG';
        echo PHP_EOL . "Logic: BTKL -> COA " . $coaKode . " (" . $coaNama . ")" . PHP_EOL;
    } else {
        $coaKode = '54'; // BTKTL untuk lainnya
        $coaNama = 'BIAYA TENAGA KERJA TIDAK LANGSUNG';
        echo PHP_EOL . "Logic: BTKTL -> COA " . $coaKode . " (" . $coaNama . ")" . PHP_EOL;
    }
    
    // Get COA objects
    $coaBebanGaji = DB::table('coas')->where('kode_akun', $coaKode)->first();
    $coaKasBank = DB::table('coas')->where('kode_akun', $penggajian->coa_kasbank)->first();
    
    if ($coaBebanGaji && $coaKasBank) {
        echo PHP_EOL . "COA Objects:" . PHP_EOL;
        echo "- COA Beban: " . $coaBebanGaji->kode_akun . " - " . $coaBebanGaji->nama_akun . PHP_EOL;
        echo "- COA Kas: " . $coaKasBank->kode_akun . " - " . $coaKasBank->nama_akun . PHP_EOL;
        
        echo PHP_EOL . "=== Manual Create Journal ===" . PHP_EOL;
        
        try {
            // Create journal entry
            $journalEntry = \App\Models\JournalEntry::create([
                'tanggal' => $penggajian->tanggal_penggajian,
                'ref_type' => 'penggajian',
                'ref_id' => $penggajian->id,
                'memo' => "Penggajian " . $penggajian->nama_pegawai,
            ]);
            
            echo "Journal Entry Created: ID " . $journalEntry->id . PHP_EOL;
            
            // Create journal lines
            // DEBIT: Beban Gaji
            \App\Models\JournalLine::create([
                'journal_entry_id' => $journalEntry->id,
                'coa_id' => $coaBebanGaji->id,
                'debit' => $penggajian->total_gaji,
                'credit' => 0,
                'memo' => "Beban Gaji " . $penggajian->nama_pegawai,
            ]);
            
            echo "Journal Line (Debit): Beban Gaji Created" . PHP_EOL;
            
            // CREDIT: Kas/Bank
            \App\Models\JournalLine::create([
                'journal_entry_id' => $journalEntry->id,
                'coa_id' => $coaKasBank->id,
                'debit' => 0,
                'credit' => $penggajian->total_gaji,
                'memo' => "Pembayaran Gaji " . $penggajian->nama_pegawai,
            ]);
            
            echo "Journal Line (Credit): Kas/Bank Created" . PHP_EOL;
            
            // Also create in jurnal_umum table
            \App\Models\JurnalUmum::create([
                'coa_id' => $coaBebanGaji->id,
                'tanggal' => $penggajian->tanggal_penggajian,
                'keterangan' => "Penggajian " . $penggajian->nama_pegawai,
                'debit' => $penggajian->total_gaji,
                'kredit' => 0,
                'referensi' => $penggajian->id,
                'tipe_referensi' => 'penggajian',
                'created_by' => 1,
            ]);
            
            echo "Jurnal Umum (Debit): Created" . PHP_EOL;
            
            \App\Models\JurnalUmum::create([
                'coa_id' => $coaKasBank->id,
                'tanggal' => $penggajian->tanggal_penggajian,
                'keterangan' => "Pembayaran Gaji " . $penggajian->nama_pegawai,
                'debit' => 0,
                'kredit' => $penggajian->total_gaji,
                'referensi' => $penggajian->id,
                'tipe_referensi' => 'penggajian',
                'created_by' => 1,
            ]);
            
            echo "Jurnal Umum (Credit): Created" . PHP_EOL;
            
            echo PHP_EOL . "=== Verification ===" . PHP_EOL;
            
            // Check if journals were created
            $journalCount = DB::table('journal_entries')
                ->join('journal_lines', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
                ->where('journal_entries.ref_type', 'penggajian')
                ->where('journal_entries.ref_id', $penggajian->id)
                ->count();
            
            $jurnalUmumCount = DB::table('jurnal_umum')
                ->where('tipe_referensi', 'penggajian')
                ->where('referensi', $penggajian->id)
                ->count();
            
            echo "Journal Entries Created: " . $journalCount . PHP_EOL;
            echo "Jurnal Umum Created: " . $jurnalUmumCount . PHP_EOL;
            
            if ($journalCount > 0 && $jurnalUmumCount > 0) {
                echo PHP_EOL . "SUCCESS: Jurnal berhasil dibuat!" . PHP_EOL;
                echo "COA: " . $coaKode . " - " . $coaNama . PHP_EOL;
                echo "Total: Rp " . number_format($penggajian->total_gaji, 0) . PHP_EOL;
                echo "Status: Akan muncul di Jurnal Umum" . PHP_EOL;
            } else {
                echo PHP_EOL . "ERROR: Jurnal gagal dibuat!" . PHP_EOL;
            }
            
        } catch (\Exception $e) {
            echo "ERROR: " . $e->getMessage() . PHP_EOL;
            echo "Stack Trace: " . $e->getTraceAsString() . PHP_EOL;
        }
        
    } else {
        echo PHP_EOL . "ERROR: COA tidak ditemukan!" . PHP_EOL;
        echo "- COA Beban: " . ($coaBebanGaji ? "Ditemukan" : "TIDAK DITEMUKAN") . PHP_EOL;
        echo "- COA Kas: " . ($coaKasBank ? "Ditemukan" : "TIDAK DITEMUKAN") . PHP_EOL;
    }
    
} else {
    echo "ERROR: Data penggajian Dedi Gunawan tidak ditemukan!" . PHP_EOL;
}

echo PHP_EOL . "=== Summary ===" . PHP_EOL;
echo "Action: Manual create journal untuk Dedi Gunawan" . PHP_EOL;
echo "Result: " . ($journalCount > 0 && $jurnalUmumCount > 0 ? "SUCCESS" : "FAILED") . PHP_EOL;
echo "Next: Check Jurnal Umum page" . PHP_EOL;
