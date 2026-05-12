<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixCoaNow extends Command
{
    protected $signature = 'fix:coa-now';
    protected $description = 'Fix COA data for expense payments immediately';

    public function handle()
    {
        $this->info('FIXING EXPENSE PAYMENTS COA DATA');
        $this->line('');

        // Step 1: Show current data
        $this->info('STEP 1: Current data in expense_payments');
        $this->line(str_repeat("=", 80));

        $current = DB::table('expense_payments')
            ->whereIn('id', [2, 3])
            ->select('id', 'tanggal', 'coa_beban_id', 'nominal_pembayaran')
            ->orderBy('id')
            ->get();

        foreach ($current as $row) {
            $this->line("ID {$row->id}: {$row->tanggal} | COA: {$row->coa_beban_id} | Amount: {$row->nominal_pembayaran}");
        }

        // Step 2: Update COA data
        $this->line('');
        $this->info('STEP 2: Updating COA data');
        $this->line(str_repeat("=", 80));

        DB::table('expense_payments')->where('id', 2)->update(['coa_beban_id' => '551']);
        $this->line('✓ ID 2: Updated to COA 551 (BOP Sewa Tempat)');

        DB::table('expense_payments')->where('id', 3)->update(['coa_beban_id' => '550']);
        $this->line('✓ ID 3: Updated to COA 550 (BOP Listrik)');

        // Step 3: Delete old journal entries
        $this->line('');
        $this->info('STEP 3: Deleting old journal entries');
        $this->line(str_repeat("=", 80));

        DB::table('journal_lines')
            ->whereIn('journal_entry_id', function($q) {
                $q->select('id')
                  ->from('journal_entries')
                  ->where('ref_type', 'expense_payment')
                  ->whereIn('ref_id', [2, 3]);
            })
            ->delete();

        DB::table('journal_entries')
            ->where('ref_type', 'expense_payment')
            ->whereIn('ref_id', [2, 3])
            ->delete();

        $this->line('✓ Deleted old journal entries');

        // Step 4: Create new journal entries
        $this->line('');
        $this->info('STEP 4: Creating new journal entries');
        $this->line(str_repeat("=", 80));

        // Get COA IDs
        $coa_551 = DB::table('coas')->where('kode_akun', '551')->value('id');
        $coa_550 = DB::table('coas')->where('kode_akun', '550')->value('id');
        $coa_111 = DB::table('coas')->where('kode_akun', '111')->value('id');

        // Create journal for ID 2 (Sewa - 551)
        $je2_id = DB::table('journal_entries')->insertGetId([
            'tanggal' => '2026-04-28',
            'ref_type' => 'expense_payment',
            'ref_id' => 2,
            'memo' => 'Pembayaran Beban: Pembayaran Beban Sewa',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('journal_lines')->insert([
            'journal_entry_id' => $je2_id,
            'coa_id' => $coa_551,
            'debit' => 1500000,
            'credit' => 0,
            'memo' => 'Pembayaran beban',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('journal_lines')->insert([
            'journal_entry_id' => $je2_id,
            'coa_id' => $coa_111,
            'debit' => 0,
            'credit' => 1500000,
            'memo' => 'Pembayaran beban operasional',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->line('✓ Created journal entry for ID 2 (Sewa - COA 551)');

        // Create journal for ID 3 (Listrik - 550)
        $je3_id = DB::table('journal_entries')->insertGetId([
            'tanggal' => '2026-04-29',
            'ref_type' => 'expense_payment',
            'ref_id' => 3,
            'memo' => 'Pembayaran Beban: Pembayaran Beban Listrik',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('journal_lines')->insert([
            'journal_entry_id' => $je3_id,
            'coa_id' => $coa_550,
            'debit' => 2030000,
            'credit' => 0,
            'memo' => 'Pembayaran beban',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('journal_lines')->insert([
            'journal_entry_id' => $je3_id,
            'coa_id' => $coa_111,
            'debit' => 0,
            'credit' => 2030000,
            'memo' => 'Pembayaran beban operasional',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->line('✓ Created journal entry for ID 3 (Listrik - COA 550)');

        // Step 5: Verify
        $this->line('');
        $this->info('STEP 5: Verification');
        $this->line(str_repeat("=", 80));

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
        $this->line(str_repeat("=", 80));
        $this->info('✅ SELESAI! Refresh halaman: http://127.0.0.1:8000/akuntansi/jurnal-umum');
        $this->line(str_repeat("=", 80));
    }
}
