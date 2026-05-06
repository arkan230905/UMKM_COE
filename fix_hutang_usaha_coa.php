<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== FIXING HUTANG USAHA COA MISMATCH ===\n\n";

// Find the COA id being used in jurnal_umum for Hutang Usaha
echo "1. Finding COA id used in jurnal_umum:\n";
$hutangEntries = DB::table('jurnal_umum as ju')
    ->join('coas', 'ju.coa_id', '=', 'coas.id')
    ->where('ju.user_id', 1)
    ->where('coas.kode_akun', '2101')
    ->select('ju.coa_id', 'coas.kode_akun', 'coas.nama_akun', 'coas.user_id')
    ->distinct()
    ->first();

if ($hutangEntries) {
    echo "   Found: COA ID {$hutangEntries->coa_id} - {$hutangEntries->kode_akun} - {$hutangEntries->nama_akun}\n";
    echo "   User ID: {$hutangEntries->user_id}\n";
    
    // Check if this COA exists in coas table
    $coaExists = DB::table('coas')->where('id', $hutangEntries->coa_id)->exists();
    echo "   COA exists in coas table: " . ($coaExists ? "YES" : "NO") . "\n\n";
    
    // Get the correct COA 210
    $correctCoa = DB::table('coas')
        ->where('kode_akun', '210')
        ->where('user_id', 1)
        ->first();
    
    if ($correctCoa) {
        echo "2. Correct COA found:\n";
        echo "   COA ID {$correctCoa->id} - {$correctCoa->kode_akun} - {$correctCoa->nama_akun}\n\n";
        
        // Count entries that need to be updated
        $countToUpdate = DB::table('jurnal_umum')
            ->where('coa_id', $hutangEntries->coa_id)
            ->where('user_id', 1)
            ->count();
        
        echo "3. Entries to update: {$countToUpdate}\n\n";
        
        // Show the entries before update
        echo "4. Entries before update:\n";
        $entriesToUpdate = DB::table('jurnal_umum')
            ->where('coa_id', $hutangEntries->coa_id)
            ->where('user_id', 1)
            ->get(['id', 'tanggal', 'keterangan', 'debit', 'kredit']);
        
        foreach ($entriesToUpdate as $entry) {
            echo "   ID {$entry->id}: {$entry->tanggal} - {$entry->keterangan} - Dr: {$entry->debit}, Cr: {$entry->kredit}\n";
        }
        
        // Update the entries
        echo "\n5. Updating entries to use correct COA...\n";
        $updated = DB::table('jurnal_umum')
            ->where('coa_id', $hutangEntries->coa_id)
            ->where('user_id', 1)
            ->update(['coa_id' => $correctCoa->id]);
        
        echo "   ✅ Updated {$updated} entries\n\n";
        
        // Verify the update
        echo "6. Verification:\n";
        $newTotal = DB::table('jurnal_umum as ju')
            ->join('coas', 'ju.coa_id', '=', 'coas.id')
            ->where('ju.user_id', 1)
            ->where('coas.kode_akun', '210')
            ->selectRaw('SUM(ju.debit) as total_debit, SUM(ju.kredit) as total_kredit')
            ->first();
        
        echo "   COA 210 - Hutang Usaha:\n";
        echo "   Debit: Rp " . number_format($newTotal->total_debit, 0) . "\n";
        echo "   Kredit: Rp " . number_format($newTotal->total_kredit, 0) . "\n";
        echo "   Saldo: Rp " . number_format($newTotal->total_kredit - $newTotal->total_debit, 0) . "\n";
        
    } else {
        echo "   ❌ Correct COA 210 not found!\n";
    }
} else {
    echo "   No entries found with COA 2101\n";
}
