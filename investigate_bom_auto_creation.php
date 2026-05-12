<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== INVESTIGASI AUTO-CREATION BOM_JOB_COSTINGS ===\n\n";

echo "1. CEK DATA SAAT INI:\n\n";

try {
    $jobCostings = \App\Models\BomJobCosting::with('produk')->get();
    
    foreach ($jobCostings as $jc) {
        echo "ID: " . $jc->id . "\n";
        echo "Produk: " . $jc->produk->nama_produk . "\n";
        echo "User ID: " . $jc->user_id . "\n";
        echo "Created At: " . $jc->created_at . "\n";
        echo "Updated At: " . $jc->updated_at . "\n";
        echo "Total BBB: " . $jc->total_bbb . "\n";
        echo "Total BTKL: " . $jc->total_btkl . "\n";
        echo "Total BOP: " . $jc->total_bop . "\n";
        echo "Total HPP: " . $jc->total_hpp . "\n";
        echo "HPP per Unit: " . $jc->hpp_per_unit . "\n";
        echo "---\n";
    }
} catch (\Exception $e) {
    echo "Error checking job costings: " . $e->getMessage() . "\n";
}

echo "\n2. CEK APAKAH ADA AUTO-POPULATION SERVICE:\n\n";

// Check if BomSyncService exists
if (class_exists('App\Services\BomSyncService')) {
    echo "✅ BomSyncService ditemukan\n";
    
    // Check methods in BomSyncService
    $reflection = new ReflectionClass('App\Services\BomSyncService');
    $methods = $reflection->getMethods();
    
    echo "Methods yang tersedia:\n";
    foreach ($methods as $method) {
        if ($method->isPublic()) {
            echo "  - " . $method->getName() . "\n";
        }
    }
} else {
    echo "❌ BomSyncService tidak ditemukan\n";
}

echo "\n3. CEK LOGS UNTUK AUTO-CREATION:\n\n";

// Check Laravel logs for auto-creation
$logFile = 'c:\UMKM_COE\storage\logs\laravel.log';

if (file_exists($logFile)) {
    echo "Membaca 50 baris terakhir dari log...\n";
    
    $lines = file($logFile);
    $lastLines = array_slice($lines, -50);
    
    foreach ($lastLines as $line) {
        if (strpos($line, 'Bom') !== false || strpos($line, 'bom') !== false) {
            echo trim($line) . "\n";
        }
    }
} else {
    echo "Log file tidak ditemukan\n";
}

echo "\n4. CEK MODEL BOOTED EVENTS:\n\n";

// Check if BomJobCosting model has booting events
$modelFile = 'c:\UMKM_COE\app\Models\BomJobCosting.php';

if (file_exists($modelFile)) {
    $modelContent = file_get_contents($modelFile);
    
    if (strpos($modelContent, 'booted') !== false) {
        echo "✅ BomJobCosting memiliki booting events\n";
        
        // Find booting events
        if (preg_match('/static::booted\(function \(\$model\) \{.*?\}\);/s', $modelContent, $matches)) {
            echo "Booting event code:\n";
            echo $matches[0] . "\n";
        }
    } else {
        echo "❌ BomJobCosting tidak memiliki booting events\n";
    }
} else {
    echo "❌ Model file tidak ditemukan\n";
}

echo "\n5. CEK CONTROLLER YANG MUNGKIN MEMBUAT DATA:\n\n";

// Check all controllers that might create BomJobCosting
$controllerFiles = glob('c:\UMKM_COE\app\Http\Controllers\*.php');

foreach ($controllerFiles as $file) {
    $content = file_get_contents($file);
    
    if (strpos($content, 'BomJobCosting') !== false) {
        echo "Controller: " . basename($file) . "\n";
        
        // Find create calls
        if (preg_match_all('/BomJobCosting::create\(/', $content, $matches)) {
            echo "  - Found " . count($matches[0]) . " BomJobCosting::create() calls\n";
        }
        
        // Find save calls
        if (preg_match_all('/\$.*->save\(\)/', $content, $matches)) {
            echo "  - Found " . count($matches[0]) . " save() calls\n";
        }
        
        echo "\n";
    }
}

echo "\n6. CEK ROUTES YANG MUNGKIN MEMICU AUTO-CREATION:\n\n";

// Check routes that might trigger auto-creation
$routeFile = 'c:\UMKM_COE\routes\web.php';
$routeContent = file_get_contents($routeFile);

if (strpos($routeContent, 'populate') !== false) {
    echo "✅ Found populate routes:\n";
    
    if (preg_match_all('/Route::.*populate.*\[.*\]/', $routeContent, $matches)) {
        foreach ($matches[0] as $match) {
            echo "  - " . $match . "\n";
        }
    }
}

if (strpos($routeContent, 'sync') !== false) {
    echo "✅ Found sync routes:\n";
    
    if (preg_match_all('/Route::.*sync.*\[.*\]/', $routeContent, $matches)) {
        foreach ($matches[0] as $match) {
            echo "  - " . $match . "\n";
        }
    }
}

echo "\n7. CEK CREATION TIMESTAMP:\n\n";

try {
    $jobCosting = \App\Models\BomJobCosting::find(2);
    
    if ($jobCosting) {
        echo "Job Costing ID 2:\n";
        echo "Created At: " . $jobCosting->created_at . "\n";
        echo "Updated At: " . $jobCosting->updated_at . "\n";
        
        // Check if created and updated are different
        $created = new \DateTime($jobCosting->created_at);
        $updated = new \DateTime($jobCosting->updated_at);
        
        if ($created != $updated) {
            echo "⚠️ Data telah diupdate setelah dibuat\n";
            echo "Selisih waktu: " . $updated->diff($created)->format('%H jam %I menit %S detik') . "\n";
        } else {
            echo "✅ Data belum pernah diupdate\n";
        }
    }
} catch (\Exception $e) {
    echo "Error checking timestamps: " . $e->getMessage() . "\n";
}

echo "\n8. CEK APAKAH ADA OBSERVERS:\n\n";

// Check for observers
$observerPath = 'c:\UMKM_COE\app\Observers';

if (is_dir($observerPath)) {
    $observerFiles = glob($observerPath . '/*.php');
    
    foreach ($observerFiles as $file) {
        $content = file_get_contents($file);
        
        if (strpos($content, 'BomJobCosting') !== false) {
            echo "✅ Observer found: " . basename($file) . "\n";
        }
    }
} else {
    echo "❌ Observers directory tidak ditemukan\n";
}

echo "\n=== INVESTIGASI SELESAI ===\n";
