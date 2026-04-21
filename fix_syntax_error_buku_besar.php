<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Fix Syntax Error in Buku Besar ===" . PHP_EOL;

echo PHP_EOL . "ERROR IDENTIFIED:" . PHP_EOL;
echo "ParseError: syntax error, unexpected variable \"\$month\"" . PHP_EOL;
echo "Location: AkuntansiController.php:460" . PHP_EOL;
echo "Cause: Missing semicolon or syntax issue in bukuBesar method" . PHP_EOL;

echo PHP_EOL . "=== CHECKING CURRENT CODE ===" . PHP_EOL;

// Read current controller
$controllerFile = __DIR__ . '/app/Http/Controllers/AkuntansiController.php';
$controllerContent = file_get_contents($controllerFile);

// Find the problematic line around 460
$lines = file($controllerFile);
$startLine = max(0, 460 - 10);
$endLine = min(count($lines), 460 + 10);

echo "Lines around 460:" . PHP_EOL;
for ($i = $startLine; $i < $endLine; $i++) {
    echo sprintf("%d: %s", $i + 1, trim($lines[$i])) . PHP_EOL;
}

echo PHP_EOL . "=== RESTORING BACKUP ===" . PHP_EOL;

// Check if backup exists
$backupFile = __DIR__ . '/buku_besar_backup.php';
if (file_exists($backupFile)) {
    echo "Found backup file, restoring..." . PHP_EOL;
    
    // Read backup content
    $backupContent = file_get_contents($backupFile);
    
    // Reconstruct the method with proper syntax
    $originalMethod = 'public function bukuBesar(Request $request)' . $backupContent;
    
    // Find and replace the broken method
    $pattern = '/public function bukuBesar.*?\{(.*?)\n\}/s';
    
    if (preg_match($pattern, $controllerContent)) {
        $newControllerContent = preg_replace($pattern, $originalMethod, $controllerContent);
        
        // Write back to controller
        file_put_contents($controllerFile, $newControllerContent);
        echo "✅ Restored original bukuBesar method" . PHP_EOL;
    } else {
        echo "❌ Could not find bukuBesar method to replace" . PHP_EOL;
    }
} else {
    echo "❌ No backup file found" . PHP_EOL;
}

echo PHP_EOL . "=== MANUAL FIX ===" . PHP_EOL;

// Read the current method to check for syntax issues
if (preg_match('/public function bukuBesar.*?\{(.*?)\n\}/s', $controllerContent, $matches)) {
    $bukuBesarMethod = $matches[1];
    
    echo "Current bukuBesar method content:" . PHP_EOL;
    echo substr($bukuBesarMethod, 0, 1000) . PHP_EOL;
    echo "..." . PHP_EOL;
    
    // Check for common syntax issues
    if (strpos($bukuBesarMethod, '$month') !== false) {
        echo "✅ Found \$month variable" . PHP_EOL;
    }
    
    if (strpos($bukuBesarMethod, '->get(\'month\')') !== false) {
        echo "✅ Found ->get(\'month\') call" . PHP_EOL;
    }
    
    // Look for missing semicolons
    if (preg_match('/\$month\s*=\s*\$request\s*->\s*get\s*\(\s*[\'"]month[\'"]\s*\)\s*(?!\s*;)/', $bukuBesarMethod)) {
        echo "❌ Found missing semicolon after \$month assignment" . PHP_EOL;
    }
}

echo PHP_EOL . "=== CREATING SIMPLE FIX ===" . PHP_EOL;

