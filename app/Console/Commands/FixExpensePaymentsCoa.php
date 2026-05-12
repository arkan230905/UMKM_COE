<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\ExpensePayment;

class FixExpensePaymentsCoa extends Command
{
    protected $signature = 'fix:expense-payments-coa';
    protected $description = 'Fix COA codes in expense_payments table';

    public function handle()
    {
        $this->info('Fixing expense_payments COA data...');
        $this->line('');

        // Step 1: Show current data
        $this->info('CURRENT DATA:');
        $current = DB::table('expense_payments')
            ->whereIn('id', [2, 3])
            ->select('id', 'tanggal', 'coa_beban_id', 'nominal_pembayaran')
            ->get();

        foreach ($current as $row) {
            $this->line("ID {$row->id}: COA {$row->coa_beban_id}, Amount: {$row->nominal_pembayaran}");
        }

        $this->line('');
        $this->info('FIXING DATA:');

        // Step 2: Fix the COA codes
        DB::table('expense_payments')->where('id', 2)->update(['coa_beban_id' => '551']);
        $this->line('✓ ID 2: Updated to COA 551 (BOP Sewa Tempat)');

        DB::table('expense_payments')->where('id', 3)->update(['coa_beban_id' => '550']);
        $this->line('✓ ID 3: Updated to COA 550 (BOP Listrik)');

        $this->line('');
        $this->info('RECREATING JOURNAL ENTRIES:');

        // Step 3: Recreate journal entries
        $payments = ExpensePayment::whereIn('id', [2, 3])->get();

        foreach ($payments as $payment) {
            $this->line("Processing ID {$payment->id}...");
            
            // Trigger the boot method to recreate journal entries
            $payment->touch(); // This will trigger the updated event
            
            $this->line("✓ Journal entries recreated");
        }

        $this->line('');
        $this->info('VERIFICATION:');

        $verify = DB::table('journal_entries as je')
            ->leftJoin('journal_lines as jl', 'jl.journal_entry_id', '=', 'je.id')
            ->leftJoin('coas as c', 'c.id', '=', 'jl.coa_id')
            ->where('je.ref_type', 'expense_payment')
            ->whereIn('je.ref_id', [2, 3])
            ->select('je.tanggal', 'je.memo', 'c.kode_akun', 'c.nama_akun', 'jl.debit', 'jl.credit')
            ->orderBy('je.id')
            ->orderBy('jl.id')
            ->get();

        foreach ($verify as $row) {
            $this->line("{$row->tanggal} | {$row->kode_akun} {$row->nama_akun} | D: {$row->debit} K: {$row->credit}");
        }

        $this->line('');
        $this->info('✅ Done! Refresh the page to see changes.');
    }
}
