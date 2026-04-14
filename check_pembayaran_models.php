<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "CHECKING PEMBAYARAN MODELS\n";
echo "==========================\n\n";

echo "1. PembayaranBeban Model:\n";
$bayarBeban = \App\Models\PembayaranBeban::all();
echo "Total records: " . $bayarBeban->count() . "\n";

foreach ($bayarBeban as $b) {
    echo "  ID: {$b->id} | Beban ID: {$b->beban_operasional_id} | Jumlah: {$b->jumlah} | Tanggal: {$b->tanggal}\n";
}

echo "\n2. ExpensePayment Model:\n";
$expensePayment = \App\Models\ExpensePayment::all();
echo "Total records: " . $expensePayment->count() . "\n";

foreach ($expensePayment as $e) {
    echo "  ID: {$e->id} | Beban ID: {$e->beban_operasional_id} | Nominal: " . ($e->nominal_pembayaran ?? 'NULL') . " | Tanggal: {$e->tanggal}\n";
}

echo "\n3. BebanOperasional Master Data:\n";
$bebanOps = \App\Models\BebanOperasional::where('status', 'aktif')->get();
echo "Total active: " . $bebanOps->count() . "\n";

foreach ($bebanOps as $b) {
    echo "  ID: {$b->id} | Nama: {$b->nama_beban} | Budget: {$b->budget_bulanan}\n";
    
    // Check actual payments using both models
    $bayarBebanCount = \App\Models\PembayaranBeban::where('beban_operasional_id', $b->id)->count();
    $expensePaymentCount = \App\Models\ExpensePayment::where('beban_operasional_id', $b->id)->count();
    
    echo "    PembayaranBeban: {$bayarBebanCount} records\n";
    echo "    ExpensePayment: {$expensePaymentCount} records\n";
}

echo "\n4. Testing Laporan Logic:\n";
$selectedMonth = now();

foreach ($bebanOps as $beban) {
    // Current logic (using ExpensePayment)
    $aktualExpense = \App\Models\ExpensePayment::where('beban_operasional_id', $beban->id)
        ->whereYear('tanggal', $selectedMonth->year)
        ->whereMonth('tanggal', $selectedMonth->month)
        ->sum('nominal_pembayaran');
    
    // Correct logic (using PembayaranBeban)
    $aktualBayar = \App\Models\PembayaranBeban::where('beban_operasional_id', $beban->id)
        ->whereYear('tanggal', $selectedMonth->year)
        ->whereMonth('tanggal', $selectedMonth->month)
        ->sum('jumlah');
    
    echo "Beban: {$beban->nama_beban}\n";
    echo "  Current (ExpensePayment): Rp " . number_format($aktualExpense, 0, ',', '.') . "\n";
    echo "  Correct (PembayaranBeban): Rp " . number_format($aktualBayar, 0, ',', '.') . "\n";
    echo "---\n";
}

?>
