<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Manual Fix Buku Besar Syntax Error ===" . PHP_EOL;

echo PHP_EOL . "PROBLEM: Syntax error in AkuntansiController.php line 460" . PHP_EOL;
echo "ERROR: unexpected variable \"\$month\", expecting \";\" or \"{\"" . PHP_EOL;

echo PHP_EOL . "=== READING CURRENT CONTROLLER ===" . PHP_EOL;

$controllerFile = __DIR__ . '/app/Http/Controllers/AkuntansiController.php';
$content = file_get_contents($controllerFile);

echo PHP_EOL . "=== FINDING BROKEN METHOD ===" . PHP_EOL;

// Find the bukuBesar method and extract surrounding lines
$lines = file($controllerFile);
$problemLine = 459; // Line 460 in editor is 459 in array (0-based)

echo "Lines around the error:" . PHP_EOL;
for ($i = max(0, $problemLine - 5); $i < min(count($lines), $problemLine + 10); $i++) {
    $lineNum = $i + 1;
    $prefix = ($lineNum == 460) ? ">>> " : "    ";
    echo $prefix . $lineNum . ": " . $lines[$i] . PHP_EOL;
}

echo PHP_EOL . "=== ANALYZING THE ERROR ===" . PHP_EOL;

// Look for the specific issue
if (isset($lines[$problemLine])) {
    $problematicLine = $lines[$problemLine];
    echo "Problematic line: " . $problematicLine . PHP_EOL;
    
    // Check for common syntax issues
    if (strpos($problematicLine, '$month') !== false) {
        echo "✅ Contains \$month variable" . PHP_EOL;
    }
    
    if (strpos($problematicLine, '{') !== false) {
        echo "✅ Contains opening brace" . PHP_EOL;
    }
    
    // Check if there's a missing semicolon in previous line
    if (isset($lines[$problemLine - 1])) {
        $prevLine = $lines[$problemLine - 1];
        if (strpos($prevLine, '$request->') !== false && strpos($prevLine, ';') === false) {
            echo "❌ Previous line might be missing semicolon: " . $prevLine . PHP_EOL;
        }
    }
}

echo PHP_EOL . "=== CREATING SIMPLE WORKING METHOD ===" . PHP_EOL;

// Create a simple, working bukuBesar method
$simpleMethod = '
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

            // Get saldo awal from COA
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

echo PHP_EOL . "=== REPLACING BROKEN METHOD ===" . PHP_EOL;

// Find the exact bukuBesar method and replace it
$pattern = '/public function bukuBesar.*?\{.*?\n\}/s';
$replacement = $simpleMethod;

if (preg_match($pattern, $content)) {
    $newContent = preg_replace($pattern, $replacement, $content);
    
    if (file_put_contents($controllerFile, $newContent)) {
        echo "✅ Successfully replaced bukuBesar method" . PHP_EOL;
    } else {
        echo "❌ Failed to write to controller file" . PHP_EOL;
    }
} else {
    echo "❌ Could not find bukuBesar method pattern" . PHP_EOL;
}

echo PHP_EOL . "=== VERIFYING FIX ===" . PHP_EOL;

// Check if the file is now syntactically correct
$output = [];
$returnCode = 0;
exec('php -l "' . $controllerFile . '" 2>&1', $output, $returnCode);

if ($returnCode === 0) {
    echo "✅ PHP syntax is now valid" . PHP_EOL;
} else {
    echo "❌ PHP syntax error still exists:" . PHP_EOL;
    echo implode(PHP_EOL, $output) . PHP_EOL;
}

echo PHP_EOL . "=== SUMMARY ===" . PHP_EOL;
echo "Action: Replaced broken bukuBesar method with working version" . PHP_EOL;
echo "Result: " . ($returnCode === 0 ? "SUCCESS - Syntax fixed" : "FAILED - Still has errors") . PHP_EOL;
echo "Next: Test buku-besar page functionality" . PHP_EOL;

echo PHP_EOL . "=== NEXT STEPS ===" . PHP_EOL;
echo "1. Test buku-besar page at /akuntansi/buku-besar" . PHP_EOL;
echo "2. Verify page loads without syntax errors" . PHP_EOL;
echo "3. Check if saldo awal calculation works" . PHP_EOL;
echo "4. Later we can implement neraca-saldo logic if needed" . PHP_EOL;
