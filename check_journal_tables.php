<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "CHECKING JOURNAL TABLES STRUCTURE\n";
echo "================================\n\n";

// Check all tables related to journals
echo "Tables related to journals:\n";
echo "=========================\n";

$tables = \DB::select("SHOW TABLES LIKE '%jurnal%'");
echo "Tables with 'jurnal':\n";
foreach ($tables as $table) {
    foreach ($table as $tableName) {
        echo "  - $tableName\n";
        
        try {
            $count = \DB::table($tableName)->count();
            echo "    Records: $count\n";
            
            if ($count > 0) {
                // Show table structure
                $columns = \DB::select("SHOW COLUMNS FROM $tableName");
                echo "    Columns:\n";
                foreach ($columns as $column) {
                    echo "      - {$column->Field} ({$column->Type})\n";
                }
                
                // Show sample data
                $sample = \DB::table($tableName)->limit(2)->get();
                echo "    Sample data:\n";
                foreach ($sample as $row) {
                    echo "      " . json_encode($row) . "\n";
                }
            }
        } catch (\Exception $e) {
            echo "    Error: " . $e->getMessage() . "\n";
        }
        echo "\n";
    }
}

// Check COA table
echo "COA table structure:\n";
echo "===================\n";

try {
    $coaColumns = \DB::select("SHOW COLUMNS FROM coas");
    echo "COA table columns:\n";
    foreach ($coaColumns as $column) {
        echo "  - {$column->Field} ({$column->Type})\n";
    }
    
    // Find Ayam Goreng Bundo COA
    $ayamGorengCoa = \DB::table('coas')
        ->where('nama_akun', 'like', '%Ayam Goreng Bundo%')
        ->first();
    
    if ($ayamGorengCoa) {
        echo "\nAyam Goreng Bundo COA found:\n";
        echo "  ID: {$ayamGorengCoa->id}\n";
        echo "  Kode Akun: {$ayamGorengCoa->kode_akun}\n";
        echo "  Nama Akun: {$ayamGorengCoa->nama_akun}\n";
        echo "  Tipe Akun: {$ayamGorengCoa->tipe_akun}\n";
        echo "  Kategori: {$ayamGorengCoa->kategori_akun}\n";
    } else {
        echo "\nAyam Goreng Bundo COA not found!\n";
    }
    
} catch (\Exception $e) {
    echo "Error checking COA table: " . $e->getMessage() . "\n";
}

// Check jurnal_umum table specifically
echo "\nJURNAL_UMUM table structure:\n";
echo "==========================\n";

try {
    $jurnalColumns = \DB::select("SHOW COLUMNS FROM jurnal_umum");
    echo "Jurnal_umum columns:\n";
    foreach ($jurnalColumns as $column) {
        echo "  - {$column->Field} ({$column->Type})\n";
    }
    
    // Show sample jurnal_umum data
    $sampleJurnal = \DB::table('jurnal_umum')
        ->leftJoin('coas', 'coas.id', '=', 'jurnal_umum.coa_id')
        ->select('jurnal_umum.*', 'coas.kode_akun', 'coas.nama_akun')
        ->limit(3)
        ->get();
    
    echo "\nSample jurnal_umum data with COA:\n";
    foreach ($sampleJurnal as $journal) {
        echo "  ID: {$journal->id}\n";
        echo "  COA ID: {$journal->coa_id}\n";
        echo "  COA Kode: {$journal->kode_akun}\n";
        echo "  COA Nama: {$journal->nama_akun}\n";
        echo "  Debit: {$journal->debit}\n";
        echo "  Kredit: {$journal->kredit}\n";
        echo "  Keterangan: {$journal->keterangan}\n";
        echo "  ---\n";
    }
    
} catch (\Exception $e) {
    echo "Error checking jurnal_umum table: " . $e->getMessage() . "\n";
}

echo "\nSUMMARY:\n";
echo "========\n";
echo "1. COA accounts are stored in 'coas' table\n";
echo "2. Journal entries are stored in 'jurnal_umum' table\n";
echo "3. jurnal_umum.coa_id links to coas.id\n";
echo "4. COA Ayam Goreng Bundo should be in coas table\n";

?>
