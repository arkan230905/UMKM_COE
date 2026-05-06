<?php

// Simple BOP API route to test
echo "Creating simple BOP route...\n";

$routeCode = '
        // BOP API Route (simple version)
        Route::get("api/bop-details/{btkl_ids}", function ($btkl_ids) {
            try {
                $btklIdArray = explode(",", $btkl_ids);
                
                $rawBopData = DB::table("bop_proses as bp")
                    ->whereIn("bp.proses_produksi_id", $btklIdArray)
                    ->where("bp.user_id", auth()->id())
                    ->select(
                        "bp.id",
                        "bp.komponen_bop",
                        "bp.total_bop_per_produk",
                        "bp.total_bop_per_jam"
                    )
                    ->orderBy("bp.id")
                    ->get();
                
                $processedBopData = [];
                foreach ($rawBopData as $bop) {
                    $processedBopData[] = [
                        "id" => $bop->id,
                        "komponen_bop" => $bop->komponen_bop,
                        "total_bop_per_produk" => $bop->total_bop_per_produk,
                        "total_bop_per_jam" => $bop->total_bop_per_jam
                    ];
                }
                
                return response()->json($processedBopData);
            } catch (Exception $e) {
                return response()->json(["error" => $e->getMessage()], 500);
            }
        });
';

echo "Route code generated:\n";
echo $routeCode . "\n";

// Check current routes file
$routesFile = file_get_contents('c:\UMKM_COE\routes\web.php');

// Find the position after BTKL API route
$btklApiEnd = strpos($routesFile, '});', strpos($routesFile, 'api/btkl-details'));
if ($btklApiEnd !== false) {
    echo "Found BTKL API route end at position: $btklApiEnd\n";
    
    // Insert the simple BOP route
    $newRoutes = substr_replace($routesFile, $routeCode . "\n", $btklApiEnd + 3, 0);
    
    echo "Writing new routes file...\n";
    file_put_contents('c:\UMKM_COE\routes\web.php', $newRoutes);
    echo "Routes file updated successfully!\n";
} else {
    echo "Could not find BTKL API route end position\n";
}

echo "Done.\n";
