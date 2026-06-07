<?php
/**
 * COMPREHENSIVE PEMBELIAN ENVIRONMENT DIAGNOSTIC
 * 
 * Script ini akan mengecek semua aspek yang mempengaruhi fitur pembelian:
 * - Database structure & data
 * - COA mappings
 * - Model relations
 * - Environment configuration
 * - Cache status
 * - File versions
 * 
 * Jalankan di LOCAL dan PRODUCTION, kemudian bandingkan hasilnya.
 * 
 * Usage: php diagnose_pembelian_environment.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$environment = env('APP_ENV', 'unknown');
$timestamp = date('Y-m-d H:i:s');

echo "==========================================\n";
echo "PEMBELIAN ENVIRONMENT DIAGNOSTIC\n";
echo "==========================================\n";
echo "Environment: $environment\n";
echo "Timestamp: $timestamp\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "Laravel Version: " . app()->version() . "\n";
echo "\n";

$report = [];

// ==========================================
// 1. DATABASE STRUCTURE CHECK
// ==========================================
echo "1. CHECKING DATABASE STRUCTURE...\n";
echo "-------------------------------------------\n";

$tables = ['pembelians', 'pembelian_details', 'bahan_bakus', 'bahan_pendukungs', 'vendors', 'coas'];
$report['database']['tables'] = [];

foreach ($tables as $table) {
    $exists = DB::select("SHOW TABLES LIKE '$table'");
    $status = !empty($exists) ? '✓' : '✗';
    echo "  $status Table: $table\n";
    
    if (!empty($exists)) {
        $columns = DB::select("SHOW COLUMNS FROM $table");
        $report['database']['tables'][$table] = array_map(fn($col) => $col->Field, $columns);
        
        // Check critical columns
        if ($table === 'bahan_bakus' || $table === 'bahan_pendukungs') {
            $hasCoaColumn = false;
            foreach ($columns as $col) {
                if ($col->Field === 'coa_persediaan_id') {
                    $hasCoaColumn = true;
                    echo "    ✓ Column coa_persediaan_id exists (Type: {$col->Type}, Null: {$col->Null})\n";
                }
            }
            if (!$hasCoaColumn) {
                echo "    ✗ CRITICAL: coa_persediaan_id column missing!\n";
            }
        }
    }
}
echo "\n";

// ==========================================
// 2. COA MAPPINGS CHECK
// ==========================================
echo "2. CHECKING COA MAPPINGS...\n";
echo "-------------------------------------------\n";

// Bahan Baku
echo "  Bahan Baku COA Mappings:\n";
$bahanBakus = \App\Models\BahanBaku::whereIn('nama_bahan', ['Ayam Potong', 'Ayam Kampung', 'Bebek'])
    ->select('id', 'nama_bahan', 'coa_persediaan_id', 'user_id')
    ->get();

$report['coa_mappings']['bahan_baku'] = [];
foreach ($bahanBakus as $bb) {
    $coaExists = 'N/A';
    $coaNama = 'N/A';
    
    if ($bb->coa_persediaan_id) {
        $coa = \App\Models\Coa::where('kode_akun', $bb->coa_persediaan_id)
            ->where('user_id', $bb->user_id)
            ->first();
        $coaExists = $coa ? '✓' : '✗';
        $coaNama = $coa ? $coa->nama_akun : 'NOT FOUND';
    }
    
    $status = $bb->coa_persediaan_id ? $coaExists : '⚠';
    echo "    $status {$bb->nama_bahan}: {$bb->coa_persediaan_id} → $coaNama\n";
    
    $report['coa_mappings']['bahan_baku'][] = [
        'nama' => $bb->nama_bahan,
        'coa_id' => $bb->coa_persediaan_id,
        'coa_exists' => $coaExists,
        'coa_nama' => $coaNama
    ];
}

// Bahan Pendukung
echo "\n  Bahan Pendukung COA Mappings:\n";
$bahanPendukungs = \App\Models\BahanPendukung::whereIn('nama_bahan', ['Tepung Terigu', 'Minyak Goreng', 'Air Galon'])
    ->select('id', 'nama_bahan', 'coa_persediaan_id', 'user_id')
    ->get();

$report['coa_mappings']['bahan_pendukung'] = [];
foreach ($bahanPendukungs as $bp) {
    $coaExists = 'N/A';
    $coaNama = 'N/A';
    
    if ($bp->coa_persediaan_id) {
        $coa = \App\Models\Coa::where('kode_akun', $bp->coa_persediaan_id)
            ->where('user_id', $bp->user_id)
            ->first();
        $coaExists = $coa ? '✓' : '✗';
        $coaNama = $coa ? $coa->nama_akun : 'NOT FOUND';
    }
    
    $status = $bp->coa_persediaan_id ? $coaExists : '⚠';
    echo "    $status {$bp->nama_bahan}: {$bp->coa_persediaan_id} → $coaNama\n";
    
    $report['coa_mappings']['bahan_pendukung'][] = [
        'nama' => $bp->nama_bahan,
        'coa_id' => $bp->coa_persediaan_id,
        'coa_exists' => $coaExists,
        'coa_nama' => $coaNama
    ];
}
echo "\n";

// ==========================================
// 3. MODEL RELATIONS CHECK
// ==========================================
echo "3. CHECKING MODEL RELATIONS...\n";
echo "-------------------------------------------\n";

$bb = \App\Models\BahanBaku::first();
$bp = \App\Models\BahanPendukung::first();

$report['model_relations'] = [];

if ($bb) {
    echo "  BahanBaku Model:\n";
    $relations = ['satuan', 'coaPersediaan', 'coaPembelian', 'coaHpp'];
    foreach ($relations as $relation) {
        try {
            $exists = method_exists($bb, $relation);
            echo "    " . ($exists ? '✓' : '✗') . " Relation: $relation\n";
            $report['model_relations']['BahanBaku'][$relation] = $exists;
        } catch (\Exception $e) {
            echo "    ✗ Relation $relation: ERROR - {$e->getMessage()}\n";
            $report['model_relations']['BahanBaku'][$relation] = false;
        }
    }
}

if ($bp) {
    echo "\n  BahanPendukung Model:\n";
    $relations = ['satuanRelation', 'coaPersediaan', 'coaPembelian'];
    foreach ($relations as $relation) {
        try {
            $exists = method_exists($bp, $relation);
            echo "    " . ($exists ? '✓' : '✗') . " Relation: $relation\n";
            $report['model_relations']['BahanPendukung'][$relation] = $exists;
        } catch (\Exception $e) {
            echo "    ✗ Relation $relation: ERROR - {$e->getMessage()}\n";
            $report['model_relations']['BahanPendukung'][$relation] = false;
        }
    }
}
echo "\n";

// ==========================================
// 4. FOREIGN KEY CONSTRAINTS CHECK
// ==========================================
echo "4. CHECKING FOREIGN KEY CONSTRAINTS...\n";
echo "-------------------------------------------\n";

$fkConstraints = DB::select("
    SELECT 
        TABLE_NAME,
        COLUMN_NAME,
        CONSTRAINT_NAME,
        REFERENCED_TABLE_NAME,
        REFERENCED_COLUMN_NAME
    FROM information_schema.KEY_COLUMN_USAGE
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME IN ('pembelians', 'pembelian_details', 'bahan_bakus', 'bahan_pendukungs')
    AND REFERENCED_TABLE_NAME IS NOT NULL
");

$report['foreign_keys'] = [];
foreach ($fkConstraints as $fk) {
    echo "  ✓ {$fk->TABLE_NAME}.{$fk->COLUMN_NAME} → {$fk->REFERENCED_TABLE_NAME}.{$fk->REFERENCED_COLUMN_NAME}\n";
    $report['foreign_keys'][] = [
        'table' => $fk->TABLE_NAME,
        'column' => $fk->COLUMN_NAME,
        'references' => "{$fk->REFERENCED_TABLE_NAME}.{$fk->REFERENCED_COLUMN_NAME}"
    ];
}
echo "\n";

// ==========================================
// 5. ENVIRONMENT CONFIGURATION CHECK
// ==========================================
echo "5. CHECKING ENVIRONMENT CONFIGURATION...\n";
echo "-------------------------------------------\n";

$envVars = [
    'APP_ENV',
    'APP_DEBUG',
    'APP_URL',
    'DB_CONNECTION',
    'DB_HOST',
    'DB_DATABASE',
    'CACHE_DRIVER',
    'SESSION_DRIVER',
    'QUEUE_CONNECTION',
    'LOG_CHANNEL'
];

$report['environment'] = [];
foreach ($envVars as $var) {
    $value = env($var, 'NOT SET');
    
    // Mask sensitive data
    if ($var === 'DB_DATABASE') {
        $value = substr($value, 0, 3) . '***';
    }
    
    echo "  $var: $value\n";
    $report['environment'][$var] = $value;
}
echo "\n";

// ==========================================
// 6. CACHE STATUS CHECK
// ==========================================
echo "6. CHECKING CACHE STATUS...\n";
echo "-------------------------------------------\n";

$cacheFiles = [
    'bootstrap/cache/config.php' => 'Config Cache',
    'bootstrap/cache/routes-v7.php' => 'Route Cache',
    'bootstrap/cache/services.php' => 'Services Cache',
];

$report['cache_status'] = [];
foreach ($cacheFiles as $file => $name) {
    $exists = file_exists(base_path($file));
    $status = $exists ? '⚠ EXISTS' : '✓ NOT CACHED';
    $modified = $exists ? date('Y-m-d H:i:s', filemtime(base_path($file))) : 'N/A';
    echo "  $status $name\n";
    if ($exists) {
        echo "           Modified: $modified\n";
    }
    $report['cache_status'][$name] = [
        'exists' => $exists,
        'modified' => $modified
    ];
}

// View cache directory
$viewCachePath = storage_path('framework/views');
if (is_dir($viewCachePath)) {
    $viewCacheFiles = glob($viewCachePath . '/*');
    $viewCacheCount = count($viewCacheFiles);
    echo "  ⚠ View Cache: $viewCacheCount files\n";
    $report['cache_status']['View Cache'] = $viewCacheCount;
} else {
    echo "  ✓ View Cache: Directory not found (good)\n";
    $report['cache_status']['View Cache'] = 0;
}
echo "\n";

// ==========================================
// 7. FILE VERSIONS CHECK
// ==========================================
echo "7. CHECKING FILE VERSIONS (MD5)...\n";
echo "-------------------------------------------\n";

$criticalFiles = [
    'app/Http/Controllers/PembelianController.php',
    'resources/views/transaksi/pembelian/create.blade.php',
    'app/Services/PembelianJournalService.php',
    'app/Models/BahanBaku.php',
    'app/Models/BahanPendukung.php',
];

$report['file_versions'] = [];
foreach ($criticalFiles as $file) {
    $fullPath = base_path($file);
    if (file_exists($fullPath)) {
        $md5 = md5_file($fullPath);
        $size = filesize($fullPath);
        $modified = date('Y-m-d H:i:s', filemtime($fullPath));
        echo "  ✓ $file\n";
        echo "    MD5: $md5\n";
        echo "    Size: $size bytes\n";
        echo "    Modified: $modified\n";
        
        $report['file_versions'][$file] = [
            'md5' => $md5,
            'size' => $size,
            'modified' => $modified
        ];
    } else {
        echo "  ✗ $file: NOT FOUND\n";
        $report['file_versions'][$file] = 'NOT FOUND';
    }
}
echo "\n";

// ==========================================
// 8. SERVICES CHECK
// ==========================================
echo "8. CHECKING SERVICES AVAILABILITY...\n";
echo "-------------------------------------------\n";

$services = [
    'PembelianJournalService' => \App\Services\PembelianJournalService::class,
    'StockService' => \App\Services\StockService::class,
    'JournalService' => \App\Services\JournalService::class,
];

$report['services'] = [];
foreach ($services as $name => $class) {
    $exists = class_exists($class);
    echo "  " . ($exists ? '✓' : '✗') . " $name\n";
    
    if ($exists) {
        $methods = get_class_methods($class);
        echo "    Methods: " . count($methods) . "\n";
        $report['services'][$name] = [
            'exists' => true,
            'methods_count' => count($methods)
        ];
    } else {
        $report['services'][$name] = ['exists' => false];
    }
}
echo "\n";

// ==========================================
// 9. OBSERVERS CHECK
// ==========================================
echo "9. CHECKING OBSERVERS...\n";
echo "-------------------------------------------\n";

$observers = [
    'PembelianObserver' => \App\Observers\PembelianObserver::class,
    'BahanPendukungObserver' => \App\Observers\BahanPendukungObserver::class,
];

$report['observers'] = [];
foreach ($observers as $name => $class) {
    $exists = class_exists($class);
    echo "  " . ($exists ? '✓' : '✗') . " $name\n";
    $report['observers'][$name] = $exists;
}
echo "\n";

// ==========================================
// 10. RECENT PEMBELIAN DATA
// ==========================================
echo "10. CHECKING RECENT PEMBELIAN DATA...\n";
echo "-------------------------------------------\n";

$recentPembelian = \App\Models\Pembelian::with('details')
    ->orderBy('created_at', 'desc')
    ->first();

if ($recentPembelian) {
    echo "  ✓ Latest Pembelian: #{$recentPembelian->id}\n";
    echo "    Nomor: {$recentPembelian->nomor_pembelian}\n";
    echo "    Tanggal: {$recentPembelian->tanggal}\n";
    echo "    Total: Rp " . number_format($recentPembelian->total_harga) . "\n";
    echo "    Details: " . $recentPembelian->details->count() . " items\n";
    
    $report['recent_data']['latest_pembelian'] = [
        'id' => $recentPembelian->id,
        'nomor' => $recentPembelian->nomor_pembelian,
        'total' => $recentPembelian->total_harga,
        'items' => $recentPembelian->details->count()
    ];
} else {
    echo "  ⚠ No pembelian data found\n";
    $report['recent_data']['latest_pembelian'] = null;
}
echo "\n";

// ==========================================
// SUMMARY & RECOMMENDATIONS
// ==========================================
echo "==========================================\n";
echo "DIAGNOSTIC SUMMARY\n";
echo "==========================================\n";

$issues = [];

// Check COA mappings
$unmappedBahanBaku = \App\Models\BahanBaku::whereNull('coa_persediaan_id')->count();
$unmappedBahanPendukung = \App\Models\BahanPendukung::whereNull('coa_persediaan_id')->count();

if ($unmappedBahanBaku > 0) {
    $issues[] = "⚠ $unmappedBahanBaku Bahan Baku without COA mapping";
}
if ($unmappedBahanPendukung > 0) {
    $issues[] = "⚠ $unmappedBahanPendukung Bahan Pendukung without COA mapping";
}

// Check cache
$cachedConfigs = file_exists(base_path('bootstrap/cache/config.php'));
if ($cachedConfigs) {
    $issues[] = "⚠ Config cache exists (may cause issues in development)";
}

if (empty($issues)) {
    echo "✓✓✓ NO CRITICAL ISSUES DETECTED ✓✓✓\n";
} else {
    echo "ISSUES DETECTED:\n";
    foreach ($issues as $issue) {
        echo "  $issue\n";
    }
}

echo "\n";

// Save report to JSON
$reportFile = "diagnostic_report_{$environment}_" . date('Ymd_His') . ".json";
file_put_contents($reportFile, json_encode($report, JSON_PRETTY_PRINT));
echo "✓ Detailed report saved to: $reportFile\n";
echo "\n";

echo "==========================================\n";
echo "NEXT STEPS\n";
echo "==========================================\n";
echo "1. Run this script on BOTH Local and Production\n";
echo "2. Compare the JSON reports\n";
echo "3. Focus on differences in:\n";
echo "   - COA mappings\n";
echo "   - File versions (MD5)\n";
echo "   - Cache status\n";
echo "   - Foreign key constraints\n";
echo "4. Clear caches: php artisan config:clear && php artisan view:clear\n";
echo "5. Update COA mappings if needed\n";
echo "6. Re-test pembelian flow\n";
echo "\n";
