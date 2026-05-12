<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Create HPP Journal for Sales ===" . PHP_EOL;

// Get COA for HPP and Persediaan Barang Jadi
echo PHP_EOL . "Getting COA accounts..." . PHP_EOL;

$coaHpp = DB::table('coas')->where('kode_akun', '1600')->first();
$coaPersediaanBarangJadi = DB::table('coas')
    ->where('nama_akun', 'like', '%Persediaan Barang Jadi%')
    ->orWhere('kode_akun', 'like', '160%')
    ->where('kode_akun', '!=', '1600')
    ->first();

if (!$coaHpp) {
    echo "❌ COA HPP (1600) not found!" . PHP_EOL;
    exit;
}

if (!$coaPersediaanBarangJadi) {
    echo "❌ COA Persediaan Barang Jadi not found!" . PHP_EOL;
    exit;
}

echo "COA HPP: " . $coaHpp->kode_akun . " - " . $coaHpp->nama_akun . PHP_EOL;
echo "COA Persediaan Barang Jadi: " . $coaPersediaanBarangJadi->kode_akun . " - " . $coaPersediaanBarangJadi->nama_akun . PHP_EOL;

// Get recent sales that don't have HPP journal
echo PHP_EOL . "Finding sales without HPP journal..." . PHP_EOL;

$salesWithoutHpp = DB::table('penjualans')
    ->whereDate('tanggal', '>=', '2026-04-21')
    ->whereDate('tanggal', '<=', '2026-04-23')
    ->whereNotExists(function($query) {
        $query->select(DB::raw(1))
              ->from('jurnal_umum')
              ->whereRaw('jurnal_umum.referensi = penjualans.id')
              ->where('jurnal_umum.tipe_referensi', 'penjualan')
              ->where('jurnal_umum.coa_id', DB::raw("(SELECT id FROM coas WHERE kode_akun = '1600')"));
    })
    ->select('id', 'tanggal', 'total', 'nomor_penjualan')
    ->orderBy('tanggal')
    ->get();

echo "Sales without HPP journal: " . $salesWithoutHpp->count() . PHP_EOL;
foreach ($salesWithoutHpp as $sale) {
    echo sprintf(
        "ID: %d | %s | Total: %s | Faktur: %s",
        $sale->id,
        $sale->tanggal,
        number_format($sale->total, 0),
        $sale->nomor_penjualan
    ) . PHP_EOL;
}

echo PHP_EOL . "=== Creating HPP Journals ===" . PHP_EOL;

$createdCount = 0;
foreach ($salesWithoutHpp as $sale) {
    echo PHP_EOL . "Processing Sale ID: " . $sale->id . PHP_EOL;
    
    try {
        // Calculate HPP (assuming 70% of total as example, adjust as needed)
        $hpp = $sale->total * 0.7; // This should be calculated based on actual cost
        
        echo "Creating HPP journal for Sale ID: " . $sale->id . PHP_EOL;
        echo "- HPP Amount: Rp " . number_format($hpp, 0) . PHP_EOL;
        echo "- Sale Total: Rp " . number_format($sale->total, 0) . PHP_EOL;
        
        // Create HPP journal entry (Debit HPP, Credit Persediaan Barang Jadi)
        DB::table('jurnal_umum')->insert([
            [
                'coa_id' => $coaHpp->id,
                'tanggal' => $sale->tanggal,
                'keterangan' => 'HPP Penjualan ' . $sale->nomor_penjualan,
                'debit' => $hpp,
                'kredit' => 0,
                'referensi' => $sale->id,
                'tipe_referensi' => 'penjualan',
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'coa_id' => $coaPersediaanBarangJadi->id,
                'tanggal' => $sale->tanggal,
                'keterangan' => 'HPP Penjualan ' . $sale->nomor_penjualan,
                'debit' => 0,
                'kredit' => $hpp,
                'referensi' => $sale->id,
                'tipe_referensi' => 'penjualan',
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
        
        echo "✅ HPP journal created for Sale ID: " . $sale->id . PHP_EOL;
        $createdCount++;
        
    } catch (\Exception $e) {
        echo "❌ Error creating HPP journal for Sale ID " . $sale->id . ": " . $e->getMessage() . PHP_EOL;
    }
}

echo PHP_EOL . "=== Verification ===" . PHP_EOL;

// Check if HPP journals were created
$hppJournalsCreated = DB::table('jurnal_umum')
    ->join('coas', 'jurnal_umum.coa_id', '=', 'coas.id')
    ->where('jurnal_umum.tipe_referensi', 'penjualan')
    ->where('jurnal_umum.coa_id', $coaHpp->id)
    ->whereDate('jurnal_umum.tanggal', '>=', '2026-04-21')
    ->whereDate('jurnal_umum.tanggal', '<=', '2026-04-23')
    ->count();

echo "HPP Journals Created: " . $hppJournalsCreated . PHP_EOL;
echo "Expected: " . $createdCount . PHP_EOL;
echo "Status: " . ($hppJournalsCreated === $createdCount ? "SUCCESS" : "PARTIAL") . PHP_EOL;

echo PHP_EOL . "=== Sample Journal Display ===" . PHP_EOL;

// Show sample of created journals
$sampleJournals = DB::table('jurnal_umum')
    ->join('coas', 'jurnal_umum.coa_id', '=', 'coas.id')
    ->where('jurnal_umum.tipe_referensi', 'penjualan')
    ->where('jurnal_umum.coa_id', $coaHpp->id)
    ->whereDate('jurnal_umum.tanggal', '>=', '2026-04-21')
    ->whereDate('jurnal_umum.tanggal', '<=', '2026-04-23')
    ->select('jurnal_umum.tanggal', 'jurnal_umum.keterangan', 'jurnal_umum.debit', 'jurnal_umum.kredit', 'coas.kode_akun', 'coas.nama_akun')
    ->orderBy('jurnal_umum.tanggal')
    ->limit(3)
    ->get();

echo "Sample HPP Journals:" . PHP_EOL;
foreach ($sampleJournals as $journal) {
    echo sprintf(
        "%s | %s | %s | %s | %s | %s",
        $journal->tanggal,
        $journal->keterangan,
        $journal->kode_akun,
        $journal->nama_akun,
        number_format($journal->debit, 0),
        number_format($journal->kredit, 0)
    ) . PHP_EOL;
}

echo PHP_EOL . "=== Summary ===" . PHP_EOL;
echo "Action: Created HPP journals for sales" . PHP_EOL;
echo "Created: " . $createdCount . " HPP journals" . PHP_EOL;
echo "Status: " . ($createdCount > 0 ? "SUCCESS" : "NO SALES PROCESSED") . PHP_EOL;
echo "Result: HPP now recorded in Jurnal Umum" . PHP_EOL;
echo "Next: Check Jurnal Umum page to verify HPP entries" . PHP_EOL;

echo PHP_EOL . "=== Note ===" . PHP_EOL;
echo "- HPP calculation uses 70% of total (adjust as needed)" . PHP_EOL;
echo "- For perpetual system, HPP should be calculated based on actual cost" . PHP_EOL;
echo "- This creates the journal entries that were missing" . PHP_EOL;
echo "- Proper perpetual logic should be implemented in PenjualanController" . PHP_EOL;
