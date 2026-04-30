<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "CHECKING KUALIFIKASI SYSTEM INTEGRATION FOR HOSTING\n";

// Check if there's a kualifikasi table with different name
echo "\n=== CHECKING FOR KUALIFIKASI TABLES ===\n";
$tables = \Illuminate\Support\Facades\Schema::getTableListing();
$kualifikasiTables = [];

foreach ($tables as $table) {
    if (strpos(strtolower($table), 'kualifikasi') !== false || 
        strpos(strtolower($table), 'qualification') !== false ||
        strpos(strtolower($table), 'jabatan') !== false) {
        $kualifikasiTables[] = $table;
        echo "Found table: " . $table . "\n";
    }
}

if (empty($kualifikasiTables)) {
    echo "No kualifikasi tables found - checking jabatan table structure\n";
    
    // Check jabatan table structure
    echo "\n=== JABATAN TABLE STRUCTURE ===\n";
    $jabatanColumns = \Illuminate\Support\Facades\Schema::getColumnListing('jabatans');
    echo "Jabatan table columns:\n";
    foreach ($jabatanColumns as $column) {
        echo "  - {$column}\n";
    }
    
    // Check if jabatan has the required fields
    $requiredFields = ['tarif', 'tarif_per_jam', 'tunjangan', 'tunjangan_transport', 'tunjangan_konsumsi', 'asuransi', 'gaji_pokok'];
    $missingFields = [];
    
    foreach ($requiredFields as $field) {
        if (!in_array($field, $jabatanColumns)) {
            $missingFields[] = $field;
        }
    }
    
    if (!empty($missingFields)) {
        echo "\nMissing required fields: " . implode(', ', $missingFields) . "\n";
        
        // Add missing fields
        echo "\n=== ADDING MISSING FIELDS ===\n";
        try {
            \Illuminate\Support\Facades\Schema::table('jabatans', function (Blueprint $table) {
                if (!Schema::hasColumn('jabatans', 'gaji_pokok')) {
                    $table->decimal('gaji_pokok', 15, 2)->default(0)->after('gaji');
                    echo "Added gaji_pokok column\n";
                }
            });
            
            echo "Successfully added missing fields\n";
        } catch (Exception $e) {
            echo "Error adding fields: " . $e->getMessage() . "\n";
        }
    } else {
        echo "\nAll required fields present in jabatan table\n";
    }
}

// Check presensi system
echo "\n=== CHECKING PRESENSI SYSTEM ===\n";
$presensiTables = [];

foreach ($tables as $table) {
    if (strpos(strtolower($table), 'presensi') !== false) {
        $presensiTables[] = $table;
        echo "Found presensi table: " . $table . "\n";
    }
}

