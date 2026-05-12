<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== IDENTIFIKASI DATABASE TRANSAKSI PRODUKSI ===\n\n";

echo "1. CEK ROUTE UNTUK HALAMAN PRODUKSI:\n\n";

try {
    // Check if the route exists
    $routes = app('router')->getRoutes();
    
    foreach ($routes as $route) {
        if (strpos($route->uri(), 'produksi') !== false) {
            echo "Route: " . $route->uri() . "\n";
            echo "Method: " . implode(', ', $route->methods()) . "\n";
            echo "Action: " . $route->getActionName() . "\n";
            echo "Name: " . ($route->getName() ?? 'No name') . "\n";
            echo "---\n";
        }
    }
    
} catch (\Exception $e) {
    echo "Error checking routes: " . $e->getMessage() . "\n";
}

echo "\n2. CEK CONTROLLER YANG MENANGANI PRODUKSI:\n\n";

try {
    // Find the controller
    $controllerPath = 'c:\UMKM_COE\app\Http\Controllers';
    
    if (file_exists($controllerPath . '\ProduksiController.php')) {
        echo "✅ Found ProduksiController.php\n";
        
        $controllerContent = file_get_contents($controllerPath . '\ProduksiController.php');
        
        // Extract method names
        if (preg_match_all('/public function (\w+)/', $controllerContent, $matches)) {
            echo "Methods found:\n";
            foreach ($matches[1] as $method) {
                echo "  - " . $method . "\n";
            }
        }
        
    } else {
        echo "❌ ProduksiController.php not found\n";
        
        // Look for other controllers that might handle produksi
        $controllers = glob($controllerPath . '/*.php');
        foreach ($controllers as $controller) {
            $content = file_get_contents($controller);
            if (strpos($content, 'produksi') !== false || strpos($content, 'Produksi') !== false) {
                echo "Found in: " . basename($controller) . "\n";
            }
        }
    }
    
} catch (\Exception $e) {
    echo "Error checking controller: " . $e->getMessage() . "\n";
}

echo "\n3. CEK MODEL YANG TERKAIT PRODUKSI:\n\n";

try {
    // Check for Produksi model
    if (class_exists('App\Models\Produksi')) {
        echo "✅ Found Produksi model\n";
        
        $produksi = new \App\Models\Produksi();
        echo "Table: " . $produksi->getTable() . "\n";
        
        // Get fillable fields
        $fillable = $produksi->getFillable();
        echo "Fillable fields: " . implode(', ', $fillable) . "\n";
        
        // Check if user_id is in fillable
        if (in_array('user_id', $fillable)) {
            echo "✅ user_id is in fillable\n";
        } else {
            echo "❌ user_id is NOT in fillable\n";
        }
        
    } else {
        echo "❌ Produksi model not found\n";
    }
    
    // Check for ProduksiDetail model
    if (class_exists('App\Models\ProduksiDetail')) {
        echo "\n✅ Found ProduksiDetail model\n";
        
        $produksiDetail = new \App\Models\ProduksiDetail();
        echo "Table: " . $produksiDetail->getTable() . "\n";
        
        // Get fillable fields
        $fillable = $produksiDetail->getFillable();
        echo "Fillable fields: " . implode(', ', $fillable) . "\n";
        
        // Check if user_id is in fillable
        if (in_array('user_id', $fillable)) {
            echo "✅ user_id is in fillable\n";
        } else {
            echo "❌ user_id is NOT in fillable\n";
        }
        
    } else {
        echo "❌ ProduksiDetail model not found\n";
    }
    
} catch (\Exception $e) {
    echo "Error checking models: " . $e->getMessage() . "\n";
}

echo "\n4. CEK STRUKTUR TABEL DATABASE:\n\n";

