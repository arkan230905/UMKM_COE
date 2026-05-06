<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== DEBUG HPP SAVE ISSUE ===\n\n";

echo "1. CEK CURRENT DATA DI bom_job_costings:\n\n";

try {
    $jobCostings = \App\Models\BomJobCosting::all();
    
    echo "Data saat ini di bom_job_costings:\n";
    foreach ($jobCostings as $jc) {
        echo "ID: " . $jc->id . "\n";
        echo "Produk ID: " . $jc->produk_id . "\n";
        echo "User ID: " . $jc->user_id . "\n";
        echo "Total BBB: " . $jc->total_bbb . "\n";
        echo "Total BTKL: " . $jc->total_btkl . "\n";
        echo "Total BOP: " . $jc->total_bop . "\n";
        echo "Total HPP: " . $jc->total_hpp . "\n";
        echo "HPP per unit: " . $jc->hpp_per_unit . "\n";
        echo "Created: " . $jc->created_at . "\n";
        echo "Updated: " . $jc->updated_at . "\n";
        echo "---\n";
    }
    
} catch (\Exception $e) {
    echo "Error checking job costings: " . $e->getMessage() . "\n";
}

echo "\n2. CEK BomController@store METHOD:\n\n";

try {
    $controllerFile = 'c:\UMKM_COE\app\Http\Controllers\BomController.php';
    
    if (file_exists($controllerFile)) {
        $controllerContent = file_get_contents($controllerFile);
        
        // Find the store method
        if (preg_match('/public function store\(Request \$request\)(.*?)^}/sm', $controllerContent, $matches)) {
            $storeMethod = $matches[0];
            
            echo "✅ Found BomController@store method\n";
            echo "Method length: " . strlen($storeMethod) . " characters\n";
            
            // Check for key parts
            if (strpos($storeMethod, 'DB::beginTransaction()') !== false) {
                echo "✅ Uses DB transaction\n";
            } else {
                echo "❌ No DB transaction found\n";
            }
            
            if (strpos($storeMethod, 'validate') !== false) {
                echo "✅ Has validation\n";
            } else {
                echo "❌ No validation found\n";
            }
            
            if (strpos($storeMethod, 'return redirect()') !== false) {
                echo "✅ Has redirect response\n";
            } else {
                echo "❌ No redirect response found\n";
            }
            
            if (strpos($storeMethod, 'BomJobCosting::create') !== false) {
                echo "✅ Creates BomJobCosting\n";
            } else {
                echo "❌ No BomJobCosting creation found\n";
            }
            
            // Check for potential issues
            if (strpos($storeMethod, 'bom_job_costing_id') !== false) {
                echo "⚠️ Still uses bom_job_costing_id (might cause issues)\n";
            }
            
        } else {
            echo "❌ BomController@store method not found\n";
        }
    }
    
} catch (\Exception $e) {
    echo "Error checking controller: " . $e->getMessage() . "\n";
}

echo "\n3. CEK FORM SUBMISSION ROUTE:\n\n";

try {
    // Check if the route exists
    $routes = \Illuminate\Support\Facades\Route::getRoutes();
    
    $foundRoute = false;
    foreach ($routes as $route) {
        if ($route->uri() === 'master-data/harga-pokok-produksi' && $route->methods()[0] === 'POST') {
            $foundRoute = true;
            echo "✅ POST route found: " . $route->uri() . "\n";
            echo "Action: " . $route->getActionName() . "\n";
            break;
        }
    }
    
    if (!$foundRoute) {
        echo "❌ POST route not found for master-data/harga-pokok-produksi\n";
    }
    
} catch (\Exception $e) {
    echo "Error checking routes: " . $e->getMessage() . "\n";
}

echo "\n4. CEK FORM HTML STRUCTURE:\n\n";

