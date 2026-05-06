<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== CEK MULTI-TENANT PRODUKSI ===\n\n";

echo "1. CEK PRODUKSI CONTROLLER METHODS:\n\n";

try {
    $controllerFile = 'c:\UMKM_COE\app\Http\Controllers\ProduksiController.php';
    $controllerContent = file_get_contents($controllerFile);
    
    echo "Checking user_id filtering in controller methods:\n";
    
    $methods = ['index', 'create', 'store', 'show', 'proses'];
    
    foreach ($methods as $method) {
        if (preg_match('/public function ' . $method . '\((.*?)\n    }/s', $controllerContent, $matches)) {
            $methodContent = $matches[0];
            
            echo "\nMethod: " . $method . "\n";
            
            // Check for user_id filtering
            if (strpos($methodContent, 'user_id') !== false) {
                echo "  ✅ Contains user_id references\n";
            } else {
                echo "  ❌ No user_id references found\n";
            }
            
            // Check for auth()->id()
            if (strpos($methodContent, 'auth()->id()') !== false) {
                echo "  ✅ Uses auth()->id()\n";
            } else {
                echo "  ❌ No auth()->id() found\n";
            }
            
            // Check for where user_id
            if (strpos($methodContent, 'where(\'user_id\'') !== false || strpos($methodContent, 'where("user_id"') !== false) {
                echo "  ✅ Has where user_id filtering\n";
            } else {
                echo "  ❌ No where user_id filtering\n";
            }
            
            // Check for whereHas user_id
            if (strpos($methodContent, 'whereHas.*user_id') !== false) {
                echo "  ✅ Has whereHas user_id filtering\n";
            } else {
                echo "  ❌ No whereHas user_id filtering\n";
            }
        }
    }
    
} catch (\Exception $e) {
    echo "Error checking controller: " . $e->getMessage() . "\n";
}

echo "\n2. CEK PRODUKSI VIEWS:\n\n";

try {
    $viewPath = 'c:\UMKM_COE\resources\views\transaksi\produksi';
    
    if (is_dir($viewPath)) {
        $views = ['index.blade.php', 'create.blade.php', 'show.blade.php'];
        
        foreach ($views as $view) {
            $viewFile = $viewPath . '\\' . $view;
            
            if (file_exists($viewFile)) {
                echo "\nView: " . $view . "\n";
                
                $viewContent = file_get_contents($viewFile);
                
                // Check for user_id filtering
                if (strpos($viewContent, 'user_id') !== false) {
                    echo "  ✅ Contains user_id references\n";
                } else {
                    echo "  ❌ No user_id references found\n";
                }
                
                // Check for auth()->id()
                if (strpos($viewContent, 'auth()->id()') !== false) {
                    echo "  ✅ Uses auth()->id()\n";
                } else {
                    echo "  ❌ No auth()->id() found\n";
                }
                
                // Check for where user_id in PHP blocks
                if (strpos($viewContent, 'where(\'user_id\'') !== false || strpos($viewContent, 'where("user_id"') !== false) {
                    echo "  ✅ Has where user_id filtering\n";
                } else {
                    echo "  ❌ No where user_id filtering\n";
                }
            } else {
                echo "\nView: " . $view . " - NOT FOUND\n";
            }
        }
    } else {
        echo "❌ produksi views directory not found\n";
    }
    
} catch (\Exception $e) {
    echo "Error checking views: " . $e->getMessage() . "\n";
}

echo "\n3. CEK MODEL RELATIONSHIPS:\n\n";

try {
    // Check Produksi model relationships
    $produksiModel = 'c:\UMKM_COE\app\Models\Produksi.php';
    
    if (file_exists($produksiModel)) {
        $modelContent = file_get_contents($produksiModel);
        
        echo "Produksi Model Relationships:\n";
        
        // Check for relationships
        if (preg_match_all('/public function (\w+)\(\)/', $modelContent, $matches)) {
            foreach ($matches[1] as $relation) {
                echo "  - " . $relation . "\n";
                
                // Check if relationship has user_id filtering
                if (strpos($modelContent, $relation) !== false) {
                    $relationStart = strpos($modelContent, 'public function ' . $relation);
                    $relationEnd = strpos($modelContent, "\n    }", $relationStart);
                    $relationContent = substr($modelContent, $relationStart, $relationEnd - $relationStart);
                    
                    if (strpos($relationContent, 'user_id') !== false) {
                        echo "    ✅ Has user_id filtering\n";
                    } else {
                        echo "    ❌ No user_id filtering\n";
                    }
                }
            }
        }
    }
    
    // Check ProduksiDetail model relationships
    $produksiDetailModel = 'c:\UMKM_COE\app\Models\ProduksiDetail.php';
    
    if (file_exists($produksiDetailModel)) {
        $modelContent = file_get_contents($produksiDetailModel);
        
        echo "\nProduksiDetail Model Relationships:\n";
        
        // Check for relationships
        if (preg_match_all('/public function (\w+)\(\)/', $modelContent, $matches)) {
            foreach ($matches[1] as $relation) {
                echo "  - " . $relation . "\n";
                
                // Check if relationship has user_id filtering
                if (strpos($modelContent, $relation) !== false) {
                    $relationStart = strpos($modelContent, 'public function ' . $relation);
                    $relationEnd = strpos($modelContent, "\n    }", $relationStart);
                    $relationContent = substr($modelContent, $relationStart, $relationEnd - $relationStart);
                    
                    if (strpos($relationContent, 'user_id') !== false) {
                        echo "    ✅ Has user_id filtering\n";
                    } else {
                        echo "    ❌ No user_id filtering\n";
                    }
                }
            }
        }
    }
    
} catch (\Exception $e) {
    echo "Error checking model relationships: " . $e->getMessage() . "\n";
}

echo "\n4. IDENTIFIKASI MASALAH MULTI-TENANT:\n\n";

echo "Issues found:\n";
echo "1. ❌ ProduksiDetail model: user_id not in fillable\n";
echo "2. ❌ Need to check if controller methods use user_id filtering\n";
echo "3. ❌ Need to check if views use user_id filtering\n";
echo "4. ❌ Need to check if model relationships use user_id filtering\n\n";

echo "=== CEK MULTI-TENANT SELESAI ===\n";
