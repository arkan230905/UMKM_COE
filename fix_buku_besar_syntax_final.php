<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Fix Buku Besar Syntax Error - Final ===" . PHP_EOL;

echo PHP_EOL . "PROBLEM: Syntax error in bukuBesar method" . PHP_EOL;
echo "SOLUTION: Create clean, working method" . PHP_EOL;

echo PHP_EOL . "=== READING CURRENT CONTROLLER ===" . PHP_EOL;

$controllerFile = __DIR__ . '/app/Http/Controllers/AkuntansiController.php';
$controllerContent = file_get_contents($controllerFile);

echo PHP_EOL . "=== CREATING CLEAN BUKU BESAR METHOD ===" . PHP_EOL;

// Create a completely clean bukuBesar method
$cleanBukuBesarMethod = '
    public function bukuBesar(Request $request)
    {
        $month = $request->get("month");
        $year = $request->get("year");
        $accountCode = $request->get("account_code");

        // Ambil semua COA yang ada di sistem
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

            // Get saldo awal - sama seperti neraca saldo
            $saldoAwal = (float)($coa->saldo_awal ?? 0);

            // Build query untuk journal entries
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
                ->orderBy("je.created_at","asc")
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
            $totalKredit = $journalLines->sum("kredit");
            $saldoAkhir = $saldoAwal + $totalDebit - $totalKredit;
        }

        return view("akuntansi.buku-besar", compact("coas","accountCode","lines","from","to","saldoAwal","month","year","totalDebit","totalKredit","saldoAkhir"));
    }';

echo PHP_EOL . "=== REPLACING BROKEN METHOD ===" . PHP_EOL;

// Find and replace the broken bukuBesar method
$pattern = '/public function bukuBesar.*?\{(.*?)\n\}/s';
$replacement = $cleanBukuBesarMethod;

if (preg_match($pattern, $controllerContent)) {
    $newControllerContent = preg_replace($pattern, $replacement, $controllerContent);
    
    if (file_put_contents($controllerFile, $newControllerContent)) {
        echo "✅ Successfully replaced bukuBesar method" . PHP_EOL;
    } else {
        echo "❌ Failed to write to controller file" . PHP_EOL;
    }
} else {
    echo "❌ Could not find bukuBesar method to replace" . PHP_EOL;
}

echo PHP_EOL . "=== SYNTAX CHECK ===" . PHP_EOL;

// Check if the file is now syntactically correct
$output = [];
$returnCode = 0;
exec('php -l "' . $controllerFile . '" 2>&1', $output, $returnCode);

if ($returnCode === 0) {
    echo "✅ PHP syntax is now valid" . PHP_EOL;
} else {
    echo "❌ PHP syntax error still exists:" . PHP_EOL;
    echo implode(PHP_EOL, $output) . PHP_EOL;
    
    echo PHP_EOL . "=== ATTEMPTING MANUAL FIX ===" . PHP_EOL;
    
    // Try to identify and fix the specific issue
    $currentContent = file_get_contents($controllerFile);
    
    // Look for malformed method signature
    if (strpos($currentContent, 'public function bukuBesar') !== false) {
        echo "Found bukuBesar method, checking syntax..." . PHP_EOL;
        
        // Check for common issues
        if (preg_match('/public function bukuBesar\s*\([^)]*\)\s*\{/', $currentContent)) {
            echo "✅ Method signature looks correct" . PHP_EOL;
        } else {
            echo "❌ Method signature issue found" . PHP_EOL;
        }
    }
}

echo PHP_EOL . "=== SUMMARY ===" . PHP_EOL;
echo "Action: Replaced bukuBesar method with clean version" . PHP_EOL;
echo "Status: " . ($returnCode === 0 ? "SUCCESS" : "NEEDS MANUAL REVIEW") . PHP_EOL;
echo "Next: Test buku-besar page functionality" . PHP_EOL;

echo PHP_EOL . "=== NEXT STEPS ===" . PHP_EOL;
echo "1. Test buku-besar page at /akuntansi/buku-besar" . PHP_EOL;
echo "2. Verify page loads without syntax errors" . PHP_EOL;
echo "3. Check if saldo awal calculation works" . PHP_EOL;
echo "4. Later implement neraca-saldo logic if needed" . PHP_EOL;
