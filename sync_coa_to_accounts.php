<?php
/**
 * Script untuk sync akun dari Coa table ke Account table
 * Jalankan: php sync_coa_to_accounts.php
 */

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Coa;
use App\Models\Account;

echo "\n" . str_repeat("=", 100) . "\n";
echo "SYNC: Coa Table → Account Table\n";
echo str_repeat("=", 100) . "\n\n";

// Get all Coa records
$coas = Coa::all();
$synced = 0;
$skipped = 0;
$errors = 0;

foreach ($coas as $coa) {
    try {
        // Check if account already exists
        $exists = Account::where('kode_akun', $coa->kode_akun)
            ->where('user_id', $coa->user_id)
            ->exists();
        
        if ($exists) {
            $skipped++;
            continue;
        }
        
        // Create account from coa
        Account::create([
            'kode_akun' => $coa->kode_akun,
            'nama_akun' => $coa->nama_akun,
            'tipe_akun' => $coa->tipe_akun,
            'kategori_akun' => $coa->kategori_akun ?? null,
            'saldo_normal' => $coa->saldo_normal,
            'keterangan' => $coa->keterangan ?? null,
            'saldo_awal' => $coa->saldo_awal ?? 0,
            'tanggal_saldo_awal' => $coa->tanggal_saldo_awal ?? null,
            'posted_saldo_awal' => $coa->posted_saldo_awal ?? false,
            'user_id' => $coa->user_id,
        ]);
        
        $synced++;
        echo "✅ Synced: {$coa->kode_akun} - {$coa->nama_akun}\n";
        
    } catch (\Exception $e) {
        $errors++;
        echo "❌ Error syncing {$coa->kode_akun}: {$e->getMessage()}\n";
    }
}

echo "\n" . str_repeat("=", 100) . "\n";
echo "SUMMARY\n";
echo str_repeat("=", 100) . "\n";
echo "✅ Synced: {$synced}\n";
echo "⏭️  Skipped (already exists): {$skipped}\n";
echo "❌ Errors: {$errors}\n";
echo str_repeat("=", 100) . "\n\n";

// Verify akun 5 (Beban)
echo "Verifying Beban Accounts (kode 5x):\n";
$bebanAccounts = Account::where('kode_akun', 'like', '5%')->get();
echo "Total Beban Accounts: {$bebanAccounts->count()}\n";

if ($bebanAccounts->count() > 0) {
    echo "\nBeban Accounts:\n";
    foreach ($bebanAccounts as $acc) {
        echo "  - {$acc->kode_akun}: {$acc->nama_akun}\n";
    }
} else {
    echo "⚠️  No Beban Accounts found!\n";
}

echo "\n";
