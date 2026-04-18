<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== CHECKING PEMBELIAN DATA AFTER FIX ===\n\n";

// 1. Check total pembelian records
$totalPembelian = DB::table('pembelians')->count();
echo "Total Pembelian Records: $totalPembelian\n\n";

// 2. Check if there are any soft-deleted records
$totalWithTrashed = DB::table('pembelians')->whereNull('deleted_at')->count();
$totalDeleted = DB::table('pembelians')->whereNotNull('deleted_at')->count();
echo "Active Records: $totalWithTrashed\n";
echo "Soft Deleted Records: $totalDeleted\n\n";

// 3. Show latest 5 pembelian records
echo "=== LATEST 5 PEMBELIAN RECORDS ===\n";
$latestPembelians = DB::table('pembelians')
    ->whereNull('deleted_at')
    ->orderBy('id', 'desc')
    ->limit(5)
    ->get();

if ($latestPembelians->isEmpty()) {
    echo "No pembelian records found!\n\n";
} else {
    foreach ($latestPembelians as $p) {
        echo "ID: {$p->id}\n";
        echo "Nomor: {$p->nomor_pembelian}\n";
        echo "Tanggal: {$p->tanggal}\n";
        echo "Vendor ID: {$p->vendor_id}\n";
        echo "Total: Rp " . number_format($p->total_harga, 0, ',', '.') . "\n";
        echo "Status: {$p->status}\n";
        echo "Payment Method: {$p->payment_method}\n";
        echo "Created At: {$p->created_at}\n";
        
        // Check details
        $detailCount = DB::table('pembelian_details')
            ->where('pembelian_id', $p->id)
            ->count();
        echo "Detail Count: $detailCount\n";
        
        echo "---\n";
    }
}

// 4. Check Laravel log for recent errors
echo "\n=== CHECKING RECENT LARAVEL LOG ===\n";
$logFile = storage_path('logs/laravel.log');
if (file_exists($logFile)) {
    $logContent = file_get_contents($logFile);
    $lines = explode("\n", $logContent);
    $recentLines = array_slice($lines, -50); // Last 50 lines
    
    $hasErrors = false;
    foreach ($recentLines as $line) {
        if (stripos($line, 'error') !== false || stripos($line, 'exception') !== false) {
            echo $line . "\n";
            $hasErrors = true;
        }
    }
    
    if (!$hasErrors) {
        echo "No recent errors found in log.\n";
    }
} else {
    echo "Log file not found.\n";
}

echo "\n=== CHECK COMPLETE ===\n";
