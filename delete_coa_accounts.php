<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Deleting COA accounts...\n\n";

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
                
                // Check if COA is being used in other tables
                $usageChecks = [
                    'bahan_bakus' => ['coa_pembelian_id', 'coa_persediaan_id', 'coa_hpp_id'],
                    'bahan_pendukungs' => ['coa_pembelian_id', 'coa_persediaan_id', 'coa_hpp_id'],
                    'asets' => ['coa_akumulasi_penyusutan_id', 'coa_beban_penyusutan_id'],
                    'pembelians' => ['coa_id'],
                    'penjualans' => ['coa_id']
                ];
                
                $isUsed = false;
                $usedIn = [];
                
                foreach ($usageChecks as $table => $columns) {
                    foreach ($columns as $column) {
                        $count = \DB::table($table)
                            ->where($column, $kodeAkun)
                            ->count();
                        
                        if ($count > 0) {
                            $isUsed = true;
                            $usedIn[] = "$table.$column ($count records)";
                        }
                    }
                }
                
                if ($isUsed) {
                    echo "⚠️  Cannot delete $kodeAkun - $namaAkun (still used in: " . implode(', ', $usedIn) . ")\n";
                } else {
                    // Delete the COA
                    \DB::table('coas')
                        ->where('kode_akun', $kodeAkun)
                        ->delete();
                    
                    echo "✓ Deleted: $kodeAkun - $namaAkun\n";
                    $successCount++;
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
    
    echo "\n✅ COA deletion process completed!\n";
    
} catch (\Exception $e) {
    echo "COA deletion failed: " . $e->getMessage() . "\n";
}
