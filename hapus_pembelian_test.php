<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== HAPUS PEMBELIAN TEST ===" . PHP_EOL;

// Cari pembelian test
$pembelianTest = \App\Models\Pembelian::where('nomor_pembelian', 'LIKE', '%TEST%')
    ->orWhere('nomor_pembelian', 'LIKE', '%test%')
    ->first();

if (!$pembelianTest) {
    echo "Tidak ada pembelian test yang ditemukan!" . PHP_EOL;
    exit;
}

echo "Menghapus pembelian test:" . PHP_EOL;
echo "ID: {$pembelianTest->id}" . PHP_EOL;
echo "Nomor: {$pembelianTest->nomor_pembelian}" . PHP_EOL;
echo "Tanggal: {$pembelianTest->tanggal}" . PHP_EOL;
echo "Total: Rp " . number_format($pembelianTest->total_harga, 2, ',', '.') . PHP_EOL;
echo PHP_EOL;

try {
    \DB::beginTransaction();
    
    // 1. Hapus journal entries terkait
    $journalEntries = \App\Models\JournalEntry::where('ref_type', 'purchase')
        ->where('ref_id', $pembelianTest->id)
        ->get();
    
    echo "Menghapus journal entries..." . PHP_EOL;
    foreach ($journalEntries as $journal) {
        echo "  - Journal ID {$journal->id}: {$journal->memo}" . PHP_EOL;
        
        // Hapus journal lines
        \App\Models\JournalLine::where('journal_entry_id', $journal->id)->delete();
        
        // Hapus journal entry
        $journal->delete();
    }
    
    // 2. Hapus pembelian details
    echo "Menghapus pembelian details..." . PHP_EOL;
    $details = \App\Models\PembelianDetail::where('pembelian_id', $pembelianTest->id)->get();
    foreach ($details as $detail) {
        echo "  - Detail ID {$detail->id}: {$detail->jumlah} x " . 
             ($detail->bahanBaku ? $detail->bahanBaku->nama_bahan : 'Unknown') . PHP_EOL;
        $detail->delete();
    }
    
    // 3. Hapus pembelian
    echo "Menghapus pembelian..." . PHP_EOL;
    echo "  - Pembelian ID {$pembelianTest->id}: {$pembelianTest->nomor_pembelian}" . PHP_EOL;
    $pembelianTest->delete();
    
    \DB::commit();
    
    echo PHP_EOL . "✅ PEMBELIAN TEST BERHASIL DIHAPUS!" . PHP_EOL;
    
    // Verifikasi
    echo PHP_EOL . "VERIFIKASI:" . PHP_EOL;
    echo "============" . PHP_EOL;
    
    $sisaPembelian = \App\Models\Pembelian::count();
    echo "Sisa pembelian: {$sisaPembelian}" . PHP_EOL;
    
    $sisaJournal = \App\Models\JournalEntry::where('ref_type', 'purchase')->count();
    echo "Sisa journal purchase: {$sisaJournal}" . PHP_EOL;
    
    $sisaDetails = \App\Models\PembelianDetail::count();
    echo "Sisa pembelian details: {$sisaDetails}" . PHP_EOL;
    
    echo PHP_EOL . "Data yang tersisa:" . PHP_EOL;
    $pembelians = \App\Models\Pembelian::orderBy('id', 'desc')->get();
    foreach ($pembelians as $p) {
        echo "- ID {$p->id}: {$p->nomor_pembelian} (Rp " . 
             number_format($p->total_harga, 2, ',', '.') . ")" . PHP_EOL;
    }
    
} catch (\Exception $e) {
    \DB::rollBack();
    echo "❌ ERROR: " . $e->getMessage() . PHP_EOL;
    echo "Line: " . $e->getLine() . PHP_EOL;
    echo "File: " . $e->getFile() . PHP_EOL;
}

echo PHP_EOL . "✅ Selesai!" . PHP_EOL;