try {
    $viewFile = 'c:\UMKM_COE\resources\views\master-data\bom\create.blade.php';
    
    if (file_exists($viewFile)) {
        $viewContent = file_get_contents($viewFile);
        
        // Check form action
        if (preg_match('/<form[^>]*action="([^"]*)"/', $viewContent, $matches)) {
            echo "✅ Form action: " . $matches[1] . "\n";
        } else {
            echo "❌ Form action not found\n";
        }
        
        // Check form method
        if (strpos($viewContent, 'method="POST"') !== false || strpos($viewContent, "method='POST'") !== false) {
            echo "✅ Form method: POST\n";
        } else {
            echo "❌ Form method not POST\n";
        }
        
        // Check CSRF token
        if (strpos($viewContent, '@csrf') !== false) {
            echo "✅ CSRF token present\n";
        } else {
            echo "❌ CSRF token missing\n";
        }
        
        // Check submit button
        if (strpos($viewContent, 'type="submit"') !== false) {
            echo "✅ Submit button found\n";
        } else {
            echo "❌ Submit button not found\n";
        }
        
        // Check JavaScript submit handling
        if (strpos($viewContent, 'addEventListener(\'submit\'') !== false || strpos($viewContent, 'submit') !== false) {
            echo "✅ JavaScript submit handling found\n";
        } else {
            echo "❌ No JavaScript submit handling\n";
        }
        
    }
    
} catch (\Exception $e) {
    echo "Error checking form: " . $e->getMessage() . "\n";
}

echo "\n5. SIMULASI FORM SUBMISSION:\n\n";

try {
    echo "Simulating form submission data:\n";
    
    // Simulate the data that would be submitted
    $simulatedData = [
        'produk_id' => 2,
        'proses_ids' => [1], // Pengukusan
        'biaya_bahan' => 2500,
        'total_btkl' => 166.67,
        'total_bop' => 95,
        'total_hpp' => 2761.67
    ];
    
    echo "Form data:\n";
    foreach ($simulatedData as $key => $value) {
        if (is_array($value)) {
            echo "  $key: [" . implode(', ', $value) . "]\n";
        } else {
            echo "  $key: $value\n";
        }
    }
    
    // Test validation
    $rules = [
        'produk_id' => 'required|exists:produks,id',
        'proses_ids' => 'required|array|min:1',
        'proses_ids.*' => 'exists:proses_produksis,id',
        'biaya_bahan' => 'required|numeric|min:0',
        'total_btkl' => 'required|numeric|min:0',
        'total_bop' => 'required|numeric|min:0',
        'total_hpp' => 'required|numeric|min:0',
    ];
    
    echo "\nValidation rules:\n";
    foreach ($rules as $field => $rule) {
        echo "  $field: $rule\n";
    }
    
    // Check if data passes validation
    $validationErrors = [];
    
    if (empty($simulatedData['produk_id'])) {
        $validationErrors[] = 'produk_id is required';
    }
    
    if (empty($simulatedData['proses_ids'])) {
        $validationErrors[] = 'proses_ids is required';
    }
    
    if (!is_numeric($simulatedData['biaya_bahan'])) {
        $validationErrors[] = 'biaya_bahan must be numeric';
    }
    
    if (empty($validationErrors)) {
        echo "✅ Data passes validation\n";
    } else {
        echo "❌ Validation errors:\n";
        foreach ($validationErrors as $error) {
            echo "  - $error\n";
        }
    }
    
} catch (\Exception $e) {
    echo "Error simulating submission: " . $e->getMessage() . "\n";
}

echo "\n6. CHECK FOR POTENTIAL ERRORS IN STORE METHOD:\n\n";

try {
    echo "Common issues that could prevent save:\n";
    echo "1. ❌ Missing user_id in BomJobCosting creation\n";
    echo "2. ❌ Missing produk_id validation\n";
    echo "3. ❌ Database transaction rollback\n";
    echo "4. ❌ Exception not being caught\n";
    echo "5. ❌ Missing required fields\n";
    echo "6. ❌ Foreign key constraints\n\n";
    
    // Check if there are any missing user_id assignments
    $controllerFile = 'c:\UMKM_COE\app\Http\Controllers\BomController.php';
    $controllerContent = file_get_contents($controllerFile);
    
    if (strpos($controllerContent, "'user_id' => auth()->id()") !== false) {
        echo "✅ user_id is assigned in creation\n";
    } else {
        echo "❌ user_id might not be assigned in creation\n";
    }
    
    if (strpos($controllerContent, 'catch (\\Exception $e)') !== false) {
        echo "✅ Exception handling found\n";
    } else {
        echo "❌ No exception handling found\n";
    }
    
} catch (\Exception $e) {
    echo "Error checking potential issues: " . $e->getMessage() . "\n";
}

echo "\n7. RECOMMENDATIONS:\n\n";

echo "Based on analysis:\n";
echo "1. Check Laravel logs for errors: storage/logs/laravel.log\n";
echo "2. Verify form data is being sent correctly\n";
echo "3. Check if validation is passing\n";
echo "4. Verify BomJobCosting creation logic\n";
echo "5. Check for any JavaScript preventing form submission\n\n";

echo "=== DEBUG COMPLETE ===\n";
