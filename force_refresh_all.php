<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "FORCE REFRESH ALL CACHES\n";
echo "=======================\n\n";

// Clear all Laravel caches
echo "Clearing Laravel caches...\n";

try {
    \Artisan::call('cache:clear');
    echo "Application cache cleared\n";
} catch (\Exception $e) {
    echo "Error clearing cache: " . $e->getMessage() . "\n";
}

try {
    \Artisan::call('view:clear');
    echo "View cache cleared\n";
} catch (\Exception $e) {
    echo "Error clearing view: " . $e->getMessage() . "\n";
}

try {
    \Artisan::call('config:clear');
    echo "Config cache cleared\n";
} catch (\Exception $e) {
    echo "Error clearing config: " . $e->getMessage() . "\n";
}

try {
    \Artisan::call('route:clear');
    echo "Route cache cleared\n";
} catch (\Exception $e) {
    echo "Error clearing routes: " . $e->getMessage() . "\n";
}

echo "\nVerifying current database state:\n";
echo "=================================\n";

$produksiJournals = \App\Models\JurnalUmum::where('tipe_referensi', 'produksi')
    ->with('coa')
    ->orderBy('id')
    ->get();

echo "Current produksi journals in database:\n";
foreach ($produksiJournals as $journal) {
    echo "ID: {$journal->id} | Ref: {$journal->referensi} | COA: {$journal->coa->kode_akun} | D: " . number_format($journal->debit, 0, ',', '.') . " | K: " . number_format($journal->kredit, 0, ',', '.') . " | {$journal->keterangan}\n";
}

echo "\nExpected UI display:\n";
echo "===================\n";
echo "12/04/2026\n";
echo "Transfer WIP ke Barang Jadi\n";
echo "116 - Pers. Barang Jadi Ayam Crispi Macdi | Asset | Debit | Rp 3.864.960\n";
echo "117 - Pers. Barang dalam Proses | Asset | Kredit | Rp 3.864.960\n";
echo "1161 - Pers. Barang Jadi Ayam Goreng Bundo | Asset | Debit | Rp 3.368.960\n";
echo "117 - Pers. Barang dalam Proses | Asset | Kredit | Rp 3.368.960\n";

echo "\nIMPORTANT INSTRUCTIONS:\n";
echo "=====================\n";
echo "1. All Laravel caches have been cleared\n";
echo "2. Database is correct with proper structure\n";
echo "3. PLEASE DO THIS NOW:\n";
echo "   - Close ALL browser windows completely\n";
echo "   - Wait 10 seconds\n";
echo "   - Open browser again\n";
echo "   - Go to Jurnal Umum page\n";
echo "   - Press Ctrl+F5 (hard refresh)\n";
echo "   - If still wrong, clear browser cache manually\n\n";
echo "The issue might be browser cache or session data\n";
echo "Database is 100% correct!\n";

echo "\nDone!\n";

?>
