<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== CEK JOURNAL DISPLAY VS DATABASE ===" . PHP_EOL;

// 1. Cek data di database
echo "DATA DI DATABASE:" . PHP_EOL;
$journalEntry = \App\Models\JournalEntry::where('ref_type', 'purchase')
    ->with('linesWithAccount.account')
    ->orderBy('id', 'desc')
    ->first();

if ($journalEntry) {
    echo "Journal Entry ID: {$journalEntry->id}" . PHP_EOL;
    echo "Tanggal: {$journalEntry->tanggal}" . PHP_EOL;
    echo PHP_EOL;
    
    foreach ($journalEntry->linesWithAccount as $line) {
        $accountName = $line->account ? $line->account->name : 'Unknown';
        $accountCode = $line->account ? $line->account->code : 'Unknown';
        echo "Account: {$accountCode} - {$accountName}" . PHP_EOL;
        echo "Debit: Rp " . number_format($line->debit, 2, ',', '.') . PHP_EOL;
        echo "Credit: Rp " . number_format($line->credit, 2, ',', '.') . PHP_EOL;
        echo PHP_EOL;
    }
}

// 2. Cek apakah ada view atau query khusus yang digunakan web
echo PHP_EOL . "CEK QUERY YANG DIGUNAKAN WEB:" . PHP_EOL;

// Cek controller atau file yang mungkin menghandle journal display
$journalControllerFiles = [
    'app/Http/Controllers/JournalController.php',
    'app/Http/Controllers/JournalEntryController.php',
    'app/Http/Controllers/PembelianController.php'
];

foreach ($journalControllerFiles as $file) {
    if (file_exists($file)) {
        echo "Found: {$file}" . PHP_EOL;
        $content = file_get_contents($file);
        
        // Cek query yang mengambil journal
        if (strpos($content, 'JournalEntry') !== false) {
            echo "  - Mengandung JournalEntry query" . PHP_EOL;
        }
        
        // Cek apakah ada swap debit/kredit
        if (strpos($content, 'debit') !== false && strpos($content, 'credit') !== false) {
            echo "  - Mengandung logic debit/credit" . PHP_EOL;
            
            // Cari baris yang mungkin menukar
            $lines = explode("\n", $content);
            foreach ($lines as $lineNum => $line) {
                if (strpos($line, 'debit') !== false && strpos($line, 'credit') !== false) {
                    echo "    Line " . ($lineNum + 1) . ": " . trim($line) . PHP_EOL;
                }
            }
        }
    }
}

// 3. Cek blade template yang mungkin menampilkan journal
echo PHP_EOL . "CEK BLADE TEMPLATE:" . PHP_EOL;

$bladeFiles = [
    'resources/views/journal/',
    'resources/views/pembelian/',
    'resources/views/transaksi/'
];

foreach ($bladeFiles as $dir) {
    if (is_dir($dir)) {
        $files = glob($dir . '*.blade.php');
        foreach ($files as $file) {
            echo "Found: " . basename($file) . PHP_EOL;
            $content = file_get_contents($file);
            
            if (strpos($content, 'debit') !== false && strpos($content, 'credit') !== false) {
                echo "  - Mengandung display debit/credit" . PHP_EOL;
            }
        }
    }
}

echo PHP_EOL . "✅ Selesai!" . PHP_EOL;
