<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Test Raw SQL ===\n";

$startDate = '2026-02-01';
$endDate = '2026-02-28';

// Test raw SQL
$sql = "SELECT COUNT(*) as count, SUM(total) as total 
          FROM pembelians 
          WHERE tanggal BETWEEN '$startDate' AND '$endDate' 
          AND bank_id = 2 
          AND payment_method = 'transfer'";

$result = \DB::select($sql);

echo "SQL: $sql\n";
echo "Result: " . json_encode($result) . "\n";

// Test dengan tanggal spesifik
$sql2 = "SELECT COUNT(*) as count, SUM(total) as total 
           FROM pembelians 
           WHERE tanggal = '2026-02-02' 
           AND bank_id = 2 
           AND payment_method = 'transfer'";

$result2 = \DB::select($sql2);

echo "\nSQL2: $sql2\n";
echo "Result2: " . json_encode($result2) . "\n";

// Test dengan DATE
$sql3 = "SELECT COUNT(*) as count, SUM(total) as total 
           FROM pembelians 
           WHERE tanggal = DATE('2026-02-02') 
           AND bank_id = 2 
           AND payment_method = 'transfer'";

$result3 = \DB::select($sql3);

echo "\nSQL3: $sql3\n";
echo "Result3: " . json_encode($result3) . "\n";
