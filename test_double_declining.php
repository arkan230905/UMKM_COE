<?php

// Test Double Declining Balance - SESUAI EXCEL 100%
echo "=== TEST DOUBLE DECLINING BALANCE (SESUAI EXCEL) ===\n";
echo "Data Contoh: Vehicle A\n";
echo "Harga Perolehan: Rp 500.000.000\n";
echo "Umur Ekonomis: 4 tahun (karena rate 50%)\n";
echo "Nilai Sisa: Rp 0\n";
echo "Rate: (100%/4) × 2 = 50% (SESUAI EXCEL)\n\n";

// Data persis seperti Excel
$cost = 500000000;
$residual = 0;
$life = 4; // Umur 4 tahun agar rate 50%
$rate = (1 / $life) * 2; // 0.5 = 50%

echo "Tahun\tBook Value Awal\tRate\tDepreciasi\tAkumulasi\tBook Value Akhir\n";
echo "----\t---------------\t----\t----------\t--------\t----------------\n";

$book = $cost;
$acc = 0;

// Tahun 1: Feb-Des (11 bulan)
$monthlyDepreciation = ($cost * $rate) / 12;
$year1Depreciation = $monthlyDepreciation * 11; // 11 bulan
$acc += $year1Depreciation;
$bookEnd = $book - $year1Depreciation;

echo sprintf(
    "1 (11 bln)\tRp %s\t50%%\tRp %s\tRp %s\tRp %s\n",
    number_format($book, 0, ',', '.'),
    number_format($year1Depreciation, 0, ',', '.'),
    number_format($acc, 0, ',', '.'),
    number_format($bookEnd, 0, ',', '.')
);

$book = $bookEnd;

// Tahun 2-4: Full years
for ($i = 2; $i <= 4; $i++) {
    $yearlyDepreciation = $book * $rate;
    
    // Pastikan tidak melebihi sisa yang bisa disusutkan
    $maxDepreciable = $book - $residual;
    if ($yearlyDepreciation > $maxDepreciable) {
        $yearlyDepreciation = $maxDepreciable;
    }
    
    $acc += $yearlyDepreciation;
    $bookEnd = $book - $yearlyDepreciation;
    
    echo sprintf(
        "%d\tRp %s\t50%%\tRp %s\tRp %s\tRp %s\n",
        $i,
        number_format($book, 0, ',', '.'),
        number_format($yearlyDepreciation, 0, ',', '.'),
        number_format($acc, 0, ',', '.'),
        number_format($bookEnd, 0, ',', '.')
    );
    
    $book = $bookEnd;
    
    if ($book <= $residual) {
        break;
    }
}

// Tahun 5: Jan-Feb (2 bulan) - sisa penyusutan
if ($book > $residual) {
    $year5Depreciation = $book; // Habiskan sisa
    $acc += $year5Depreciation;
    $bookEnd = 0;
    
    echo sprintf(
        "5 (2 bln)\tRp %s\t50%%\tRp %s\tRp %s\tRp %s\n",
        number_format($book + $year5Depreciation, 0, ',', '.'),
        number_format($year5Depreciation, 0, ',', '.'),
        number_format($acc, 0, ',', '.'),
        number_format($bookEnd, 0, ',', '.')
    );
}

echo "\nTotal Akumulasi: Rp " . number_format($acc, 0, ',', '.') . "\n";
echo "Target Excel: Rp 500.000.000\n";
echo "Selisih: Rp " . number_format(abs($acc - 500000000), 0, ',', '.') . "\n";

?>
