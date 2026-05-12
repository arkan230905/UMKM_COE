<?php
/**
 * CRITICAL SECURITY AUDIT: Multi-Tenant Data Leakage Check
 * 
 * This script audits all controllers to find potential data leakage issues
 * where queries don't filter by user_id in a multi-tenant system.
 * 
 * Run: php audit_multi_tenant_controllers.php
 */

echo "=== MULTI-TENANT DATA LEAKAGE AUDIT ===\n\n";

$controllersToAudit = [
    'app/Http/Controllers/ProdukController.php',
    'app/Http/Controllers/VendorController.php',
    'app/Http/Controllers/PegawaiController.php',
    'app/Http/Controllers/JabatanController.php',
    'app/Http/Controllers/AsetController.php',
    'app/Http/Controllers/KategoriAsetController.php',
    'app/Http/Controllers/PembelianController.php',
    'app/Http/Controllers/PenjualanController.php',
    'app/Http/Controllers/ProsesProduksiController.php',
    'app/Http/Controllers/BomController.php',
    'app/Http/Controllers/PresensiController.php',
    'app/Http/Controllers/PenggajianController.php',
    'app/Http/Controllers/SatuanController.php',
    'app/Http/Controllers/GudangController.php',
    'app/Http/Controllers/PelangganController.php',
    'app/Http/Controllers/BebanController.php',
    'app/Http/Controllers/BopController.php',
    'app/Http/Controllers/KomponenBopController.php',
];

$issues = [];

foreach ($controllersToAudit as $file) {
    if (!file_exists($file)) {
        echo "⚠️  File not found: $file\n";
        continue;
    }
    
    $content = file_get_contents($file);
    $lines = explode("\n", $content);
    
    // Check for common patterns that might indicate data leakage
    $patterns = [
        'withoutGlobalScopes()' => '/withoutGlobalScopes\(\)/',
        '::all()' => '/::all\(\)/',
        '::get()' => '/::get\(\)/',
        '::where(' => '/::where\([^)]*\)/',
        '->get()' => '/->get\(\)/',
    ];
    
    foreach ($patterns as $patternName => $regex) {
        foreach ($lines as $lineNum => $line) {
            if (preg_match($regex, $line)) {
                // Check if this line has user_id filter nearby (within 5 lines)
                $hasUserIdFilter = false;
                $contextStart = max(0, $lineNum - 5);
                $contextEnd = min(count($lines) - 1, $lineNum + 5);
                
                for ($i = $contextStart; $i <= $contextEnd; $i++) {
                    if (preg_match('/user_id.*auth\(\)->id\(\)/', $lines[$i]) ||
                        preg_match('/where\([\'"]user_id[\'"]/', $lines[$i])) {
                        $hasUserIdFilter = true;
                        break;
                    }
                }
                
                if (!$hasUserIdFilter && 
                    !preg_match('/\/\/.*SKIP/', $line) && 
                    !preg_match('/seeder/i', $line)) {
                    $issues[] = [
                        'file' => $file,
                        'line' => $lineNum + 1,
                        'pattern' => $patternName,
                        'code' => trim($line)
                    ];
                }
            }
        }
    }
}

// Display results
if (empty($issues)) {
    echo "✅ No obvious multi-tenant data leakage issues found!\n";
} else {
    echo "🚨 FOUND " . count($issues) . " POTENTIAL DATA LEAKAGE ISSUES:\n\n";
    
    $groupedByFile = [];
    foreach ($issues as $issue) {
        $groupedByFile[$issue['file']][] = $issue;
    }
    
    foreach ($groupedByFile as $file => $fileIssues) {
        echo "📁 $file (" . count($fileIssues) . " issues)\n";
        foreach ($fileIssues as $issue) {
            echo "   Line {$issue['line']}: {$issue['pattern']}\n";
            echo "   Code: {$issue['code']}\n\n";
        }
    }
}

echo "\n=== AUDIT COMPLETE ===\n";
echo "⚠️  IMPORTANT: This is an automated scan. Manual review is required!\n";
echo "⚠️  Check each flagged line to ensure it properly filters by user_id\n";
