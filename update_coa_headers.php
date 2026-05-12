<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Updating COA account headers...\n\n";

// COA updates with correct headers
$coaUpdates = [
    // Ayam potong dan ayam kampung -> header persediaan bahan baku (1104)
    '12111' => [
        'nama_akun' => 'Persediaan Ayam Potong',
        'kategori_akun' => 'Persediaan Bahan Baku',
        'kode_induk' => '1104'
    ],
    '12112' => [
        'nama_akun' => 'Persediaan Ayam Kampung',
        'kategori_akun' => 'Persediaan Bahan Baku',
        'kode_induk' => '1104'
    ],
    
    // Akun baru lainnya -> header 1107 Persediaan Bahan Pendukung
    '1210' => [
        'nama_akun' => 'Persediaan Bahan Baku',
        'kategori_akun' => 'Persediaan Bahan Baku',
        'kode_induk' => '1104'
    ],
    '1220' => [
        'nama_akun' => 'Persediaan Bahan Penolong',
        'kategori_akun' => 'Persediaan Bahan Pendukung',
        'kode_induk' => '1107'
    ],
    '122111' => [
        'nama_akun' => 'Persediaan Air',
        'kategori_akun' => 'Persediaan Bahan Pendukung',
        'kode_induk' => '1107'
    ],
    '122112' => [
        'nama_akun' => 'Persediaan Minyak Goreng',
        'kategori_akun' => 'Persediaan Bahan Pendukung',
        'kode_induk' => '1107'
    ],
    '122113' => [
        'nama_akun' => 'Persediaan Gas',
        'kategori_akun' => 'Persediaan Bahan Pendukung',
        'kode_induk' => '1107'
    ],
    '122114' => [
        'nama_akun' => 'Persediaan Ketumbar',
        'kategori_akun' => 'Persediaan Bahan Pendukung',
        'kode_induk' => '1107'
    ],
    '122115' => [
        'nama_akun' => 'Persediaan Cabe Merah',
        'kategori_akun' => 'Persediaan Bahan Pendukung',
        'kode_induk' => '1107'
    ],
    '122116' => [
        'nama_akun' => 'Persediaan Bawang Putih',
        'kategori_akun' => 'Persediaan Bahan Pendukung',
        'kode_induk' => '1107'
    ],
    '122117' => [
        'nama_akun' => 'Persediaan Tepung Maizena',
        'kategori_akun' => 'Persediaan Bahan Pendukung',
        'kode_induk' => '1107'
    ],
    '122118' => [
        'nama_akun' => 'Persediaan Merica Bubuk',
        'kategori_akun' => 'Persediaan Bahan Pendukung',
        'kode_induk' => '1107'
    ],
    '122119' => [
        'nama_akun' => 'Persediaan Listrik',
        'kategori_akun' => 'Persediaan Bahan Pendukung',
        'kode_induk' => '1107'
    ],
    '122120' => [
        'nama_akun' => 'Persediaan Bawang Merah',
        'kategori_akun' => 'Persediaan Bahan Pendukung',
        'kode_induk' => '1107'
    ],
    '122121' => [
        'nama_akun' => 'Persediaan Kemasan',
        'kategori_akun' => 'Persediaan Bahan Pendukung',
        'kode_induk' => '1107'
    ]
];

try {
    $successCount = 0;
    $errorCount = 0;
    
    foreach ($coaUpdates as $kodeAkun => $updateData) {
        try {
            // Check if COA exists
            $coa = \DB::table('coas')
                ->where('kode_akun', $kodeAkun)
                ->first();
            
            if ($coa) {
                $namaLama = $coa->nama_akun;
                $headerLama = $coa->kode_induk;
                
                // Update COA
                \DB::table('coas')
                    ->where('kode_akun', $kodeAkun)
                    ->update([
                        'nama_akun' => $updateData['nama_akun'],
                        'kategori_akun' => $updateData['kategori_akun'],
                        'kode_induk' => $updateData['kode_induk'],
                        'updated_at' => now()
                    ]);
                
                echo "✓ Updated: $kodeAkun\n";
                echo "  Old: $namaLama (Header: $headerLama)\n";
                echo "  New: {$updateData['nama_akun']} (Header: {$updateData['kode_induk']})\n\n";
                $successCount++;
            } else {
                echo "✗ Not found: $kodeAkun\n";
                $errorCount++;
            }
            
        } catch (\Exception $e) {
            echo "✗ Error updating $kodeAkun: " . $e->getMessage() . "\n";
            $errorCount++;
        }
    }
    
    echo "=== UPDATE COMPLETE ===\n";
    echo "Success: $successCount accounts updated\n";
    echo "Errors: $errorCount accounts\n\n";
    
    // Show summary by header
    echo "Summary by Account Header:\n";
    echo str_repeat("=", 60) . "\n";
    
    $summary = \DB::table('coas')
        ->selectRaw('kode_induk, COUNT(*) as count')
        ->whereNotNull('kode_induk')
        ->groupBy('kode_induk')
        ->orderBy('kode_induk')
        ->get();
    
    foreach ($summary as $item) {
        $headerName = \DB::table('coas')
            ->where('kode_akun', $item->kode_induk)
            ->value('nama_akun');
        
        echo sprintf("%-8s %-40s %d accounts\n", 
            $item->kode_induk, 
            $headerName ?? 'Unknown', 
            $item->count
        );
    }
    
    echo str_repeat("=", 60) . "\n";
    
    // Show updated accounts
    echo "\nUpdated COA Accounts:\n";
    echo str_repeat("=", 80) . "\n";
    
    $updated = \DB::table('coas')
        ->whereIn('kode_akun', array_keys($coaUpdates))
        ->orderBy('kode_akun')
        ->get(['kode_akun', 'nama_akun', 'kategori_akun', 'kode_induk']);
    
    foreach ($updated as $coa) {
        echo sprintf("%-8s %-35s %-25s %-8s\n", 
            $coa->kode_akun, 
            $coa->nama_akun, 
            $coa->kategori_akun,
            $coa->kode_induk
        );
    }
    
    echo str_repeat("=", 80) . "\n";
    echo "✅ COA account headers have been successfully updated!\n";
    
} catch (\Exception $e) {
    echo "COA update failed: " . $e->getMessage() . "\n";
}