try {
    // Check produksi table structure
    if (\Illuminate\Support\Facades\Schema::hasTable('produksis')) {
        echo "✅ produksis table exists\n";
        
        $columns = \Illuminate\Support\Facades\Schema::getColumnListing('produksis');
        echo "Columns: " . implode(', ', $columns) . "\n";
        
        if (in_array('user_id', $columns)) {
            echo "✅ user_id column exists\n";
        } else {
            echo "❌ user_id column missing\n";
        }
        
        // Check data count
        $totalCount = \Illuminate\Support\Facades\DB::table('produksis')->count();
        $userCount = \Illuminate\Support\Facades\DB::table('produksis')->where('user_id', 1)->count();
        
        echo "Total records: " . $totalCount . "\n";
        echo "User 1 records: " . $userCount . "\n";
        
        if ($totalCount > $userCount) {
            echo "⚠️ There are records from other users!\n";
        } else {
            echo "✅ All records belong to user 1\n";
        }
        
    } else {
        echo "❌ produksis table not found\n";
    }
    
    // Check produksi_details table structure
    if (\Illuminate\Support\Facades\Schema::hasTable('produksi_details')) {
        echo "\n✅ produksi_details table exists\n";
        
        $columns = \Illuminate\Support\Facades\Schema::getColumnListing('produksi_details');
        echo "Columns: " . implode(', ', $columns) . "\n";
        
        if (in_array('user_id', $columns)) {
            echo "✅ user_id column exists\n";
        } else {
            echo "❌ user_id column missing\n";
        }
        
        // Check data count
        $totalCount = \Illuminate\Support\Facades\DB::table('produksi_details')->count();
        $userCount = \Illuminate\Support\Facades\DB::table('produksi_details')->where('user_id', 1)->count();
        
        echo "Total records: " . $totalCount . "\n";
        echo "User 1 records: " . $userCount . "\n";
        
        if ($totalCount > $userCount) {
            echo "⚠️ There are records from other users!\n";
        } else {
            echo "✅ All records belong to user 1\n";
        }
        
    } else {
        echo "❌ produksi_details table not found\n";
    }
    
} catch (\Exception $e) {
    echo "Error checking table structure: " . $e->getMessage() . "\n";
}

echo "\n5. CEK VIEW FILE UNTUK PRODUKSI:\n\n";

try {
    $viewPath = 'c:\UMKM_COE\resources\views\transaksi';
    
    if (is_dir($viewPath)) {
        $views = scandir($viewPath);
        
        foreach ($views as $view) {
            if (strpos($view, 'produksi') !== false && pathinfo($view, PATHINFO_EXTENSION) === 'php') {
                echo "Found view: " . $view . "\n";
                
                $viewContent = file_get_contents($viewPath . '\\' . $view);
                
                // Check for user_id filtering
                if (strpos($viewContent, 'user_id') !== false) {
                    echo "  ✅ Contains user_id references\n";
                } else {
                    echo "  ❌ No user_id references found\n";
                }
                
                // Check for auth()->id()
                if (strpos($viewContent, 'auth()->id()') !== false) {
                    echo "  ✅ Uses auth()->id() for filtering\n";
                } else {
                    echo "  ❌ No auth()->id() found\n";
                }
            }
        }
    } else {
        echo "❌ transaksi view directory not found\n";
    }
    
} catch (\Exception $e) {
    echo "Error checking views: " . $e->getMessage() . "\n";
}

echo "\n6. SUMMARY DATABASE YANG TERKAIT PRODUKSI:\n\n";

echo "Based on analysis, the following databases handle produksi transactions:\n";
echo "1. produksis - Main production transactions\n";
echo "2. produksi_details - Production detail items\n";
echo "3. Related tables: products, pegawais, etc.\n\n";

echo "Key tables to check for multi-tenant compliance:\n";
echo "- produksis (must have user_id filtering)\n";
echo "- produksi_details (must have user_id filtering)\n";
echo "- Any related lookup tables used in produksi\n\n";

echo "=== IDENTIFIKASI SELESAI ===\n";
