<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== UPDATE JOURNAL CONTROLLERS ===\n\n";

echo "1. CEK CONTROLLERS YANG MENGGUNAKAN JOURNAL:\n\n";

try {
    $controllerPath = 'c:\UMKM_COE\app\Http\Controllers';
    $controllers = glob($controllerPath . '/*.php');
    
    $journalControllers = [];
    foreach ($controllers as $controller) {
        $content = file_get_contents($controller);
        if (strpos($content, 'Journal') !== false || strpos($content, 'Jurnal') !== false) {
            $journalControllers[] = basename($controller);
        }
    }
    
    echo "Controllers yang menggunakan journal:\n";
    foreach ($journalControllers as $controller) {
        echo "- " . $controller . "\n";
    }
    
} catch (\Exception $e) {
    echo "Error checking controllers: " . $e->getMessage() . "\n";
}

echo "\n2. ANALISIS PENGGUNAAN JOURNAL DI CONTROLLERS:\n\n";

try {
    $keyControllers = ['ProduksiController.php', 'PembelianController.php', 'PenjualanController.php', 'PenggajianController.php'];
    
    foreach ($keyControllers as $controller) {
        $controllerFile = 'c:\UMKM_COE\app\Http\Controllers\\' . $controller;
        
        if (file_exists($controllerFile)) {
            $content = file_get_contents($controllerFile);
            
            echo "\nController: " . $controller . "\n";
            
            // Check for JournalEntry usage
            if (strpos($content, 'JournalEntry') !== false) {
                echo "  ❌ Uses JournalEntry model\n";
            } else {
                echo "  ✅ No JournalEntry usage\n";
            }
            
            // Check for JournalLine usage
            if (strpos($content, 'JournalLine') !== false) {
                echo "  ❌ Uses JournalLine model\n";
            } else {
                echo "  ✅ No JournalLine usage\n";
            }
            
            // Check for JurnalUmum usage
            if (strpos($content, 'JurnalUmum') !== false) {
                echo "  ✅ Uses JurnalUmum model\n";
            } else {
                echo "  ❌ No JurnalUmum usage\n";
            }
            
            // Check for JournalService usage
            if (strpos($content, 'JournalService') !== false) {
                echo "  ✅ Uses JournalService\n";
            } else {
                echo "  ❌ No JournalService usage\n";
            }
        }
    }
    
} catch (\Exception $e) {
    echo "Error analyzing controllers: " . $e->getMessage() . "\n";
}

echo "\n3. CEK JOURNAL SERVICE:\n\n";

try {
    $servicePath = 'c:\UMKM_COE\app\Services';
    
    if (file_exists($servicePath . '\JournalService.php')) {
        echo "✅ JournalService exists\n";
        
        $serviceContent = file_get_contents($servicePath . '\JournalService.php');
        
        // Check if it uses JurnalUmum
        if (strpos($serviceContent, 'JurnalUmum') !== false) {
            echo "  ✅ Uses JurnalUmum model\n";
        } else {
            echo "  ❌ No JurnalUmum usage\n";
        }
        
        // Check if it uses old models
        if (strpos($serviceContent, 'JournalEntry') !== false) {
            echo "  ❌ Uses JournalEntry model\n";
        } else {
            echo "  ✅ No JournalEntry usage\n";
        }
        
        if (strpos($serviceContent, 'JournalLine') !== false) {
            echo "  ❌ Uses JournalLine model\n";
        } else {
            echo "  ✅ No JournalLine usage\n";
        }
        
    } else {
        echo "❌ JournalService doesn't exist\n";
    }
    
} catch (\Exception $e) {
    echo "Error checking JournalService: " . $e->getMessage() . "\n";
}

echo "\n4. REKOMENDASI UPDATE:\n\n";

echo "Berdasarkan analisis:\n";
echo "1. JournalService seharusnya sudah menggunakan JurnalUmum\n";
echo "2. Controllers seharusnya menggunakan JournalService\n";
echo "3. Tidak perlu update controllers jika sudah menggunakan JournalService\n";
echo "4. Hapus model JournalEntry dan JournalLine yang tidak digunakan\n\n";

echo "Tindakan yang diperlukan:\n";
echo "1. Verifikasi JournalService menggunakan JurnalUmum\n";
echo "2. Hapus model JournalEntry dan JournalLine\n";
echo "3. Test semua fungsi jurnal\n";

echo "\n=== ANALYSIS COMPLETE ===\n";
