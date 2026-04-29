<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing Purchase Journal System...\n\n";

// 1. Check if there are any pembelian records
$pembelian = \App\Models\Pembelian::latest()->first();
if (!$pembelian) {
    echo "❌ No pembelian found in database\n";
    exit;
}

echo "✅ Found pembelian: {$pembelian->id} - {$pembelian->nomor_pembelian}\n";
echo "   - Vendor: " . ($pembelian->vendor->nama_vendor ?? 'N/A') . "\n";
echo "   - Total: Rp " . number_format($pembelian->total_harga, 0, ',', '.') . "\n";
echo "   - Payment Method: {$pembelian->payment_method}\n";
echo "   - Status: {$pembelian->status}\n\n";

// 2. Check if there are existing journal entries
$existingJournals = \App\Models\JurnalUmum::where('tipe_referensi', 'pembelian')
    ->where('referensi', $pembelian->nomor_pembelian)
    ->get();

echo "📊 Existing journal entries for this pembelian: " . $existingJournals->count() . "\n";
foreach ($existingJournals as $journal) {
    echo "   - ID: {$journal->id}, COA: {$journal->coa_id}, Debit: {$journal->debit}, Credit: {$journal->kredit}\n";
}
echo "\n";

// 3. Test creating journal entries
echo "🔧 Testing PembelianJournalService...\n";
try {
    $service = new \App\Services\PembelianJournalService();
    $result = $service->createJournalFromPembelian($pembelian);
    
    if ($result) {
        echo "✅ Journal created successfully!\n";
    } else {
        echo "❌ Journal creation returned null\n";
    }
} catch (Exception $e) {
    echo "❌ Error creating journal: " . $e->getMessage() . "\n";
    echo "   Stack trace: " . $e->getTraceAsString() . "\n";
}

// 4. Check journal entries after creation
echo "\n📊 Journal entries after creation:\n";
$newJournals = \App\Models\JurnalUmum::where('tipe_referensi', 'pembelian')
    ->where('referensi', $pembelian->nomor_pembelian)
    ->get();

echo "Total entries: " . $newJournals->count() . "\n";
foreach ($newJournals as $journal) {
    $coa = \App\Models\Coa::find($journal->coa_id);
    echo "   - COA: {$coa->kode_akun} {$coa->nama_akun}\n";
    echo "     Debit: Rp " . number_format($journal->debit, 0, ',', '.') . "\n";
    echo "     Credit: Rp " . number_format($journal->kredit, 0, ',', '.') . "\n";
    echo "     Memo: {$journal->keterangan}\n\n";
}

// 5. Test if journals appear in jurnal-umum view
echo "🔍 Testing if journals appear in jurnal-umum view...\n";
$jurnalUmumQuery = \DB::table('jurnal_umum as ju')
    ->leftJoin('coas', 'coas.id', '=', 'ju.coa_id')
    ->where('ju.tipe_referensi', 'pembelian')
    ->where('ju.referensi', $pembelian->nomor_pembelian)
    ->get();

echo "Journals found in jurnal-umum query: " . $jurnalUmumQuery->count() . "\n";
foreach ($jurnalUmumQuery as $journal) {
    echo "   - {$journal->tanggal}: {$journal->kode_akun} {$journal->nama_akun} - {$journal->keterangan}\n";
}

echo "\n🎯 Test completed!\n";
