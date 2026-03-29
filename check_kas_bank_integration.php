<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Checking Kas Bank Integration ===\n";

try {
    // Check latest pembelian
    $latestPembelian = DB::table('pembelians')
        ->orderBy('created_at', 'desc')
        ->first();
    
    if (!$latestPembelian) {
        echo "❌ No pembelian found\n";
        exit(1);
    }
    
    echo "LATEST PEMBELIAN:\n";
    echo "ID: {$latestPembelian->id}\n";
    echo "Nomor: {$latestPembelian->nomor_pembelian}\n";
    echo "Tanggal: {$latestPembelian->tanggal}\n";
    echo "Total: Rp " . number_format($latestPembelian->total_harga, 0, ',', '.') . "\n";
    echo "Payment Method: {$latestPembelian->payment_method}\n";
    echo "Bank ID: " . ($latestPembelian->bank_id ?? 'NULL') . "\n";
    echo "Status: {$latestPembelian->status}\n\n";
    
    // Check if there are journal entries for this pembelian
    echo "JOURNAL ENTRIES for this pembelian:\n";
    $journalEntries = DB::table('journal_entries')
        ->where('ref_type', 'pembelian')
        ->where('ref_id', $latestPembelian->id)
        ->get();
    
    if ($journalEntries->count() > 0) {
        foreach ($journalEntries as $entry) {
            echo "- Entry ID {$entry->id}: {$entry->tanggal} - {$entry->memo}\n";
            
            // Get journal lines
            $lines = DB::table('journal_lines')
                ->join('coas', 'journal_lines.coa_id', '=', 'coas.kode_akun')
                ->where('journal_entry_id', $entry->id)
                ->select('journal_lines.*', 'coas.nama_akun')
                ->get();
            
            foreach ($lines as $line) {
                $debit = $line->debit > 0 ? 'Rp ' . number_format($line->debit, 0, ',', '.') : '';
                $credit = $line->credit > 0 ? 'Rp ' . number_format($line->credit, 0, ',', '.') : '';
                echo "  - {$line->coa_id} ({$line->nama_akun}): Debit {$debit} | Credit {$credit}\n";
            }
        }
    } else {
        echo "❌ NO JOURNAL ENTRIES FOUND!\n";
        echo "❌ This is why kas/bank report doesn't show the transaction\n";
    }
    
    // Check kas/bank accounts
    echo "\nKAS/BANK ACCOUNTS:\n";
    $kasAccounts = DB::table('coas')
        ->where('nama_akun', 'like', '%kas%')
        ->orWhere('nama_akun', 'like', '%bank%')
        ->orWhere('kode_akun', 'like', '1-1%') // Usually cash/bank accounts start with 1-1
        ->get();
    
    foreach ($kasAccounts as $account) {
        echo "- {$account->kode_akun}: {$account->nama_akun}\n";
    }
    
    // Check if pembelian controller creates journal entries
    echo "\nCHECKING PEMBELIAN CONTROLLER:\n";
    $controllerFile = 'app/Http/Controllers/PembelianController.php';
    if (file_exists($controllerFile)) {
        $content = file_get_contents($controllerFile);
        
        if (strpos($content, 'JournalEntry') !== false) {
            echo "✅ Controller mentions JournalEntry\n";
        } else {
            echo "❌ Controller does NOT create journal entries\n";
        }
        
        if (strpos($content, 'journal_entries') !== false) {
            echo "✅ Controller works with journal_entries table\n";
        } else {
            echo "❌ Controller does NOT work with journal_entries table\n";
        }
    }
    
    echo "\nPROBLEM ANALYSIS:\n";
    if ($journalEntries->count() == 0) {
        echo "❌ MAIN ISSUE: Pembelian does not create journal entries\n";
        echo "❌ Without journal entries, kas/bank report has no data to show\n";
        echo "❌ Need to add journal entry creation in pembelian store method\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=== Check completed ===\n";