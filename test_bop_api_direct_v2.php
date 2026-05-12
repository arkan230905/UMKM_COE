<?php

echo "=== TEST BOP API DIRECT V2 ===\n\n";

echo "Testing BOP API endpoint directly...\n";

// Bootstrap Laravel
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "1. Testing route existence...\n";
    
    // Check if route exists
    $routes = app('router')->getRoutes();
    $bopRouteFound = false;
    
    foreach ($routes as $route) {
        $uri = $route->uri();
        if (strpos($uri, 'bop-details') !== false) {
            echo "   Found BOP route: {$uri}\n";
            echo "   Methods: " . implode(', ', $route->methods()) . "\n";
            echo "   Action: " . $route->getActionName() . "\n";
            $bopRouteFound = true;
        }
    }
    
    if ($bopRouteFound) {
        echo "   ✅ BOP route found\n";
        
        echo "\n2. Testing route directly...\n";
        // Test the route directly
        try {
            $request = \Illuminate\Http\Request::create('/master-data/api/bop-details/2,1', 'GET');
            $response = app()->handle($request);
            
            echo "   Response status: " . $response->getStatusCode() . "\n";
            echo "   Response content type: " . $response->headers->get('Content-Type') . "\n";
            
            $content = $response->getContent();
            echo "   Response length: " . strlen($content) . " characters\n";
            
            if (strpos($content, '<!DOCTYPE') !== false) {
                echo "   ❌ Response is HTML (404 page)\n";
                echo "   Response preview: " . substr($content, 0, 200) . "...\n";
            } else {
                echo "   ✅ Response is JSON\n";
                echo "   JSON preview: " . substr($content, 0, 200) . "...\n";
            }
            
        } catch (Exception $e) {
            echo "   Error testing route: " . $e->getMessage() . "\n";
        }
        
        echo "\n3. Testing with authentication...\n";
        // Test with simulated authentication
        try {
            // Create a request with user session
            $request = \Illuminate\Http\Request::create('/master-data/api/bop-details/2,1', 'GET');
            
            // Simulate authentication by setting user in session
            $session = app('session');
            $session->start();
            
            // Create a user and authenticate
            $user = new class {
                public $id = 1;
            };
            
            auth()->login($user);
            
            $response = app()->handle($request);
            
            echo "   Authenticated response status: " . $response->getStatusCode() . "\n";
            echo "   Authenticated response content type: " . $response->headers->get('Content-Type') . "\n";
            
            $content = $response->getContent();
            if (strpos($content, '<!DOCTYPE') !== false) {
                echo "   ❌ Authenticated response is still HTML\n";
            } else {
                echo "   ✅ Authenticated response is JSON\n";
                echo "   Authenticated JSON preview: " . substr($content, 0, 200) . "...\n";
            }
            
        } catch (Exception $e) {
            echo "   Error testing authenticated route: " . $e->getMessage() . "\n";
        }
        
    } else {
        echo "   ❌ BOP route not found\n";
    }
    
    echo "\n4. Manual database test...\n";
    // Test the database query directly
    try {
        $btklIds = [2, 1];
        
        echo "   Testing query for proses_produksi_id IN (" . implode(',', $btklIds) . ")\n";
        
        $rawBopData = \Illuminate\Support\Facades\DB::table('bop_proses as bp')
            ->whereIn('bp.proses_produksi_id', $btklIds)
            ->where('bp.user_id', 1)
            ->select(
                'bp.id',
                'bp.komponen_bop',
                'bp.total_bop_per_produk',
                'bp.total_bop_per_jam'
            )
            ->orderBy('bp.id')
            ->get();
        
        echo "   Query result: {$rawBopData->count()} records\n";
        
        if ($rawBopData->count() > 0) {
            echo "   Raw data found:\n";
            foreach ($rawBopData as $bop) {
                echo "     ID: {$bop->id}, Proses ID: " . substr($bop->komponen_bop, 0, 50) . "...\n";
            }
            
            // Process the data
            $processedBopData = [];
            foreach ($rawBopData as $bop) {
                $komponenData = json_decode($bop->komponen_bop, true);
                
                if (is_array($komponenData)) {
                    foreach ($komponenData as $component) {
                        $processedBopData[] = [
                            'id' => $bop->id,
                            'komponen_bop' => $component['component'] ?? 'Unknown',
                            'total_bop_per_produk' => $component['rate_per_hour'] ?? 0,
                            'total_bop_per_jam' => $component['rate_per_hour'] ?? 0
                        ];
                    }
                }
            }
            
            echo "   Processed data: " . count($processedBopData) . " records\n";
            
            echo "\n5. Expected JSON response:\n";
            $jsonResponse = json_encode($processedBopData);
            echo $jsonResponse . "\n";
        } else {
            echo "   ❌ No data found in database\n";
        }
        
    } catch (Exception $e) {
        echo "   Error in database test: " . $e->getMessage() . "\n";
    }
    
    echo "\n=== TEST COMPLETE ===\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
