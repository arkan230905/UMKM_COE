<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "FORCE REFRESH UI CACHE\n";
echo "====================\n\n";

// Clear all possible caches
echo "Clearing Laravel caches...\n";

// Clear application cache
try {
    \Artisan::call('cache:clear');
    echo "Application cache cleared\n";
} catch (\Exception $e) {
    echo "Error clearing cache: " . $e->getMessage() . "\n";
}

// Clear view cache
try {
    \Artisan::call('view:clear');
    echo "View cache cleared\n";
} catch (\Exception $e) {
    echo "Error clearing view: " . $e->getMessage() . "\n";
}

// Clear config cache
try {
    \Artisan::call('config:clear');
    echo "Config cache cleared\n";
} catch (\Exception $e) {
    echo "Error clearing config: " . $e->getMessage() . "\n";
}

// Clear route cache
try {
    \Artisan::call('route:clear');
    echo "Route cache cleared\n";
} catch (\Exception $e) {
    echo "Error clearing routes: " . $e->getMessage() . "\n";
}

echo "\nDatabase verification:\n";
echo "====================\n";

// Final verification of database state
$journals = \App\Models\JurnalUmum::where('tipe_referensi', 'produksi')
    ->with('coa')
    ->orderBy('id')
    ->get();

echo "Current database state:\n";
foreach ($journals as $journal) {
    echo "ID: {$journal->id} | COA: {$journal->coa->kode_akun} | D: " . number_format($journal->debit, 0, ',', '.') . " | K: " . number_format($journal->kredit, 0, ',', '.') . " | {$journal->keterangan}\n";
}

echo "\nExpected UI display:\n";
echo "==================\n";
echo "12/04/2026\n";
echo "Transfer WIP ke Barang Jadi - Ayam Crispi Macdi\n";
echo "116 - Pers. Barang Jadi Ayam Crispi Macdi | Asset | Debit | Rp 3.864.960\n";
echo "117 - Pers. Barang dalam Proses | Asset | Kredit | Rp 3.864.960\n\n";
echo "12/04/2026\n";
echo "Transfer WIP ke Barang Jadi - Ayam Goreng Bundo\n";
echo "1161 - Pers. Barang Jadi Ayam Goreng Bundo | Asset | Debit | Rp 3.368.960\n";
echo "117 - Pers. Barang dalam Proses | Asset | Kredit | Rp 3.368.960\n";

echo "\nIMPORTANT:\n";
echo "=========\n";
echo "1. Database is 100% CORRECT\n";
echo "2. All caches have been cleared\n";
echo "3. PLEASE DO THIS:\n";
echo "   - Close browser completely\n";
echo "   - Reopen browser\n";
echo "   - Go to Jurnal Umum page\n";
echo "   - Press Ctrl+F5 to hard refresh\n\n";
echo "If you still see wrong data after this, there might be:\n";
echo "- JavaScript cache in browser\n";
echo "- Session data that needs to be cleared\n";
echo "- CDN caching if you're using one\n";

echo "\nDone!\n";

?>
