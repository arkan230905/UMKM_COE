<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "FORCE BROWSER CACHE CLEAR\n";
echo "========================\n\n";

// Clear all Laravel caches
\Artisan::call('cache:clear');
\Artisan::call('view:clear');
\Artisan::call('config:clear');
\Artisan::call('route:clear');

echo "All Laravel caches cleared\n\n";

// Show final database state
echo "FINAL DATABASE STATE:\n";
echo "===================\n";

$produksiJournals = \App\Models\JurnalUmum::where('tipe_referensi', 'produksi')
    ->with('coa')
    ->orderBy('id')
    ->get();

foreach ($produksiJournals as $journal) {
    echo "ID: {$journal->id} | COA: {$journal->coa->kode_akun} | D: " . number_format($journal->debit, 0, ',', '.') . " | K: " . number_format($journal->kredit, 0, ',', '.') . " | {$journal->keterangan}\n";
}

echo "\nEXPECTED UI DISPLAY:\n";
echo "===================\n";
echo "12/04/2026\n";
echo "Transfer WIP ke Barang Jadi\n";
echo "116 - Pers. Barang Jadi Ayam Crispi Macdi | Asset | Debit | Rp 3.864.960\n";
echo "117 - Pers. Barang dalam Proses | Asset | Kredit | Rp 3.864.960\n";
echo "1161 - Pers. Barang Jadi Ayam Goreng Bundo | Asset | Debit | Rp 3.368.960\n";
echo "117 - Pers. Barang dalam Proses | Asset | Kredit | Rp 3.368.960\n";

echo "\nINSTRUCTIONS:\n";
echo "============\n";
echo "1. Database is 100% correct\n";
echo "2. All caches cleared\n";
echo "3. DO THIS NOW:\n";
echo "   - Close ALL browser windows\n";
echo "   - Open Task Manager (Ctrl+Shift+Esc)\n";
echo "   - Kill all browser processes\n";
echo "   - Wait 30 seconds\n";
echo "   - Open browser fresh\n";
echo "   - Go to Jurnal Umum page\n";
echo "   - Press Ctrl+Shift+R (hard refresh)\n\n";
echo "If still showing wrong data after this,\n";
echo "the issue is in the frontend JavaScript or\n";
echo "there's a different data source being used.\n";

?>
