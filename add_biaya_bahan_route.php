<?php

echo "=== ADD BIAYA BAHAN ROUTE ===\n\n";

echo "Adding route for biaya bahan detail...\n";

$routeFile = 'c:\UMKM_COE\routes\web.php';
$routeContent = file_get_contents($routeFile);

// Find the biaya-bahan routes section
$biayaBahanRoutePattern = '/Route::prefix\(\'master-data\'\)->group\(function \(\) \{.*?Route::resource\(\'biaya-bahan\', BiayaBahanController::class\)/s';

// Add detail route before the resource route
$detailRoute = 'Route::get(\'biaya-bahan/{id}/detail\', [BiayaBahanController::class, \'detail\'])->name(\'biaya-bahan.detail\');';

if (preg_match($biayaBahanRoutePattern, $routeContent, $matches)) {
    $replacement = $detailRoute . "\n    " . $matches[0];
    $newRouteContent = preg_replace($biayaBahanRoutePattern, $replacement, $routeContent);
    
    file_put_contents($routeFile, $newRouteContent);
    echo "✅ Added biaya-bahan detail route\n";
    echo "✅ Route: GET /master-data/biaya-bahan/{id}/detail\n";
    echo "✅ Named: biaya-bahan.detail\n";
} else {
    echo "❌ Could not find biaya-bahan route section\n";
    echo "Adding route manually...\n";
    
    // Find a good place to add the route
    $insertPosition = strpos($routeContent, 'Route::resource(\'biaya-bahan\', BiayaBahanController::class)');
    if ($insertPosition !== false) {
        $newRouteContent = substr_replace($routeContent, $detailRoute . "\n    " . 'Route::resource(\'biaya-bahan\', BiayaBahanController::class', $insertPosition, strlen('Route::resource(\'biaya-bahan\', BiayaBahanController::class'));
        file_put_contents($routeFile, $newRouteContent);
        echo "✅ Added biaya-bahan detail route manually\n";
    } else {
        echo "❌ Could not add route automatically\n";
    }
}

echo "\n=== ROUTE ADDED ===\n";
