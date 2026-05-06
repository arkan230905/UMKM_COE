<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== FIND BOM_JOB_COSTING_ID REFERENCES ===\n\n";

echo "1. SEARCH IN MODELS:\n\n";

$modelFiles = glob('c:\UMKM_COE\app\Models\*.php');

foreach ($modelFiles as $file) {
    $content = file_get_contents($file);
    
    if (strpos($content, 'bom_job_costing_id') !== false) {
        echo "Model: " . basename($file) . "\n";
        
        // Find lines with bom_job_costing_id
        $lines = explode("\n", $content);
        foreach ($lines as $lineNum => $line) {
            if (strpos($line, 'bom_job_costing_id') !== false) {
                echo "  Line " . ($lineNum + 1) . ": " . trim($line) . "\n";
            }
        }
        echo "---\n";
    }
}

echo "\n2. SEARCH IN CONTROLLERS:\n\n";

$controllerFiles = glob('c:\UMKM_COE\app\Http\Controllers\*.php');

foreach ($controllerFiles as $file) {
    $content = file_get_contents($file);
    
    if (strpos($content, 'bom_job_costing_id') !== false) {
        echo "Controller: " . basename($file) . "\n";
        
        // Find lines with bom_job_costing_id
        $lines = explode("\n", $content);
        foreach ($lines as $lineNum => $line) {
            if (strpos($line, 'bom_job_costing_id') !== false) {
                echo "  Line " . ($lineNum + 1) . ": " . trim($line) . "\n";
            }
        }
        echo "---\n";
    }
}

echo "\n3. SEARCH IN VIEWS:\n\n";

$viewFiles = glob('c:\UMKM_COE\resources\views\**\*.blade.php', GLOB_BRACE);

foreach ($viewFiles as $file) {
    $content = file_get_contents($file);
    
    if (strpos($content, 'bom_job_costing_id') !== false) {
        echo "View: " . str_replace('c:\UMKM_COE\\', '', $file) . "\n";
        
        // Find lines with bom_job_costing_id
        $lines = explode("\n", $content);
        foreach ($lines as $lineNum => $line) {
            if (strpos($line, 'bom_job_costing_id') !== false) {
                echo "  Line " . ($lineNum + 1) . ": " . trim($line) . "\n";
            }
        }
        echo "---\n";
    }
}

echo "\n4. CHECK MODEL RELATIONS:\n\n";

// Check BomJobCosting model
if (file_exists('c:\UMKM_COE\app\Models\BomJobCosting.php')) {
    $content = file_get_contents('c:\UMKM_COE\app\Models\BomJobCosting.php');
    
    if (strpos($content, 'detailBBB') !== false) {
        echo "BomJobCosting model has detailBBB relation:\n";
        
        // Find detailBBB relation
        if (preg_match('/public function detailBBB\(\)[^{]*\{.*?\}/s', $content, $matches)) {
            echo "  " . trim($matches[0]) . "\n";
        }
        echo "---\n";
    }
}

echo "\n5. IDENTIFY PROBLEMATIC QUERY:\n\n";

echo "Error message shows:\n";
echo "SQL: select * from `bom_job_bbb` where `bom_job_bbb`.`bom_job_costing_id` in (2) and `user_id` = 1\n\n";

echo "This suggests there's a relation using:\n";
echo "- whereIn('bom_job_costing_id', [2])\n";
echo "- Or similar query pattern\n\n";

echo "6. CHECK SPECIFIC MODELS FOR RELATIONS:\n\n";

// Check BomJobBBB model specifically
if (file_exists('c:\UMKM_COE\app\Models\BomJobBBB.php')) {
    $content = file_get_contents('c:\UMKM_COE\app\Models\BomJobBBB.php');
    
    echo "BomJobBBB model content:\n";
    echo $content . "\n\n";
    
    if (strpos($content, 'bomJobCosting') !== false) {
        echo "❌ Found bomJobCosting relation in BomJobBBB model - needs to be removed\n";
    } else {
        echo "✅ No bomJobCosting relation found in BomJobBBB model\n";
    }
}

echo "\n=== SEARCH COMPLETE ===\n";
