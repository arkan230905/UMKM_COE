<?php
/**
 * PRODUCTION SYNC VERIFICATION SCRIPT
 * 
 * Script ini akan membandingkan checksum file penting antara local dan production
 * untuk memastikan kode sudah ter-deploy dengan benar.
 * 
 * Cara pakai:
 * 1. Jalankan di LOCAL: php verify_production_sync.php generate
 * 2. Upload file checksums_local.json ke production
 * 3. Jalankan di PRODUCTION: php verify_production_sync.php verify
 */

$criticalFiles = [
    // Controllers
    'app/Http/Controllers/PembelianController.php',
    'app/Http/Controllers/VendorController.php',
    'app/Http/Controllers/BahanBakuController.php',
    'app/Http/Controllers/PurchaseReturnController.php',
    'app/Http/Controllers/PelunasanUtangController.php',
    
    // Models
    'app/Models/Pembelian.php',
    'app/Models/PembelianDetail.php',
    'app/Models/BahanBaku.php',
    'app/Models/BahanPendukung.php',
    'app/Models/Vendor.php',
    'app/Models/Coa.php',
    
    // Services
    'app/Services/PembelianJournalService.php',
    'app/Services/StockService.php',
    'app/Services/JournalService.php',
    
    // Observers
    'app/Observers/PembelianObserver.php',
    'app/Observers/BahanPendukungObserver.php',
    
    // Views
    'resources/views/transaksi/pembelian/create.blade.php',
    'resources/views/transaksi/pembelian/edit.blade.php',
    'resources/views/transaksi/pembelian/index.blade.php',
    
    // Routes
    'routes/web.php',
];

function generateChecksums($files) {
    $checksums = [];
    $missing = [];
    
    foreach ($files as $file) {
        if (file_exists($file)) {
            $checksums[$file] = [
                'md5' => md5_file($file),
                'size' => filesize($file),
                'modified' => filemtime($file),
                'modified_date' => date('Y-m-d H:i:s', filemtime($file))
            ];
        } else {
            $missing[] = $file;
        }
    }
    
    return [
        'checksums' => $checksums,
        'missing' => $missing,
        'generated_at' => date('Y-m-d H:i:s'),
        'total_files' => count($checksums)
    ];
}

function verifyChecksums($localChecksums, $files) {
    $results = [
        'matching' => [],
        'different' => [],
        'missing_in_production' => [],
        'missing_in_local' => []
    ];
    
    foreach ($files as $file) {
        if (!isset($localChecksums[$file])) {
            $results['missing_in_local'][] = $file;
            continue;
        }
        
        if (!file_exists($file)) {
            $results['missing_in_production'][] = [
                'file' => $file,
                'local_modified' => $localChecksums[$file]['modified_date']
            ];
            continue;
        }
        
        $currentMd5 = md5_file($file);
        $currentSize = filesize($file);
        
        if ($currentMd5 === $localChecksums[$file]['md5']) {
            $results['matching'][] = $file;
        } else {
            $results['different'][] = [
                'file' => $file,
                'local_md5' => $localChecksums[$file]['md5'],
                'production_md5' => $currentMd5,
                'local_size' => $localChecksums[$file]['size'],
                'production_size' => $currentSize,
                'local_modified' => $localChecksums[$file]['modified_date'],
                'production_modified' => date('Y-m-d H:i:s', filemtime($file))
            ];
        }
    }
    
    return $results;
}

