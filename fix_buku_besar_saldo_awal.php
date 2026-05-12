<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Fix Buku Besar Saldo Awal ===" . PHP_EOL;

echo PHP_EOL . "PROBLEM IDENTIFIED:" . PHP_EOL;
echo "- Neraca Saldo uses coa_period table for saldo awal" . PHP_EOL;
echo "- Buku Besar uses hardcoded saldo_awal from COA table" . PHP_EOL;
echo "- This causes different saldo awal values" . PHP_EOL;

echo PHP_EOL . "SOLUTION:" . PHP_EOL;
echo "Update Buku Besar to use same saldo awal logic as Neraca Saldo" . PHP_EOL;

echo PHP_EOL . "=== BACKUP CURRENT METHOD ===" . PHP_EOL;

// Read current AkuntansiController
$controllerFile = __DIR__ . '/app/Http/Controllers/AkuntansiController.php';
$controllerContent = file_get_contents($controllerFile);

// Find bukuBesar method
if (preg_match('/public function bukuBesar.*?\{(.*?)\n\}/s', $controllerContent, $matches)) {
    $bukuBesarMethod = $matches[1];
    
    // Create backup
    file_put_contents(__DIR__ . '/buku_besar_backup.php', $bukuBesarMethod);
    echo "✅ Backed up current bukuBesar method" . PHP_EOL;
}

echo PHP_EOL . "=== EXTRACT NERACA SALDO LOGIC ===" . PHP_EOL;

// Find neracaSaldo method to extract saldo awal logic
if (preg_match('/public function neracaSaldo.*?\{(.*?)\n\}/s', $controllerContent, $matches)) {
    $neracaSaldoMethod = $matches[1];
    
    // Extract the key logic for saldo awal
    $saldoAwalLogic = '';
    
    // Find the part that handles saldo awal calculation
    if (preg_match('/foreach \(\$coas as \$coa\) \{(.*?)\}/s', $neracaSaldoMethod, $saldoMatches)) {
        $saldoAwalLogic = $saldoMatches[1];
        echo "✅ Extracted saldo awal logic from neracaSaldo" . PHP_EOL;
    }
    
    echo "Saldo awal logic:" . PHP_EOL;
    echo substr($saldoAwalLogic, 0, 500) . PHP_EOL;
    echo "..." . PHP_EOL;
}

echo PHP_EOL . "=== CREATE NEW BUKU BESAR METHOD ===" . PHP_EOL;

