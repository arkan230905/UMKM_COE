<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

// Check categories table directly
echo "Checking kategori_pegawais table:\n";
echo "===================================\n";

try {
    $categories = \Illuminate\Support\Facades\DB::table('kategori_pegawais')->get();
    echo "Total categories in database: " . $categories->count() . "\n";
    
    foreach ($categories as $category) {
        echo "- ID: {$category->id}, Nama: '{$category->nama}'\n";
    }
    
    echo "\nChecking pegawais table kategori_id values:\n";
    echo "==========================================\n";
    
    $employees = \Illuminate\Support\Facades\DB::table('pegawais')
        ->select('id', 'nama', 'kategori_id', 'jabatan_id')
        ->get();
    
    foreach ($employees as $employee) {
        echo "Employee: {$employee->nama} (ID: {$employee->id})\n";
        echo "  kategori_id: " . ($employee->kategori_id ?? 'NULL') . "\n";
        echo "  jabatan_id: " . ($employee->jabatan_id ?? 'NULL') . "\n";
        echo "  --- \n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
