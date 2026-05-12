<?php

echo "=== MENGHAPUS DUPLIKASI ALERT NOTIFICATIONS ===\n\n";

// Cari semua file blade yang mengandung duplikasi alert
$bladeFiles = glob('resources/views/**/*.blade.php', GLOB_BRACE);

$duplicatePatterns = [
    // Pattern 1: Standard duplicate alerts
    '/\s*@if \(session\(\'success\'\)\)\s*\n\s*<div class="alert alert-success[^>]*>.*?<\/div>\s*\n\s*@endif\s*\n\s*@if \(session\(\'error\'\)\)\s*\n\s*<div class="alert alert-danger[^>]*>.*?<\/div>\s*\n\s*@endif\s*\n/s',
    
    // Pattern 2: Only success alerts
    '/\s*@if \(session\(\'success\'\)\)\s*\n\s*<div class="alert alert-success[^>]*>.*?<\/div>\s*\n\s*@endif\s*\n/s',
    
    // Pattern 3: Only error alerts  
    '/\s*@if \(session\(\'error\'\)\)\s*\n\s*<div class="alert alert-danger[^>]*>.*?<\/div>\s*\n\s*@endif\s*\n/s'
];

$processedFiles = [];
$totalRemoved = 0;

foreach ($bladeFiles as $file) {
    $content = file_get_contents($file);
    $originalContent = $content;
    
    // Skip layout files
    if (strpos($file, 'layouts/') !== false) {
        continue;
    }
    
    // Check if file contains duplicate alerts
    $hasDuplicates = false;
    foreach ($duplicatePatterns as $pattern) {
        if (preg_match($pattern, $content)) {
            $hasDuplicates = true;
            break;
        }
    }
    
    if ($hasDuplicates) {
        echo "Processing: {$file}\n";
        
        // Remove duplicate alerts
        foreach ($duplicatePatterns as $pattern) {
            $newContent = preg_replace($pattern, "\n", $content);
            if ($newContent !== $content) {
                $content = $newContent;
                echo "  ✅ Removed duplicate alerts\n";
                $totalRemoved++;
            }
        }
        
        // Save if changed
        if ($content !== $originalContent) {
            file_put_contents($file, $content);
            $processedFiles[] = $file;
        }
        
        echo "\n";
    }
}

echo "=== HASIL ===\n";
echo "Total file diproses: " . count($processedFiles) . "\n";
echo "Total duplikasi dihapus: {$totalRemoved}\n\n";

if (count($processedFiles) > 0) {
    echo "File yang dimodifikasi:\n";
    foreach ($processedFiles as $file) {
        echo "  - {$file}\n";
    }
} else {
    echo "Tidak ada duplikasi alert yang ditemukan.\n";
}

echo "\n✅ SELESAI!\n";
echo "Sekarang semua notifikasi hanya akan muncul sekali dari layout utama.\n";