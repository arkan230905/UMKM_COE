<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Searching for penjualan with different nomor patterns...\n\n";

// Search for SJ-260412 patterns
$patterns = ['SJ-260412-001', 'SJ-260412-002', 'SJ-260412-003'];

foreach ($patterns as $nomor) {
    echo "Searching for: $nomor\n";
    $penjualan = App\Models\Penjualan::where('nomor_penjualan', $nomor)->first();
    
    if ($penjualan) {
        echo "  FOUND: ID {$penjualan->id}, Total: " . ($penjualan->total_harga ?? 'NULL') . "\n";
        $totalFromDetails = $penjualan->details->sum('subtotal');
        echo "  Total from details: $totalFromDetails\n";
        
        $journalCount = App\Models\JurnalUmum::where('tipe_referensi', 'penjualan')
            ->where('referensi', $nomor)
            ->count();
        echo "  Journal entries: $journalCount\n";
    } else {
        echo "  NOT FOUND\n";
    }
    echo "---\n";
}

echo "\nAll penjualan records with their nomor:\n";
$allPenjualan = App\Models\Penjualan::select('id', 'nomor_penjualan', 'tanggal', 'payment_method')->get();
foreach ($allPenjualan as $p) {
    echo "ID: {$p->id} | Nomor: {$p->nomor_penjualan} | Tanggal: {$p->tanggal} | Method: {$p->payment_method}\n";
}
?>
