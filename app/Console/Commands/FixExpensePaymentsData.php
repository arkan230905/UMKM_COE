<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixExpensePaymentsData extends Command
{
    protected $signature = 'fix:expense-payments-data';
    protected $description = 'Fix expense payments COA data';

    public function handle()
    {
        $this->info('FIX: Expense Payments Data');
        $this->line('');

        $this->info('STEP 1: Check current data in expense_payments');
        $current = DB::table('expense_payments')
            ->leftJoin('beban_operasional', 'beban_operasional.id', '=', 'expense_payments.beban_operasional_id')
            ->whereIn('expense_payments.id', [2, 3])
            ->select('expense_payments.id', 'expense_payments.tanggal', 'expense_payments.coa_beban_id', 'beban_operasional.nama_beban')
            ->orderBy('expense_payments.id')
            ->get();

        $this->table(['ID', 'Tanggal', 'Beban', 'COA'], $current->map(fn($r) => [
            $r->id,
            $r->tanggal,
            substr($r->nama_beban, 0, 40),
            $r->coa_beban_id
        ])->toArray());

        $this->line('');
        $this->info('STEP 2: Fix the data');

        // ID 2: Pembayaran Beban Sewa - should be 551
        $this->line('Updating ID 2 (Sewa) to COA 551...');
        DB::table('expense_payments')->where('id', 2)->update(['coa_beban_id' => '551']);
        $this->line('✓ Updated');

        // ID 3: Pembayaran Beban Listrik - should be 550
        $this->line('Updating ID 3 (Listrik) to COA 550...');
        DB::table('expense_payments')->where('id', 3)->update(['coa_beban_id' => '550']);
        $this->line('✓ Updated');

        $this->line('');
        $this->info('STEP 3: Delete old journal_entries');
        $deleted = DB::table('journal_entries')
            ->where('ref_type', 'expense_payment')
            ->whereIn('ref_id', [2, 3])
            ->delete();
        $this->line("✓ Deleted: $deleted entries");

        $this->line('');
        $this->info('STEP 4: Create new journal_entries from updated expense_payments');

        $payments = DB::table('expense_payments')
            ->leftJoin('beban_operasional', 'beban_operasional.id', '=', 'expense_payments.beban_operasional_id')
            ->whereIn('expense_payments.id', [2, 3])
            ->select('expense_payments.*', 'beban_operasional.nama_beban')
            ->orderBy('expense_payments.id')
            ->get();

        foreach ($payments as $p) {
            $this->line("Processing ID {$p->id} ({$p->nama_beban}): COA {$p->coa_beban_id}");

            $coa_beban = DB::table('coas')->where('kode_akun', $p->coa_beban_id)->first();
            $coa_kas = DB::table('coas')->where('kode_akun', $p->coa_kasbank)->first();

            if (!$coa_beban) {
                $this->error("  ✗ COA {$p->coa_beban_id} not found");
                continue;
            }

            if (!$coa_kas) {
                $this->error("  ✗ COA {$p->coa_kasbank} not found");
                continue;
            }

            $je_id = DB::table('journal_entries')->insertGetId([
                'tanggal' => $p->tanggal,
                'ref_type' => 'expense_payment',
                'ref_id' => $p->id,
                'memo' => "Pembayaran Beban: {$p->nama_beban}",
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('journal_lines')->insert([
                'journal_entry_id' => $je_id,
                'coa_id' => $coa_beban->id,
                'debit' => $p->nominal_pembayaran,
                'credit' => 0,
                'memo' => 'Pembayaran beban',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('journal_lines')->insert([
                'journal_entry_id' => $je_id,
                'coa_id' => $coa_kas->id,
                'debit' => 0,
                'credit' => $p->nominal_pembayaran,
                'memo' => 'Pembayaran beban operasional',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->line('  ✓ Created journal_entry');
        }

        $this->line('');
        $this->info(str_repeat('=', 100));
        $this->info('VERIFIKASI');
        $this->info(str_repeat('=', 100));
        $this->line('');

        $verify = DB::table('journal_entries as je')
            ->leftJoin('journal_lines as jl', 'jl.journal_entry_id', '=', 'je.id')
            ->leftJoin('coas as c', 'c.id', '=', 'jl.coa_id')
            ->where('je.ref_type', 'expense_payment')
            ->whereIn('je.ref_id', [2, 3])
            ->select('je.id', 'je.tanggal', 'je.memo', 'jl.debit', 'jl.credit', 'c.kode_akun', 'c.nama_akun')
            ->orderBy('je.id')
            ->orderBy('jl.id')
            ->get();

        $this->table(['JE ID', 'Tanggal', 'Memo', 'COA', 'Nama Akun', 'Debit', 'Kredit'], $verify->map(fn($r) => [
            $r->id,
            $r->tanggal,
            substr($r->memo, 0, 40),
            $r->kode_akun,
            substr($r->nama_akun, 0, 40),
            $r->debit,
            $r->kredit
        ])->toArray());

        $this->line('');
        $this->info(str_repeat('=', 100));
        $this->info('✅ SELESAI! Refresh: http://127.0.0.1:8000/akuntansi/jurnal-umum');
        $this->info(str_repeat('=', 100));
    }
}
