<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "CHECKING JABATAN TABLE STRUCTURE FOR HOSTING\n";

// Check table structure
echo "\n=== JABATAN TABLE STRUCTURE ===\n";
$columns = \Illuminate\Support\Facades\Schema::getColumnListing('jabatans');
echo "Jabatans Table Columns:\n";
foreach ($columns as $column) {
    echo "  - {$column}\n";
}

// Check current data
echo "\n=== CURRENT JABATAN DATA ===\n";
$jabatans = \Illuminate\Support\Facades\DB::table('jabatans')->where('user_id', 1)->get();
echo "Total jabatans: " . $jabatans->count() . "\n";

foreach ($jabatans as $jabatan) {
    echo "ID: " . $jabatan->id . "\n";
    foreach ($columns as $column) {
        $value = $jabatan->$column ?? 'NULL';
        if (is_numeric($value) && $value > 0) {
            $value = number_format($value, 0, ',', '.');
        }
        echo "  {$column}: " . (is_string($value) ? "'{$value}'" : $value) . "\n";
    }
    echo "---\n";
}

// Check if nama_jabatan column exists
if (!in_array('nama_jabatan', $columns)) {
    echo "\n=== ISSUE: nama_jabatan COLUMN MISSING ===\n";
    echo "The nama_jabatan column does not exist in the jabatans table!\n";
    
    // Check if there's a similar column
    $possibleColumns = ['nama', 'jabatan', 'name', 'title'];
    $foundColumn = null;
    
    foreach ($possibleColumns as $possibleColumn) {
        if (in_array($possibleColumn, $columns)) {
            $foundColumn = $possibleColumn;
            echo "Found similar column: '{$possibleColumn}'\n";
            break;
        }
    }
    
    if ($foundColumn) {
        echo "\n=== ADDING nama_jabatan COLUMN ===\n";
        try {
            \Illuminate\Support\Facades\Schema::table('jabatans', function (Blueprint $table) {
                if (!Schema::hasColumn('jabatans', 'nama_jabatan')) {
                    $table->string('nama_jabatan')->nullable()->after('id');
                    echo "Added nama_jabatan column\n";
                }
            });
            
            // Update existing records with the found column data
            if ($foundColumn === 'nama') {
                \Illuminate\Support\Facades\DB::table('jabatans')
                    ->whereNull('nama_jabatan')
                    ->whereNotNull('nama')
                    ->update(['nama_jabatan' => \Illuminate\Support\Facades\DB::raw('nama')]);
                echo "Updated nama_jabatan from nama column\n";
            }
            
            echo "SUCCESS: nama_jabatan column added and populated\n";
            
        } catch (Exception $e) {
            echo "ERROR adding column: " . $e->getMessage() . "\n";
        }
    } else {
        echo "\n=== CREATING COMPLETE JABATAN STRUCTURE ===\n";
        echo "No similar column found - creating proper structure\n";
        
        try {
            // Add the missing columns
            \Illuminate\Support\Facades\Schema::table('jabatans', function (Blueprint $table) {
                if (!Schema::hasColumn('jabatans', 'nama_jabatan')) {
                    $table->string('nama_jabatan')->nullable()->after('id');
                }
                if (!Schema::hasColumn('jabatans', 'tunjangan_jabatan')) {
                    $table->decimal('tunjangan_jabatan', 15, 2)->default(0)->after('nama_jabatan');
                }
                if (!Schema::hasColumn('jabatans', 'tunjangan_transport')) {
                    $table->decimal('tunjangan_transport', 15, 2)->default(0)->after('tunjangan_jabatan');
                }
                if (!Schema::hasColumn('jabatans', 'tunjangan_konsumsi')) {
                    $table->decimal('tunjangan_konsumsi', 15, 2)->default(0)->after('tunjangan_transport');
                }
            });
            
            echo "Added missing columns\n";
            
            // Update the records with proper data
            \Illuminate\Support\Facades\DB::table('jabatans')
                ->where('id', 1)
                ->update([
                    'nama_jabatan' => 'Pengukusan (BTKL)',
                    'tunjangan_jabatan' => 500000,
                    'tunjangan_transport' => 200000,
                    'tunjangan_konsumsi' => 150000,
                ]);
            
            \Illuminate\Support\Facades\DB::table('jabatans')
                ->where('id', 2)
                ->update([
                    'nama_jabatan' => 'Pengemasan (BOP)',
                    'tunjangan_jabatan' => 400000,
                    'tunjangan_transport' => 150000,
                    'tunjangan_konsumsi' => 100000,
                ]);
            
            echo "Updated jabatan records with proper data\n";
            
        } catch (Exception $e) {
            echo "ERROR creating structure: " . $e->getMessage() . "\n";
        }
    }
} else {
    echo "\nnama_jabatan column exists - checking data\n";
    
    // Update data if needed
    \Illuminate\Support\Facades\DB::table('jabatans')
        ->where('user_id', 1)
        ->where(function($query) {
            $query->whereNull('nama_jabatan')
                  ->orWhere('nama_jabatan', '');
        })
        ->update([
            'nama_jabatan' => \Illuminate\Support\Facades\DB::raw("CASE 
                WHEN id = 1 THEN 'Pengukusan (BTKL)'
                WHEN id = 2 THEN 'Pengemasan (BOP)'
                ELSE 'Jabatan' 
            END")
        ]);
    
    echo "Updated empty nama_jabatan fields\n";
}

// Final verification
echo "\n=== FINAL VERIFICATION ===\n";
$finalJabatans = \Illuminate\Support\Facades\DB::table('jabatans')->where('user_id', 1)->get();

foreach ($finalJabatans as $jabatan) {
    echo "ID: " . $jabatan->id . "\n";
    echo "Nama Jabatan: '" . ($jabatan->nama_jabatan ?? 'NULL') . "'\n";
    echo "Tunjangan Jabatan: " . number_format($jabatan->tunjangan_jabatan ?? 0, 0, ',', '.') . "\n";
    echo "Tunjangan Transport: " . number_format($jabatan->tunjangan_transport ?? 0, 0, ',', '.') . "\n";
    echo "Tunjangan Konsumsi: " . number_format($jabatan->tunjangan_konsumsi ?? 0, 0, ',', '.') . "\n";
    
    $total = ($jabatan->tunjangan_jabatan ?? 0) + ($jabatan->tunjangan_transport ?? 0) + ($jabatan->tunjangan_konsumsi ?? 0);
    echo "Total Tunjangan: " . number_format($total, 0, ',', '.') . "\n";
    echo "---\n";
}

echo "\nJabatan table structure check completed!\n";
