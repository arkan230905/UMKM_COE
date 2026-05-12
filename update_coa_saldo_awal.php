<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Updating COA saldo awal dates to March 1, 2026...\n\n";

try {
    // Get all COA records
    $coas = \DB::table('coas')->get();
    $totalUpdated = 0;
    $targetDate = '2026-03-01';
    
    echo "Found " . $coas->count() . " COA records\n";
    echo "Updating tanggal_saldo_awal to: $targetDate\n\n";
    
    foreach ($coas as $coa) {
        $oldDate = $coa->tanggal_saldo_awal;
        
        // Update the tanggal_saldo_awal
        $updated = \DB::table('coas')
            ->where('id', $coa->id)
            ->update([
                'tanggal_saldo_awal' => $targetDate,
                'updated_at' => now()
            ]);
        
        if ($updated) {
            echo "✓ Updated: {$coa->kode_akun} - {$coa->nama_akun}\n";
            echo "  Old date: " . ($oldDate ?: 'NULL') . "\n";
            echo "  New date: $targetDate\n\n";
            $totalUpdated++;
        } else {
            echo "- No change needed: {$coa->kode_akun} - {$coa->nama_akun}\n";
        }
    }
    
    echo "=== UPDATE COMPLETE ===\n";
    echo "Total records updated: $totalUpdated\n";
    echo "Total COA records: " . $coas->count() . "\n\n";
    
    // Verify the update
    echo "Verification - Checking updated dates:\n";
    echo str_repeat("=", 50) . "\n";
    
    $updatedCoas = \DB::table('coas')
        ->where('tanggal_saldo_awal', $targetDate)
        ->orderBy('kode_akun')
        ->get(['kode_akun', 'nama_akun', 'tanggal_saldo_awal', 'saldo_awal']);
    
    foreach ($updatedCoas as $coa) {
        echo sprintf("%-8s %-30s %-12s Rp %s\n", 
            $coa->kode_akun, 
            $coa->nama_akun, 
            $coa->tanggal_saldo_awal,
            number_format($coa->saldo_awal, 2)
        );
    }
    
    echo str_repeat("=", 50) . "\n";
    echo "Records with March 1, 2026 date: " . $updatedCoas->count() . "\n";
    
    // Check for any records that might not have been updated
    $notUpdated = \DB::table('coas')
        ->where('tanggal_saldo_awal', '!=', $targetDate)
        ->whereNotNull('tanggal_saldo_awal')
        ->count();
    
    if ($notUpdated > 0) {
        echo "\n⚠️  Warning: $notUpdated records still have different dates\n";
    } else {
        echo "\n✅ All COA records now have March 1, 2026 as saldo awal date\n";
    }
    
    echo "\n🎯 Summary:\n";
    echo "- Target date: March 1, 2026\n";
    echo "- Total COA accounts: " . $coas->count() . "\n";
    echo "- Successfully updated: $totalUpdated\n";
    echo "- Status: " . ($totalUpdated === $coas->count() ? '✅ COMPLETE' : '⚠️ PARTIAL') . "\n";
    
} catch (\Exception $e) {
    echo "❌ Error updating COA saldo awal dates: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
