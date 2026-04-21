<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Final Buku Besar Fix ===" . PHP_EOL;

echo PHP_EOL . "PROBLEM: Syntax errors in AkuntansiController" . PHP_EOL;
echo "SOLUTION: Create minimal working bukuBesar method" . PHP_EOL;

echo PHP_EOL . "=== BACKUP CONTROLLER ===" . PHP_EOL;

$controllerFile = __DIR__ . '/app/Http/Controllers/AkuntansiController.php';
$backupFile = __DIR__ . '/AkuntansiController_final_backup_' . date('Y-m-d_H-i-s') . '.php';

if (copy($controllerFile, $backupFile)) {
    echo "✅ Backed up controller to: " . basename($backupFile) . PHP_EOL;
} else {
    echo "❌ Failed to backup controller" . PHP_EOL;
    exit(1);
}

echo PHP_EOL . "=== CREATE MINIMAL CONTROLLER ===" . PHP_EOL;

// Read the controller and create a minimal working version
$content = file_get_contents($controllerFile);

// Find where to insert the method (before the last method)
$insertPosition = strrpos($content, '    }');
if ($insertPosition === false) {
    echo "❌ Could not find insertion point" . PHP_EOL;
    exit(1);
}

// Create minimal bukuBesar method
$minimalBukuBesar = '

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

// Insert the method before the last closing brace
$newContent = substr($content, 0, $insertPosition) . $minimalBukuBesar . substr($content, $insertPosition);

echo PHP_EOL . "=== WRITING FIXED CONTROLLER ===" . PHP_EOL;

if (file_put_contents($controllerFile, $newContent)) {
    echo "✅ Successfully created minimal bukuBesar method" . PHP_EOL;
} else {
    echo "❌ Failed to write to controller" . PHP_EOL;
    exit(1);
}

echo PHP_EOL . "=== SYNTAX VERIFICATION ===" . PHP_EOL;

// Check if the file is now syntactically correct
$output = [];
$returnCode = 0;
exec('php -l "' . $controllerFile . '" 2>&1', $output, $returnCode);

if ($returnCode === 0) {
    echo "✅ PHP syntax is now valid" . PHP_EOL;
} else {
    echo "❌ PHP syntax error still exists:" . PHP_EOL;
    echo implode(PHP_EOL, $output) . PHP_EOL;
    exit(1);
}

echo PHP_EOL . "=== SUMMARY ===" . PHP_EOL;
echo "Action: Created minimal working bukuBesar method" . PHP_EOL;
echo "Result: Controller syntax is now valid" . PHP_EOL;
echo "Status: SUCCESS" . PHP_EOL;

echo PHP_EOL . "=== NEXT STEPS ===" . PHP_EOL;
echo "1. Test buku-besar page at /akuntansi/buku-besar" . PHP_EOL;
echo "2. Verify page loads without syntax errors" . PHP_EOL;
echo "3. Check if basic functionality works" . PHP_EOL;
echo "4. Later implement neraca-saldo logic if needed" . PHP_EOL;

echo PHP_EOL . "=== COMPLETED ===" . PHP_EOL;
