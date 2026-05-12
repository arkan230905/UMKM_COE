<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== FIXING PRODUCTION JOURNAL ENTRIES ===\n\n";

// Step 1: Verify WIP COAs exist
echo "1. Verifying WIP COAs exist:\n";
$wipCoas = DB::table('coas')
    ->whereIn('kode_akun', ['1171', '1172', '1173'])
    ->where('user_id', 1)
    ->get(['id', 'kode_akun', 'nama_akun']);

if ($wipCoas->count() < 3) {
    echo "   ❌ ERROR: Not all WIP COAs exist!\n";
    echo "   Found: " . $wipCoas->count() . " out of 3\n";
    foreach ($wipCoas as $coa) {
        echo "   - {$coa->kode_akun}: {$coa->nama_akun}\n";
    }
    exit(1);
}

foreach ($wipCoas as $coa) {
    echo "   ✅ {$coa->id} - {$coa->kode_akun} - {$coa->nama_akun}\n";
}

// Step 2: Check current production journal entries
echo "\n2. Checking current production journal entries:\n";
$prodEntries = DB::table('jurnal_umum')
    ->where('user_id', 1)
    ->whereIn('tipe_referensi', ['produksi_bbb', 'produksi_btkl', 'produksi_bop', 'produksi_transfer'])
    ->count();

echo "   Found {$prodEntries} production journal entries\n";

// Step 3: Show entries using wrong COA (2101)
echo "\n3. Entries using wrong COA (2101 - Hutang Usaha):\n";
$wrongEntries = DB::table('jurnal_umum as ju')
    ->join('coas', 'ju.coa_id', '=', 'coas.id')
    ->where('ju.user_id', 1)
    ->where('coas.kode_akun', '2101')
    ->whereIn('ju.tipe_referensi', ['produksi_bbb', 'produksi_btkl', 'produksi_bop', 'produksi_transfer'])
    ->select('ju.id', 'ju.tanggal', 'ju.keterangan', 'ju.debit', 'ju.kredit', 'ju.tipe_referensi')
    ->get();

echo "   Found " . $wrongEntries->count() . " entries with wrong COA\n";
foreach ($wrongEntries as $entry) {
    echo "   - ID {$entry->id}: {$entry->keterangan} (Dr: {$entry->debit}, Cr: {$entry->kredit})\n";
}

// Step 4: Get production IDs
echo "\n4. Getting production IDs:\n";
$produksiIds = DB::table('jurnal_umum')
    ->where('user_id', 1)
    ->whereIn('tipe_referensi', ['produksi_bbb', 'produksi_btkl', 'produksi_bop', 'produksi_transfer'])
    ->distinct()
    ->pluck('referensi');

echo "   Found " . $produksiIds->count() . " production records: " . $produksiIds->implode(', ') . "\n";

// Step 5: Delete all production journal entries
echo "\n5. Deleting all production journal entries...\n";
$deleted = DB::table('jurnal_umum')
    ->where('user_id', 1)
    ->whereIn('tipe_referensi', ['produksi_bbb', 'produksi_btkl', 'produksi_bop', 'produksi_transfer'])
    ->delete();

echo "   ✅ Deleted {$deleted} entries\n";

// Step 6: Verify deletion
echo "\n6. Verifying deletion:\n";
$remaining = DB::table('jurnal_umum')
    ->where('user_id', 1)
    ->whereIn('tipe_referensi', ['produksi_bbb', 'produksi_btkl', 'produksi_bop', 'produksi_transfer'])
    ->count();

if ($remaining == 0) {
    echo "   ✅ All production journal entries deleted successfully\n";
} else {
    echo "   ❌ WARNING: {$remaining} entries still remain!\n";
}

// Step 7: Check if Hutang Usaha (210) still has balance
echo "\n7. Checking Hutang Usaha balance after deletion:\n";
$hutangBalance = DB::table('jurnal_umum as ju')
    ->join('coas', 'ju.coa_id', '=', 'coas.id')
    ->where('ju.user_id', 1)
    ->where('coas.kode_akun', '210')
    ->selectRaw('SUM(ju.debit) as total_debit, SUM(ju.kredit) as total_kredit')
    ->first();

$saldo = $hutangBalance->total_kredit - $hutangBalance->total_debit;
echo "   Debit: Rp " . number_format($hutangBalance->total_debit, 0) . "\n";
echo "   Kredit: Rp " . number_format($hutangBalance->total_kredit, 0) . "\n";
echo "   Saldo: Rp " . number_format($saldo, 0) . "\n";

if ($saldo == 0) {
    echo "   ✅ Hutang Usaha balance is now zero\n";
}

echo "\n=== NEXT STEPS ===\n";
echo "1. Go to the production page and re-process the production records\n";
echo "2. Production IDs to re-process: " . $produksiIds->implode(', ') . "\n";
echo "3. This will create correct journal entries using proper WIP accounts\n";
