<?php

echo "=== TEST BOP API DIRECT ===\n\n";

echo "Testing BOP API endpoint directly...\n";

// Bootstrap Laravel
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "1. Testing API route existence...\n";
    
    // Simulate the exact API call
    $btklIds = [2, 1];
    
    echo "   Testing URL: /master-data/api/bop-details/2,1\n";
    echo "   Method: GET\n";
    echo "   BTKL IDs: [" . implode(', ', $btklIds) . "]\n";
    
    // Test the exact API query
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
    
    echo "   Processed data: {$processedBopData->count()} records\n";
    
    echo "\n2. Creating JSON response...\n";
    $jsonResponse = json_encode($processedBopData);
    echo "   JSON length: " . strlen($jsonResponse) . " characters\n";
    echo "   JSON preview: " . substr($jsonResponse, 0, 200) . "...\n";
    
    echo "\n3. Expected API response:\n";
    echo $jsonResponse . "\n";
    
    echo "\n4. Route check:\n";
    echo "   The API route should be accessible at:\n";
    echo "   http://127.0.0.1:8000/master-data/api/bop-details/2,1\n";
    
    echo "\n=== TEST COMPLETE ===\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
