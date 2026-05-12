<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Restore Working Buku Besar ===" . PHP_EOL;

echo PHP_EOL . "PROBLEM: Syntax error in AkuntansiController" . PHP_EOL;
echo "SOLUTION: Restore to working state" . PHP_EOL;

echo PHP_EOL . "=== FINDING LATEST BACKUP ===" . PHP_EOL;

$backupDir = __DIR__;
$backupFiles = glob($backupDir . '/AkuntansiController_backup_*.php');

if (empty($backupFiles)) {
    echo "❌ No backup files found" . PHP_EOL;
    exit(1);
}

// Sort by modification time (newest first)
usort($backupFiles, function($a, $b) {
    return filemtime($b) - filemtime($a);
});

$latestBackup = $backupFiles[0];
echo "Latest backup: " . basename($latestBackup) . PHP_EOL;

echo PHP_EOL . "=== RESTORING BACKUP ===" . PHP_EOL;

$controllerFile = __DIR__ . '/app/Http/Controllers/AkuntansiController.php';

if (copy($latestBackup, $controllerFile)) {
    echo "✅ Restored controller from backup" . PHP_EOL;
} else {
    echo "❌ Failed to restore from backup" . PHP_EOL;
    exit(1);
}

echo PHP_EOL . "=== VERIFYING RESTORE ===" . PHP_EOL;

// Check if the restored file is syntactically correct
$output = [];
$returnCode = 0;
exec('php -l "' . $controllerFile . '" 2>&1', $output, $returnCode);

if ($returnCode === 0) {
    echo "✅ Restored file has valid PHP syntax" . PHP_EOL;
} else {
    echo "❌ Restored file still has syntax errors:" . PHP_EOL;
    echo implode(PHP_EOL, $output) . PHP_EOL;
    exit(1);
}

echo PHP_EOL . "=== CREATING SIMPLE BUKU BESAR METHOD ===" . PHP_EOL;

// Read the restored controller
$restoredContent = file_get_contents($controllerFile);

// Create a simple, working bukuBesar method
$simpleBukuBesar = '
    public function bukuBesar(Request $request)
    {
        $month = $request->get("month");
        $year = $request->get("year");
        $accountCode = $request->get("account_code");

        // Get all COAs
        $coas = \App\Models\Coa::select("kode_akun", "nama_akun", "tipe_akun")
            ->groupBy("kode_akun", "nama_akun", "tipe_akun")
            ->orderBy("kode_akun")
            ->get();

        $lines = collect();
        $saldoAwal = 0.0;
        $from = null;
        $to = null;
        $totalDebit = 0;
        $totalKredit = 0;
        $saldoAkhir = 0;

        if ($accountCode) {
            $coa = \App\Models\Coa::where("kode_akun", $accountCode)->first();

            if (!$coa) {
                return view("akuntansi.buku-besar", compact("coas","accountCode","lines","from","to","saldoAwal","month","year","totalDebit","totalKredit","saldoAkhir"));
            }

            // Get saldo awal from COA (temporary fix)
            $saldoAwal = (float)($coa->saldo_awal ?? 0);

            // Get journal entries
            $query = \DB::table("journal_entries as je")
                ->leftJoin("journal_lines as jl", "jl.journal_entry_id", "=", "je.id")
                ->leftJoin("coas", "coas.id", "=", "jl.coa_id")
                ->select([
                    "je.*",
                    "jl.id as line_id",
                    "jl.debit",
                    "jl.credit",
                    "jl.memo as line_memo",
                    "coas.kode_akun",
                    "coas.nama_akun",
                    "coas.tipe_akun"
                ])
                ->where(function($q) {
                    $q->where("jl.debit", ">", 0)
                      ->orWhere("jl.credit", ">", 0);
                })
                ->where("coas.kode_akun", $accountCode)
                ->orderBy("je.tanggal","asc")
                ->orderBy("je.id","asc")
                ->orderBy("jl.id","asc");
            
            if ($month && $year) {
                $query->whereMonth("je.tanggal", $month)
                       ->whereYear("je.tanggal", $year);
            }

            $journalLines = $query->get();

            foreach ($journalLines as $line) {
                $lines->push((object) [
                    "tanggal" => $line->tanggal,
                    "keterangan" => $line->memo,
                    "debit" => $line->debit,
                    "kredit" => $line->credit,
                    "saldo" => 0
                ]);
            }

            $totalDebit = $journalLines->sum("debit");
            $totalKredit = $journalLines->sum("credit");
            $saldoAkhir = $saldoAwal + $totalDebit - $totalKredit;
        }

        return view("akuntansi.buku-besar", compact("coas","accountCode","lines","from","to","saldoAwal","month","year","totalDebit","totalKredit","saldoAkhir"));
    }';

// Find the bukuBesar method and replace it
$pattern = '/public function bukuBesar.*?\{.*?\n\}/s';
$finalContent = preg_replace($pattern, $simpleBukuBesar, $restoredContent);

if (file_put_contents($controllerFile, $finalContent)) {
    echo "✅ Added simple bukuBesar method" . PHP_EOL;
} else {
    echo "❌ Failed to update controller" . PHP_EOL;
    exit(1);
}

echo PHP_EOL . "=== FINAL VERIFICATION ===" . PHP_EOL;

// Final syntax check
exec('php -l "' . $controllerFile . '" 2>&1', $output, $returnCode);

if ($returnCode === 0) {
    echo "✅ Final controller has valid PHP syntax" . PHP_EOL;
} else {
    echo "❌ Final controller still has errors:" . PHP_EOL;
    echo implode(PHP_EOL, $output) . PHP_EOL;
    exit(1);
}

echo PHP_EOL . "=== SUMMARY ===" . PHP_EOL;
echo "Action: Restored working controller + added simple bukuBesar" . PHP_EOL;
echo "Result: Controller is now syntactically correct" . PHP_EOL;
echo "Status: SUCCESS" . PHP_EOL;

echo PHP_EOL . "=== NEXT STEPS ===" . PHP_EOL;
echo "1. Test buku-besar page at /akuntansi/buku-besar" . PHP_EOL;
echo "2. Verify page loads without syntax errors" . PHP_EOL;
echo "3. Check if basic functionality works" . PHP_EOL;
echo "4. Later implement neraca-saldo logic if needed" . PHP_EOL;

echo PHP_EOL . "=== COMPLETED ===" . PHP_EOL;
