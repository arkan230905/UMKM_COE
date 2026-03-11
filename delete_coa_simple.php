<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Deleting COA accounts 1110 and 1120...\n\n";

// COA codes to delete
$coaToDelete = ['1110', '1120'];

try {
    $successCount = 0;
    $errorCount = 0;
    
    foreach ($coaToDelete as $kodeAkun) {
        try {
            // Check if COA exists
            $coa = \DB::table('coas')
                ->where('kode_akun', $kodeAkun)
                ->first();
            
            if ($coa) {
                $namaAkun = $coa->nama_akun;
                $coaId = $coa->id;
                
                // Delete the COA
                $deleted = \DB::table('coas')
                    ->where('kode_akun', $kodeAkun)
                    ->delete();
                
                if ($deleted > 0) {
                    echo "✓ Deleted: $kodeAkun - $namaAkun (ID: $coaId)\n";
                    $successCount++;
                } else {
                    echo "- No changes: $kodeAkun - $namaAkun\n";
                }
            } else {
                echo "- Not found: $kodeAkun\n";
            }
            
        } catch (\Exception $e) {
            echo "✗ Error deleting $kodeAkun: " . $e->getMessage() . "\n";
            $errorCount++;
        }
    }
    
    echo "\n=== DELETION COMPLETE ===\n";
    echo "Success: $successCount accounts deleted\n";
    echo "Errors: $errorCount accounts\n\n";
    
    // Show remaining COA summary
    echo "Remaining COA accounts:\n";
    echo str_repeat("=", 50) . "\n";
    
    $remaining = \DB::table('coas')
        ->orderBy('kode_akun')
        ->get(['kode_akun', 'nama_akun', 'tipe_akun']);
    
    foreach ($remaining as $coa) {
        echo sprintf("%-8s %-30s %-15s\n", 
            $coa->kode_akun, 
            $coa->nama_akun, 
            $coa->tipe_akun
        );
    }
    
    echo str_repeat("=", 50) . "\n";
    echo "Total remaining: " . $remaining->count() . " accounts\n";
    
    echo "\n✅ COA accounts 1110 (Kas) and 1120 (Bank) have been deleted!\n";
    
} catch (\Exception $e) {
    echo "COA deletion failed: " . $e->getMessage() . "\n";
}
