<?php

echo "=== FIXING BOP ROUTE SYNTAX ERROR ===\n\n";

// Read the current routes file
$routesFile = file_get_contents('c:\UMKM_COE\routes\web.php');

// Find the problematic BOP route section
$pattern = '/\/\/ BOP API Route.*?}\);/s';

// Remove the problematic BOP route
$cleanRoutes = preg_replace($pattern, '', $routesFile);

if ($cleanRoutes !== null) {
    echo "✅ Removed problematic BOP route\n";
    
    // Add a simple BOP route after the BTKL route
    $btklEnd = strpos($cleanRoutes, '});', strpos($cleanRoutes, 'api/btkl-data'));
    if ($btklEnd !== false) {
        $simpleBopRoute = '
        
        // BOP API Route (simple version)
        Route::get(\'api/bop-details/{btkl_ids}\', function ($btkl_ids) {
            try {
                $btklIdArray = explode(\',\', $btkl_ids);
                
                $rawBopData = DB::table(\'bop_proses as bp\')
                    ->whereIn(\'bp.proses_produksi_id\', $btklIdArray)
                    ->where(\'bp.user_id\', auth()->id())
                    ->select(
                        \'bp.id\',
                        \'bp.komponen_bop\',
                        \'bp.total_bop_per_produk\',
                        \'bp.total_bop_per_jam\'
                    )
                    ->orderBy(\'bp.id\')
                    ->get();
                
                $processedBopData = [];
                foreach ($rawBopData as $bop) {
                    $processedBopData[] = [
                        \'id\' => $bop->id,
                        \'komponen_bop\' => $bop->komponen_bop,
                        \'total_bop_per_produk\' => $bop->total_bop_per_produk,
                        \'total_bop_per_jam\' => $bop->total_bop_per_jam
                    ];
                }
                
                return response()->json($processedBopData);
            } catch (\\Exception $e) {
                return response()->json([\'error\' => $e->getMessage()], 500);
            }
        });';
        
        $finalRoutes = substr_replace($cleanRoutes, $simpleBopRoute, $btklEnd + 3, 0);
        
        // Write the fixed routes file
        file_put_contents('c:\UMKM_COE\routes\web.php', $finalRoutes);
        echo "✅ Added simple BOP route\n";
        echo "✅ Routes file fixed successfully!\n";
        
    } else {
        echo "❌ Could not find BTKL route end position\n";
    }
} else {
    echo "❌ Could not remove problematic BOP route\n";
}

echo "\n=== TESTING ROUTE CLEAR ===\n";

// Test route clear
$output = shell_exec('php artisan route:clear 2>&1');
echo $output;

if (strpos($output, 'ParseError') !== false) {
    echo "\n❌ Still has syntax error\n";
} else {
    echo "\n✅ Route clear successful!\n";
}

echo "\n=== DONE ===\n";
