<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ExpensePayment;
use App\Models\JournalEntry;
use App\Models\Coa;
use App\Services\JournalService;

class FixMissingExpenseJournalSeeder extends Seeder
{
    public function run()
    {
        $journalService = app(JournalService::class);
        
        echo "=== FIX MISSING EXPENSE PAYMENT JOURNALS ===\n\n";
        
        // Ambil semua expense payment
        $expenses = ExpensePayment::all();
        
        $fixed = 0;
        $skipped = 0;
        $errors = 0;
        
        foreach ($expenses as $expense) {
            // Cek apakah sudah ada jurnal
            $existingJournal = JournalEntry::where('ref_type', 'expense_payment')
                ->where('ref_id', $expense->id)
                ->first();
            
            if ($existingJournal) {
                echo "  ⏭️  Expense Payment #{$expense->id} - Jurnal sudah ada (ID: {$existingJournal->id})\n";
                $skipped++;
                continue;
            }
            
            // Ambil COA beban
            $coa = Coa::find($expense->coa_beban_id);
            if (!$coa) {
                echo "  ❌ Expense Payment #{$expense->id} - COA Beban tidak ditemukan\n";
                $errors++;
                continue;
            }
            
            // Buat jurnal
            try {
                $journalService->post(
                    $expense->tanggal,
                    'expense_payment',
                    (int)$expense->id,
                    'Pembayaran Beban - ' . $coa->nama_akun,
                    [
                        ['code' => $coa->kode_akun, 'debit' => (float)$expense->nominal, 'credit' => 0],
                        ['code' => $expense->coa_kasbank, 'debit' => 0, 'credit' => (float)$expense->nominal],
                    ]
                );
                
                echo "  ✅ Expense Payment #{$expense->id} - Jurnal berhasil dibuat\n";
                echo "     Tanggal: {$expense->tanggal}\n";
                echo "     Beban: {$coa->kode_akun} - {$coa->nama_akun}\n";
                echo "     Kas/Bank: {$expense->coa_kasbank}\n";
                echo "     Nominal: Rp " . number_format($expense->nominal, 0, ',', '.') . "\n\n";
                
                $fixed++;
            } catch (\Exception $e) {
                echo "  ❌ Expense Payment #{$expense->id} - ERROR: {$e->getMessage()}\n\n";
                $errors++;
            }
        }
        
        echo "\n=== SUMMARY ===\n";
        echo "Total Expense Payments: " . $expenses->count() . "\n";
        echo "✅ Fixed: {$fixed}\n";
        echo "⏭️  Skipped (sudah ada jurnal): {$skipped}\n";
        echo "❌ Errors: {$errors}\n";
        echo "\n=== SELESAI ===\n";
    }
}