function printResults($results) {
    echo "\n";
    echo "==========================================\n";
    echo "PRODUCTION SYNC VERIFICATION RESULTS\n";
    echo "==========================================\n\n";
    
    // Matching files
    echo "✓ MATCHING FILES (" . count($results['matching']) . "):\n";
    echo "These files are identical between local and production.\n\n";
    foreach ($results['matching'] as $file) {
        echo "  ✓ $file\n";
    }
    echo "\n";
    
    // Different files
    if (!empty($results['different'])) {
        echo "⚠ DIFFERENT FILES (" . count($results['different']) . "):\n";
        echo "These files have different content between local and production.\n";
        echo "ACTION REQUIRED: Update these files in production!\n\n";
        
        foreach ($results['different'] as $diff) {
            echo "  ⚠ {$diff['file']}\n";
            echo "     Local MD5:       {$diff['local_md5']}\n";
            echo "     Production MD5:  {$diff['production_md5']}\n";
            echo "     Local Size:      {$diff['local_size']} bytes\n";
            echo "     Production Size: {$diff['production_size']} bytes\n";
            echo "     Local Modified:  {$diff['local_modified']}\n";
            echo "     Prod Modified:   {$diff['production_modified']}\n";
            echo "\n";
        }
    }
    
    // Missing in production
    if (!empty($results['missing_in_production'])) {
        echo "❌ MISSING IN PRODUCTION (" . count($results['missing_in_production']) . "):\n";
        echo "These files exist in local but not in production.\n";
        echo "ACTION REQUIRED: Deploy these files to production!\n\n";
        
        foreach ($results['missing_in_production'] as $missing) {
            echo "  ❌ {$missing['file']}\n";
            echo "     Local Modified: {$missing['local_modified']}\n";
            echo "\n";
        }
    }
    
    // Summary
    echo "==========================================\n";
    echo "SUMMARY\n";
    echo "==========================================\n";
    echo "Total files checked: " . (count($results['matching']) + count($results['different']) + count($results['missing_in_production'])) . "\n";
    echo "✓ Matching:          " . count($results['matching']) . "\n";
    echo "⚠ Different:         " . count($results['different']) . "\n";
    echo "❌ Missing:           " . count($results['missing_in_production']) . "\n";
    echo "\n";
    
    if (empty($results['different']) && empty($results['missing_in_production'])) {
        echo "✓✓✓ ALL FILES ARE IN SYNC! ✓✓✓\n";
        echo "Production is up to date with local.\n";
        return 0;
    } else {
        echo "⚠⚠⚠ SYNC ISSUES DETECTED! ⚠⚠⚠\n";
        echo "Production needs to be updated.\n";
        echo "\nRecommended action:\n";
        echo "1. Run: git pull origin nayla\n";
        echo "2. Run: php artisan config:clear\n";
        echo "3. Run: php artisan view:clear\n";
        echo "4. Run: php artisan cache:clear\n";
        return 1;
    }
}

// Main execution
if ($argc < 2) {
    echo "Usage:\n";
    echo "  php verify_production_sync.php generate  - Generate checksums from local\n";
    echo "  php verify_production_sync.php verify    - Verify production against local checksums\n";
    exit(1);
}

$command = $argv[1];

if ($command === 'generate') {
    echo "Generating checksums from LOCAL files...\n";
    $data = generateChecksums($criticalFiles);
    
    file_put_contents('checksums_local.json', json_encode($data, JSON_PRETTY_PRINT));
    
    echo "✓ Checksums generated: checksums_local.json\n";
    echo "  Total files: {$data['total_files']}\n";
    echo "  Generated at: {$data['generated_at']}\n";
    
    if (!empty($data['missing'])) {
        echo "\n⚠ Warning: Some files are missing in local:\n";
        foreach ($data['missing'] as $file) {
            echo "  - $file\n";
        }
    }
    
    echo "\nNext steps:\n";
    echo "1. Upload checksums_local.json to production server\n";
    echo "2. Run on production: php verify_production_sync.php verify\n";
    
} elseif ($command === 'verify') {
    if (!file_exists('checksums_local.json')) {
        echo "❌ Error: checksums_local.json not found!\n";
        echo "Please run 'generate' command on local first and upload the file.\n";
        exit(1);
    }
    
    echo "Verifying PRODUCTION files against local checksums...\n";
    $localData = json_decode(file_get_contents('checksums_local.json'), true);
    
    echo "Local checksums generated at: {$localData['generated_at']}\n";
    echo "Checking {$localData['total_files']} files...\n";
    
    $results = verifyChecksums($localData['checksums'], $criticalFiles);
    $exitCode = printResults($results);
    
    exit($exitCode);
    
} else {
    echo "❌ Unknown command: $command\n";
    echo "Use 'generate' or 'verify'\n";
    exit(1);
}
