<?php

echo "=== CHECK BOP ROUTE ===\n\n";

echo "Checking if BOP API route is properly registered...\n";

// Bootstrap Laravel
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "1. Checking route registration...\n";
    
    // Get all routes
    $routes = app('router')->getRoutes();
    
    echo "   Total routes registered: " . $routes->count() . "\n";
    
    // Look for BOP API route
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
    
    if (!$bopRouteFound) {
        echo "   ❌ BOP API route not found!\n";
        
        echo "\n2. Checking if route is in master-data group...\n";
        foreach ($routes as $route) {
            $uri = $route->uri();
            if (strpos($uri, 'master-data') !== false && strpos($uri, 'api') !== false) {
                echo "   Found master-data API route: {$uri}\n";
                echo "   Methods: " . implode(', ', $route->methods()) . "\n";
            }
        }
        
        echo "\n3. Manual route test...\n";
        // Simulate the route callback
        $btklIds = '2,1';
        $btklIdArray = explode(',', $btklIds);
        
        echo "   Testing with BTKL IDs: [{$btklIds}]\n";
        
        try {
            $rawBopData = \Illuminate\Support\Facades\DB::table('bop_proses as bp')
                ->whereIn('bp.proses_produksi_id', $btklIdArray)
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
            
            // Process the BOP data
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
                } else {
                    $processedBopData[] = [
                        'id' => $bop->id,
                        'komponen_bop' => $bop->komponen_bop,
                        'total_bop_per_produk' => $bop->total_bop_per_produk,
                        'total_bop_per_jam' => $bop->total_bop_per_jam
                    ];
                }
            }
            
            echo "   Processed data: " . count($processedBopData) . " records\n";
            
            echo "\n4. Expected JSON response:\n";
            $jsonResponse = json_encode($processedBopData);
            echo $jsonResponse . "\n";
            
        } catch (Exception $e) {
            echo "   Error in manual test: " . $e->getMessage() . "\n";
        }
        
    } else {
        echo "   ✅ BOP API route found\n";
        
        echo "\n2. Testing route directly...\n";
        // Test the route directly
        try {
            $request = \Illuminate\Http\Request::create('/master-data/api/bop-details/2,1', 'GET');
            $response = app()->handle($request);
            
            echo "   Response status: " . $response->getStatusCode() . "\n";
            echo "   Response content type: " . $response->headers->get('Content-Type') . "\n";
            echo "   Response content: " . substr($response->getContent(), 0, 200) . "...\n";
            
        } catch (Exception $e) {
            echo "   Error testing route: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n=== ROUTE CHECK COMPLETE ===\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