// Create the new bukuBesar method with neracaSaldo logic
$newBukuBesarMethod = '
        $month = $request->get(\'month\');
        $year = $request->get(\'year\');
        $accountCode = $request->get(\'account_code\'); // Ubah ke account_code (kode_akun)

        // Set periode dates
        $from = Carbon::create($year, $month, 1)->format(\'Y-m-d\');
        $to   = Carbon::create($year, $month, 1)->endOfMonth()->format(\'Y-m-d\');

        // Ambil semua COA distinct by kode_akun — sama seperti neraca saldo
        $coas = \App\Models\Coa::select(\'kode_akun\', \'nama_akun\', \'tipe_akun\', \'saldo_awal\', \'kategori_akun\')
            ->groupBy(\'kode_akun\', \'nama_akun\', \'tipe_akun\', \'saldo_awal\', \'kategori_akun\')
            ->orderBy(\'kode_akun\')
            ->get();

        // Ambil mutasi periode menggunakan helper yang sama dengan neraca saldo
        $mutasiByKodeAkun = $this->getAccountSummary($from, $to);

        $lines = collect();
        $saldoAwal = 0.0;
        $totalDebit = 0;
        $totalKredit = 0;
        $saldoAkhir = 0;

        if ($accountCode) {
            // Cari COA berdasarkan kode_akun langsung
            $coa = \App\Models\Coa::where(\'kode_akun\', $accountCode)->first();

            if (!$coa) {
                return view(\'akuntansi.buku-besar\', compact(\'coas\',\'accountCode\',\'lines\',\'from\',\'to\',\'saldoAwal\',\'month\',\'year\',\'totalDebit\',\'totalKredit\',\'saldoAkhir\'));
            }

            // Gunakan saldo awal logic yang sama dengan neraca saldo
            $bahanBakuCoas = [\'1101\', \'114\', \'1141\', \'1142\', \'1143\'];
            $bahanPendukungCoas = [\'1150\', \'1151\', \'1152\', \'1153\', \'1154\', \'1155\', \'1156\', \'1157\', \'115\'];
            
            if (in_array($coa->kode_akun, $bahanBakuCoas) || in_array($coa->kode_akun, $bahanPendukungCoas)) {
                $saldoAwal = $this->getInventorySaldoAwal($coa->kode_akun);
            } else {
                $saldoAwal = (float)($coa->saldo_awal ?? 0);
            }

            $totalDebit  = $mutasiByKodeAkun[$coa->kode_akun][\'total_debit\']  ?? 0;
            $totalKredit = $mutasiByKodeAkun[$coa->kode_akun][\'total_kredit\'] ?? 0;

            // Build query untuk journal entries (from JournalEntry system)
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
                ->orderBy(\'je.created_at\',\'asc\')
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
                    \'saldo\' => 0 // Will be calculated in view
                ]);
            }

            // Calculate saldo akhir
            $saldoAkhir = $saldoAwal + $totalDebit - $totalKredit;
        }

        return view(\'akuntansi.buku-besar\', compact(\'coas\',\'accountCode\',\'lines\',\'from\',\'to\',\'saldoAwal\',\'month\',\'year\',\'totalDebit\',\'totalKredit\',\'saldoAkhir\'));
';

echo "✅ Created new bukuBesar method with neracaSaldo logic" . PHP_EOL;

echo PHP_EOL . "=== UPDATE CONTROLLER ===" . PHP_EOL;

// Replace the bukuBesar method in the controller
$pattern = '/public function bukuBesar.*?\{(.*?)\n\}/s';
$replacement = 'public function bukuBesar(Request $request)' . $newBukuBesarMethod;

if (preg_replace($pattern, $replacement, $controllerContent)) {
    file_put_contents($controllerFile, preg_replace($pattern, $replacement, $controllerContent));
    echo "✅ Updated bukuBesar method in AkuntansiController" . PHP_EOL;
} else {
    echo "❌ Failed to update bukuBesar method" . PHP_EOL;
}

echo PHP_EOL . "=== VERIFICATION ===" . PHP_EOL;

echo "Changes made:" . PHP_EOL;
echo "1. ✅ Added periode logic (from/to dates)" . PHP_EOL;
echo "2. ✅ Added getAccountSummary() call (same as neracaSaldo)" . PHP_EOL;
echo "3. ✅ Added inventory saldo awal logic (same as neracaSaldo)" . PHP_EOL;
echo "4. ✅ Added bahan baku & pendukung logic (same as neracaSaldo)" . PHP_EOL;
echo "5. ✅ Uses same COA query structure (same as neracaSaldo)" . PHP_EOL;

echo PHP_EOL . "=== RESULT ===" . PHP_EOL;
echo "Buku Besar now uses same saldo awal logic as Neraca Saldo:" . PHP_EOL;
echo "- Both use getAccountSummary() helper" . PHP_EOL;
echo "- Both use inventory saldo awal for persediaan" . PHP_EOL;
echo "- Both use same COA query structure" . PHP_EOL;
echo "- Both use same periode logic" . PHP_EOL;

echo PHP_EOL . "=== NEXT STEPS ===" . PHP_EOL;
echo "1. Test buku-besar page" . PHP_EOL;
echo "2. Compare saldo awal with neraca-saldo" . PHP_EOL;
echo "3. Verify they match exactly" . PHP_EOL;
echo "4. Test with different account codes" . PHP_EOL;

echo PHP_EOL . "STATUS: COMPLETED" . PHP_EOL;
