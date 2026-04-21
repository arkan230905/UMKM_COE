<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Check Jurnal Umum View ===" . PHP_EOL;

// Look for Jurnal Umum view or controller
echo PHP_EOL . "Looking for Jurnal Umum view files..." . PHP_EOL;

$viewPaths = [
    __DIR__ . '/resources/views/jurnal-umum/',
    __DIR__ . '/resources/views/jurnal/',
    __DIR__ . '/resources/views/transaksi/jurnal-umum/',
    __DIR__ . '/resources/views/transaksi/jurnal/'
];

foreach ($viewPaths as $path) {
    if (is_dir($path)) {
        echo "Found view directory: " . $path . PHP_EOL;
        $files = scandir($path);
        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                echo "  - " . $file . PHP_EOL;
            }
        }
    }
}

// Look for Jurnal Umum controller
echo PHP_EOL . "Looking for Jurnal Umum controller..." . PHP_EOL;

$controllerPaths = [
    __DIR__ . '/app/Http/Controllers/JurnalUmumController.php',
    __DIR__ . '/app/Http/Controllers/JurnalController.php',
    __DIR__ . '/app/Http/Controllers/Transaksi/JurnalUmumController.php',
    __DIR__ . '/app/Http/Controllers/Transaksi/JurnalController.php'
];

foreach ($controllerPaths as $path) {
    if (file_exists($path)) {
        echo "Found controller: " . $path . PHP_EOL;
        
        $controllerContent = file_get_contents($path);
        
        // Look for sorting or ordering logic
        if (strpos($controllerContent, 'orderBy') !== false) {
            echo "  - Contains orderBy logic" . PHP_EOL;
        }
        
        if (strpos($controllerContent, 'sort') !== false) {
            echo "  - Contains sort logic" . PHP_EOL;
        }
        
        if (strpos($controllerContent, 'production') !== false) {
            echo "  - Contains production logic" . PHP_EOL;
        }
        
        if (strpos($controllerContent, 'WIP') !== false) {
            echo "  - Contains WIP logic" . PHP_EOL;
        }
        
        if (strpos($controllerContent, 'alokasi') !== false) {
            echo "  - Contains alokasi logic" . PHP_EOL;
        }
        
        // Look for specific sorting patterns
        if (preg_match('/orderBy\s*\([^)]*\)/', $controllerContent, $matches)) {
            echo "  - orderBy pattern: " . $matches[0] . PHP_EOL;
        }
        
        // Look for date-based ordering
        if (strpos($controllerContent, 'tanggal') !== false) {
            echo "  - Uses tanggal field" . PHP_EOL;
        }
        
        // Look for keterangan-based ordering
        if (strpos($controllerContent, 'keterangan') !== false) {
            echo "  - Uses keterangan field" . PHP_EOL;
        }
    }
}

echo PHP_EOL . "=== Check Routes ===" . PHP_EOL;

// Look for routes related to jurnal umum
$routesFile = __DIR__ . '/routes/web.php';
if (file_exists($routesFile)) {
    $routesContent = file_get_contents($routesFile);
    
    echo "Checking routes for jurnal umum..." . PHP_EOL;
    
    if (strpos($routesContent, 'jurnal-umum') !== false) {
        echo "  - Found jurnal-umum route" . PHP_EOL;
    }
    
    if (strpos($routesContent, 'jurnal') !== false) {
        echo "  - Found jurnal route" . PHP_EOL;
    }
    
    if (strpos($routesContent, 'JurnalUmumController') !== false) {
        echo "  - Found JurnalUmumController route" . PHP_EOL;
    }
}

echo PHP_EOL . "=== Check for Production Specific Logic ===" . PHP_EOL;

// Look for any files that might handle production journal display
$productionFiles = [
    __DIR__ . '/app/Http/Controllers/ProduksiController.php',
    __DIR__ . '/resources/views/produksi/',
    __DIR__ . '/resources/views/produksi/'
];

foreach ($productionFiles as $path) {
    if (file_exists($path)) {
        if (is_dir($path)) {
            echo "Found production directory: " . $path . PHP_EOL;
        } else {
            echo "Found production file: " . $path . PHP_EOL;
            
            $content = file_get_contents($path);
            if (strpos($content, 'jurnal') !== false) {
                echo "  - Contains journal logic" . PHP_EOL;
            }
        }
    }
}

echo PHP_EOL . "=== Summary ===" . PHP_EOL;
echo "Files to check for sorting issue:" . PHP_EOL;
echo "1. Jurnal Umum Controller" . PHP_EOL;
echo "2. Jurnal Umum View" . PHP_EOL;
echo "3. Any production-related views" . PHP_EOL;
echo PHP_EOL . "Look for:" . PHP_EOL;
echo "- orderBy/sort logic" . PHP_EOL;
echo "- Date-based ordering" . PHP_EOL;
echo "- Production sequence logic" . PHP_EOL;
echo "- Any custom sorting for production entries" . PHP_EOL;
