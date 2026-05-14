<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

\Illuminate\Support\Facades\Auth::loginUsingId(6);

$p = \App\Models\Penjualan::with('details.produk', 'produk')->find(5);

echo "Rebuilding jurnal penjualan ID 5...\n";

try {
    \App\Services\JournalService::createJournalFromPenjualan($p);
    echo "BERHASIL\n\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n\n";
}

$entry = \App\Models\JournalEntry::with('lines.coa')
    ->where('ref_type', 'sale')->where('ref_id', 5)->first();

if ($entry) {
    $totalD = 0; $totalK = 0;
    foreach ($entry->lines as $line) {
        printf("  [%s] %-35s Dr: %10s  Cr: %10s  %s\n",
            $line->coa->kode_akun,
            $line->coa->nama_akun,
            $line->debit > 0 ? number_format($line->debit,0,',','.') : '-',
            $line->credit > 0 ? number_format($line->credit,0,',','.') : '-',
            $line->memo
        );
        $totalD += $line->debit;
        $totalK += $line->credit;
    }
    echo "\nTotal Dr: " . number_format($totalD,0,',','.') . " | Cr: " . number_format($totalK,0,',','.') . " | " . ($totalD == $totalK ? "BALANCE ✓" : "TIDAK BALANCE ✗") . "\n";
} else {
    echo "Tidak ada journal entry\n";
}

// Cek akun diskon yang terbuat
echo "\nAkun Diskon user 6:\n";
$diskon = \App\Models\Coa::withoutGlobalScopes()->where('nama_akun','like','%Diskon%')->where('user_id',6)->get();
foreach ($diskon as $c) echo "  [{$c->kode_akun}] {$c->nama_akun} | {$c->tipe_akun}\n";
