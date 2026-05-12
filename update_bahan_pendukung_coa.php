<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Updating COA accounts to Bahan Pendukung category...\n\n";

// COA accounts to update
$coaUpdates = [
    '1210' => 'Persediaan Bahan Pendukung - Bahan Baku',
    '12111' => 'Persediaan Bahan Pendukung - Ayam Potong',
    '12112' => 'Persediaan Bahan Pendukung - Ayam Kampung',
    '1220' => 'Persediaan Bahan Pendukung - Umum',
    '122111' => 'Persediaan Bahan Pendukung - Air',
    '122112' => 'Persediaan Bahan Pendukung - Minyak Goreng',
    '122113' => 'Persediaan Bahan Pendukung - Gas',
    '122114' => 'Persediaan Bahan Pendukung - Ketumbar',
    '122115' => 'Persediaan Bahan Pendukung - Cabe Merah',
    '122116' => 'Persediaan Bahan Pendukung - Bawang Putih',
    '122117' => 'Persediaan Bahan Pendukung - Tepung Maizena',
    '122118' => 'Persediaan Bahan Pendukung - Merica Bubuk',
    '122119' => 'Persediaan Bahan Pendukung - Listrik',
    '122120' => 'Persediaan Bahan Pendukung - Bawang Merah',
    '122121' => 'Persediaan Bahan Pendukung - Kemasan'
];

try {
    $successCount = 0;
    $errorCount = 0;
    
    foreach ($coaUpdates as $kodeAkun => $namaBaru) {
        try {
            // Check if COA exists
            $coa = \DB::table('coas')
                ->where('kode_akun', $kodeAkun)
                ->first();
            
            if ($coa) {
                $namaLama = $coa->nama_akun;
                
                // Update COA
                \DB::table('coas')
                    ->where('kode_akun', $kodeAkun)
                    ->update([
                        'nama_akun' => $namaBaru,
                        'kategori_akun' => 'Persediaan Bahan Pendukung',
                        'updated_at' => now()
                    ]);
                
                echo "✓ Updated: $kodeAkun\n";
                echo "  Old: $namaLama\n";
                echo "  New: $namaBaru\n\n";
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
    
    // Show updated COA summary
    echo "Updated Bahan Pendukung COA accounts:\n";
    echo str_repeat("=", 60) . "\n";
    
    $updated = \DB::table('coas')
        ->where('kategori_akun', 'Persediaan Bahan Pendukung')
        ->orWhere('nama_akun', 'like', '%Bahan Pendukung%')
        ->orderBy('kode_akun')
        ->get(['kode_akun', 'nama_akun', 'tipe_akun']);
    
    foreach ($updated as $coa) {
        echo sprintf("%-8s %-50s %-15s\n", 
            $coa->kode_akun, 
            $coa->nama_akun, 
            $coa->tipe_akun
        );
    }
    
    echo str_repeat("=", 60) . "\n";
    echo "Total Bahan Pendukung COA: " . $updated->count() . " accounts\n";
    
    echo "\n✅ All COA accounts have been updated to Bahan Pendukung category!\n";
    
} catch (\Exception $e) {
    echo "COA update failed: " . $e->getMessage() . "\n";
}