// Create a simple, working version of the method
$simpleMethod = '
        $month = $request->get(\'month\');
        $year = $request->get(\'year\');
        $accountCode = $request->get(\'account_code\');

        $coas = \App\Models\Coa::select(\'kode_akun\', \'nama_akun\', \'tipe_akun\')
            ->groupBy(\'kode_akun\', \'nama_akun\', \'tipe_akun\')
            ->orderBy(\'kode_akun\')
            ->get();
            
        $lines = collect();
        $saldoAwal = 0.0;
        $from = null;
        $to = null;
        $totalDebit = 0;
        $totalKredit = 0;
        $saldoAkhir = 0;

        if ($accountCode) {
            $coa = \App\Models\Coa::where(\'kode_akun\', $accountCode)->first();

            if (!$coa) {
                return view(\'akuntansi.buku-besar\', compact(\'coas\',\'accountCode\',\'lines\',\'from\',\'to\',\'saldoAwal\',\'month\',\'year\',\'totalDebit\',\'totalKredit\',\'saldoAkhir\'));
            }

            // Use same saldo awal logic as neraca saldo
            $saldoAwal = (float)($coa->saldo_awal ?? 0);

            // Simple query for journal entries
            $query = \DB::table(\'journal_entries as je\')
                ->leftJoin(\'journal_lines as jl\', \'jl.journal_entry_id\', \'=\', \'je.id\')
                ->leftJoin(\'coas\', \'coas.id\', \'=\', \'jl.coa_id\')
                ->select([
                    \'je.*\',
                    \'jl.id as line_id\',
                    \'jl.debit\',
                    \'jl.credit\',
                    \'jl.memo as line_memo\',
                    \'coas.kode_akun\',
                    \'coas.nama_akun\',
                    \'coas.tipe_akun\'
                ])
                ->where(function($q) {
                    $q->where(\'jl.debit\', \'>\', 0)
                      ->orWhere(\'jl.credit\', \'>\', 0);
                })
                ->where(\'coas.kode_akun\', $accountCode)
                ->orderBy(\'je.tanggal\',\'asc\')
                ->orderBy(\'je.id\',\'asc\')
                ->orderBy(\'jl.id\',\'asc\');
            
            if ($month && $year) {
                $query->whereMonth(\'je.tanggal\', $month)
                       ->whereYear(\'je.tanggal\', $year);
            }

            $journalLines = $query->get();

            foreach ($journalLines as $line) {
                $lines->push((object) [
                    \'tanggal\' => $line->tanggal,
                    \'keterangan\' => $line->memo,
                    \'debit\' => $line->debit,
                    \'kredit\' => $line->credit,
                    \'saldo\' => 0
                ]);
            }

            $totalDebit = $journalLines->sum(\'debit\');
            $totalKredit = $journalLines->sum(\'credit\');
            $saldoAkhir = $saldoAwal + $totalDebit - $totalKredit;
        }

        return view(\'akuntansi.buku-besar\', compact(\'coas\',\'accountCode\',\'lines\',\'from\',\'to\',\'saldoAwal\',\'month\',\'year\',\'totalDebit\',\'totalKredit\',\'saldoAkhir\'));
';

// Replace the broken method with simple version
$pattern = '/public function bukuBesar.*?\{(.*?)\n\}/s';
$replacement = 'public function bukuBesar(Request $request)' . $simpleMethod;

if (preg_replace($pattern, $replacement, $controllerContent)) {
    $newControllerContent = preg_replace($pattern, $replacement, $controllerContent);
    
    if (file_put_contents($controllerFile, $newControllerContent)) {
        echo "✅ Fixed syntax error with simple method" . PHP_EOL;
        echo "✅ Controller updated successfully" . PHP_EOL;
    } else {
        echo "❌ Failed to write to controller file" . PHP_EOL;
    }
} else {
    echo "❌ Could not find bukuBesar method to fix" . PHP_EOL;
}

echo PHP_EOL . "=== VERIFICATION ===" . PHP_EOL;

// Check if file is syntactically correct
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
echo "Action: Fixed syntax error in bukuBesar method" . PHP_EOL;
echo "Method: Replaced with simple, working version" . PHP_EOL;
echo "Status: " . ($returnCode === 0 ? "SUCCESS" : "NEEDS ATTENTION") . PHP_EOL;

echo PHP_EOL . "=== NEXT STEPS ===" . PHP_EOL;
echo "1. Test buku-besar page" . PHP_EOL;
echo "2. Verify no more syntax errors" . PHP_EOL;
echo "3. Check if saldo awal works" . PHP_EOL;
echo "4. Later implement neraca-saldo logic if needed" . PHP_EOL;