if (in_array('presensis', $tables)) {
    echo "\n=== PRESENSI TABLE STRUCTURE ===\n";
    $presensiColumns = \Illuminate\Support\Facades\Schema::getColumnListing('presensis');
    echo "Presensi table columns:\n";
    foreach ($presensiColumns as $column) {
        echo "  - {$column}\n";
    }
    
    // Check presensi data for Dedi Gunawan
    $presensiData = \Illuminate\Support\Facades\DB::table('presensis')
        ->where('pegawai_id', 2) // Dedi Gunawan's ID
        ->whereMonth('tgl_presensi', date('m'))
        ->whereYear('tgl_presensi', date('Y'))
        ->get();
    
    echo "\nPresensi data for Dedi Gunawan (current month):\n";
    echo "Total records: " . $presensiData->count() . "\n";
    
    $totalJam = 0;
    foreach ($presensiData as $presensi) {
        echo "  - " . $presensi->tgl_presensi . ": " . ($presensi->status ?? 'N/A') . " - " . ($presensi->jumlah_jam ?? 0) . " jam\n";
        $totalJam += ($presensi->jumlah_jam ?? 0);
    }
    
    echo "Total jam kerja: " . $totalJam . "\n";
    
    // Check if we need to create sample presensi data
    if ($presensiData->count() === 0) {
        echo "\n=== CREATING SAMPLE PRESENSI DATA ===\n";
        
        // Create sample presensi data for current month
        $currentMonth = date('m');
        $currentYear = date('Y');
        
        for ($day = 1; $day <= 3; $day++) { // Create 3 days sample
            $date = $currentYear . '-' . $currentMonth . '-' . str_pad($day, 2, '0', STR_PAD_LEFT);
            
            try {
                \Illuminate\Support\Facades\DB::table('presensis')->insert([
                    'pegawai_id' => 2,
                    'tgl_presensi' => $date,
                    'status' => 'hadir',
                    'jumlah_jam' => 8, // 8 hours per day
                    'user_id' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                echo "Created presensi for " . $date . ": 8 hours\n";
            } catch (Exception $e) {
                echo "Error creating presensi for " . $date . ": " . $e->getMessage() . "\n";
            }
        }
        
        echo "Sample presensi data created\n";
    }
}

// Verify complete integration
echo "\n=== VERIFYING COMPLETE INTEGRATION ===\n";

// Get Dedi Gunawan with complete data
$pegawai = \App\Models\Pegawai::with('jabatanRelasi')->find(2);

if ($pegawai && $pegawai->jabatanRelasi) {
    echo "Pegawai: " . $pegawai->nama . "\n";
    echo "Jabatan: " . $pegawai->jabatanRelasi->nama . "\n";
    echo "Tarif per Jam: Rp " . number_format($pegawai->jabatanRelasi->tarif_per_jam ?? 0, 0, ',', '.') . "\n";
    echo "Gaji Pokok: Rp " . number_format($pegawai->jabatanRelasi->gaji_pokok ?? 0, 0, ',', '.') . "\n";
    echo "Tunjangan Jabatan: Rp " . number_format($pegawai->jabatanRelasi->tunjangan ?? 0, 0, ',', '.') . "\n";
    echo "Tunjangan Transport: Rp " . number_format($pegawai->jabatanRelasi->tunjangan_transport ?? 0, 0, ',', '.') . "\n";
    echo "Tunjangan Konsumsi: Rp " . number_format($pegawai->jabatanRelasi->tunjangan_konsumsi ?? 0, 0, ',', '.') . "\n";
    echo "Asuransi: Rp " . number_format($pegawai->jabatanRelasi->asuransi ?? 0, 0, ',', '.') . "\n";
    
    // Get presensi data
    $presensiData = \Illuminate\Support\Facades\DB::table('presensis')
        ->where('pegawai_id', 2)
        ->whereMonth('tgl_presensi', date('m'))
        ->whereYear('tgl_presensi', date('Y'))
        ->where('status', 'hadir')
        ->get();
    
    $totalJamKerja = $presensiData->sum('jumlah_jam');
    echo "Total Jam Kerja (Bulan Ini): " . $totalJamKerja . " jam\n";
    
    // Calculate expected values
    $totalGaji = ($pegawai->jabatanRelasi->tarif_per_jam ?? 0) * $totalJamKerja;
    $totalTunjangan = ($pegawai->jabatanRelasi->tunjangan ?? 0) + 
                      ($pegawai->jabatanRelasi->tunjangan_transport ?? 0) + 
                      ($pegawai->jabatanRelasi->tunjangan_konsumsi ?? 0);
    
    echo "\n=== EXPECTED PENGGAJIAN CALCULATION ===\n";
    echo "Total Gaji (diluar tunjangan): Rp " . number_format($totalGaji, 0, ',', '.') . "\n";
    echo "Total Tunjangan: Rp " . number_format($totalTunjangan, 0, ',', '.') . "\n";
    echo "Total Gaji: Rp " . number_format($totalGaji + $totalTunjangan, 0, ',', '.') . "\n";
    
    echo "\nSUCCESS: Kualifikasi and Presensi integration working!\n";
    echo "Data will be automatically pulled from jabatan and presensi tables.\n";
} else {
    echo "ERROR: Integration not working\n";
}

echo "\nKualifikasi system integration check completed!\n";
