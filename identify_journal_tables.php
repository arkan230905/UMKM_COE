<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== IDENTIFIKASI TABEL JURNAL ===\n\n";

echo "1. CEK TABEL JURNAL YANG ADA:\n\n";

try {
    // Get all tables
    $tables = \Illuminate\Support\Facades\Schema::getTableListing();
    
    $journalTables = [];
    foreach ($tables as $table) {
        if (strpos($table, 'jurnal') !== false || strpos($table, 'journal') !== false) {
            $journalTables[] = $table;
        }
    }
    
    echo "Tabel jurnal yang ditemukan:\n";
    foreach ($journalTables as $table) {
        echo "- " . $table . "\n";
    }
    
} catch (\Exception $e) {
    echo "Error checking tables: " . $e->getMessage() . "\n";
}

echo "\n2. ANALISIS STRUKTUR JURNAL_UMUM:\n\n";

try {
    if (\Illuminate\Support\Facades\Schema::hasTable('jurnal_umum')) {
        $columns = \Illuminate\Support\Facades\Schema::getColumnListing('jurnal_umum');
        echo "Struktur jurnal_umum:\n";
        echo implode(', ', $columns) . "\n\n";
        
        // Check if user_id exists
        if (in_array('user_id', $columns)) {
            echo "✅ user_id column exists (multi-tenant compliant)\n";
        } else {
            echo "❌ user_id column missing (multi-tenant issue)\n";
        }
        
        // Check data count
        $totalRecords = \Illuminate\Support\Facades\DB::table('jurnal_umum')->count();
        echo "Total records: " . $totalRecords . "\n";
        
        if ($totalRecords > 0) {
            echo "Sample records:\n";
            $samples = \Illuminate\Support\Facades\DB::table('jurnal_umum')->limit(3)->get();
            
            foreach ($samples as $sample) {
                echo "  ID: " . $sample->id . ", User ID: " . $sample->user_id . ", Tipe: " . $sample->tipe_referensi . ", Debit: " . $sample->debit . "\n";
            }
        }
        
    } else {
        echo "❌ jurnal_umum table doesn't exist\n";
    }
    
} catch (\Exception $e) {
    echo "Error analyzing jurnal_umum: " . $e->getMessage() . "\n";
}

echo "\n3. CEK TABEL JOURNAL_ENTRIES DAN JURNAL_LINES:\n\n";

try {
    $tablesToCheck = ['journal_entries', 'jurnal_entries', 'journal_lines', 'jurnal_lines'];
    
    foreach ($tablesToCheck as $table) {
        if (\Illuminate\Support\Facades\Schema::hasTable($table)) {
            echo "Tabel: " . $table . "\n";
            
            $columns = \Illuminate\Support\Facades\Schema::getColumnListing($table);
            echo "  Columns: " . implode(', ', $columns) . "\n";
            
            $recordCount = \Illuminate\Support\Facades\DB::table($table)->count();
            echo "  Records: " . $recordCount . "\n";
            
            if ($recordCount > 0) {
                echo "  ⚠️ Contains data - need backup before deletion\n";
            } else {
                echo "  ✅ Empty - safe to delete\n";
            }
            
            echo "---\n";
        }
    }
    
} catch (\Exception $e) {
    echo "Error checking journal tables: " . $e->getMessage() . "\n";
}

echo "\n4. CEK MODEL YANG TERKAIT JURNAL:\n\n";

try {
    $modelPath = 'c:\UMKM_COE\app\Models';
    $models = glob($modelPath . '/*.php');
    
    $journalModels = [];
    foreach ($models as $model) {
        $content = file_get_contents($model);
        if (strpos($content, 'Journal') !== false || strpos($content, 'Jurnal') !== false) {
            $journalModels[] = basename($model);
        }
    }
    
    echo "Model jurnal yang ditemukan:\n";
    foreach ($journalModels as $model) {
        echo "- " . $model . "\n";
    }
    
} catch (\Exception $e) {
    echo "Error checking models: " . $e->getMessage() . "\n";
}

echo "\n5. CEK CONTROLLER YANG MENGGUNAKAN JURNAL:\n\n";

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
    
    echo "Controller yang menggunakan jurnal:\n";
    foreach ($journalControllers as $controller) {
        echo "- " . $controller . "\n";
    }
    
} catch (\Exception $e) {
    echo "Error checking controllers: " . $e->getMessage() . "\n";
}

echo "\n6. ANALISIS KEBUTUHAN KONSOLIDASI:\n\n";

echo "Berdasarkan analisis:\n";
echo "1. Jurnal_umum sudah memiliki struktur yang lengkap\n";
echo "2. user_id column ada untuk multi-tenant\n";
echo "3. Tipe_referensi bisa mengidentifikasi jenis transaksi\n";
echo "4. Referensi bisa link ke transaksi asli\n\n";

echo "Tindakan yang diperlukan:\n";
echo "1. Backup data dari journal_entries & jurnal_lines (jika ada)\n";
echo "2. Update controllers untuk menggunakan jurnal_umum\n";
echo "3. Update models untuk menggunakan jurnal_umum\n";
echo "4. Hapus tabel journal_entries & jurnal_lines\n";
echo "5. Test semua fungsi jurnal dengan jurnal_umum\n\n";

echo "=== IDENTIFIKASI SELESAI ===\n";
