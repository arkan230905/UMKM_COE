<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class InsertMissingJournals extends Command
{
    protected $signature = 'journal:insert-missing';
    protected $description = 'Insert missing payroll and expense payment journal entries';

    public function handle()
    {
        $this->info('Inserting missing journal entries...');

        try {
            // Get COA IDs
            $coas = DB::table('coas')
                ->whereIn('kode_akun', ['52', '54', '112', '513', '514', '515', '550', '551'])
                ->pluck('id', 'kode_akun')
                ->toArray();

            $this->info('COA IDs found: ' . json_encode($coas));

            // Check existing entries
            $existing_payroll = DB::table('journal_entries')->where('ref_type', 'payroll')->count();
            $existing_expense = DB::table('journal_entries')->where('ref_type', 'expense_payment')->count();

            $this->info("Existing entries: Payroll = $existing_payroll, Expense Payment = $existing_expense");

            if ($existing_payroll == 0) {
                $this->info('Creating payroll entries...');

                // Penggajian 1: Budi Susanto
                $entry_id = DB::table('journal_entries')->insertGetId([
                    'ref_type' => 'payroll',
                    'ref_id' => 1,
                    'tanggal' => '2026-04-24',
                    'memo' => 'Penggajian',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                DB::table('journal_lines')->insert([
                    ['journal_entry_id' => $entry_id, 'coa_id' => $coas['52'], 'debit' => 140000, 'credit' => 0, 'memo' => 'Gaji Pokok', 'created_at' => now(), 'updated_at' => now()],
                    ['journal_entry_id' => $entry_id, 'coa_id' => $coas['513'], 'debit' => 525000, 'credit' => 0, 'memo' => 'Beban Tunjangan', 'created_at' => now(), 'updated_at' => now()],
                    ['journal_entry_id' => $entry_id, 'coa_id' => $coas['514'], 'debit' => 100000, 'credit' => 0, 'memo' => 'Beban Asuransi', 'created_at' => now(), 'updated_at' => now()],
                    ['journal_entry_id' => $entry_id, 'coa_id' => $coas['112'], 'debit' => 0, 'credit' => 765000, 'memo' => 'Pembayaran Gaji', 'created_at' => now(), 'updated_at' => now()]
                ]);
                $this->line("✓ Created Penggajian 1 (Entry ID: $entry_id)");

                // Penggajian 3: Ahmad Suryanto
                $entry_id = DB::table('journal_entries')->insertGetId([
                    'ref_type' => 'payroll',
                    'ref_id' => 3,
                    'tanggal' => '2026-04-24',
                    'memo' => 'Penggajian',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                DB::table('journal_lines')->insert([
                    ['journal_entry_id' => $entry_id, 'coa_id' => $coas['52'], 'debit' => 72000, 'credit' => 0, 'memo' => 'Gaji Pokok', 'created_at' => now(), 'updated_at' => now()],
                    ['journal_entry_id' => $entry_id, 'coa_id' => $coas['513'], 'debit' => 495000, 'credit' => 0, 'memo' => 'Beban Tunjangan', 'created_at' => now(), 'updated_at' => now()],
                    ['journal_entry_id' => $entry_id, 'coa_id' => $coas['514'], 'debit' => 80000, 'credit' => 0, 'memo' => 'Beban Asuransi', 'created_at' => now(), 'updated_at' => now()],
                    ['journal_entry_id' => $entry_id, 'coa_id' => $coas['515'], 'debit' => 200000, 'credit' => 0, 'memo' => 'Beban Bonus', 'created_at' => now(), 'updated_at' => now()],
                    ['journal_entry_id' => $entry_id, 'coa_id' => $coas['112'], 'debit' => 0, 'credit' => 847000, 'memo' => 'Pembayaran Gaji', 'created_at' => now(), 'updated_at' => now()]
                ]);
                $this->line("✓ Created Penggajian 3 (Entry ID: $entry_id)");

                // Penggajian 4: Rina Wijaya
                $entry_id = DB::table('journal_entries')->insertGetId([
                    'ref_type' => 'payroll',
                    'ref_id' => 4,
                    'tanggal' => '2026-04-25',
                    'memo' => 'Penggajian',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                DB::table('journal_lines')->insert([
                    ['journal_entry_id' => $entry_id, 'coa_id' => $coas['52'], 'debit' => 51000, 'credit' => 0, 'memo' => 'Gaji Pokok', 'created_at' => now(), 'updated_at' => now()],
                    ['journal_entry_id' => $entry_id, 'coa_id' => $coas['513'], 'debit' => 475000, 'credit' => 0, 'memo' => 'Beban Tunjangan', 'created_at' => now(), 'updated_at' => now()],
                    ['journal_entry_id' => $entry_id, 'coa_id' => $coas['112'], 'debit' => 0, 'credit' => 526000, 'memo' => 'Pembayaran Gaji', 'created_at' => now(), 'updated_at' => now()]
                ]);
                $this->line("✓ Created Penggajian 4 (Entry ID: $entry_id)");

                // Penggajian 5: Dedi Gunawan
                $entry_id = DB::table('journal_entries')->insertGetId([
                    'ref_type' => 'payroll',
                    'ref_id' => 5,
                    'tanggal' => '2026-04-26',
                    'memo' => 'Penggajian',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                DB::table('journal_lines')->insert([
                    ['journal_entry_id' => $entry_id, 'coa_id' => $coas['54'], 'debit' => 2500000, 'credit' => 0, 'memo' => 'BOP Tenaga Kerja Tidak Langsung', 'created_at' => now(), 'updated_at' => now()],
                    ['journal_entry_id' => $entry_id, 'coa_id' => $coas['513'], 'debit' => 600000, 'credit' => 0, 'memo' => 'Beban Tunjangan', 'created_at' => now(), 'updated_at' => now()],
                    ['journal_entry_id' => $entry_id, 'coa_id' => $coas['514'], 'debit' => 150000, 'credit' => 0, 'memo' => 'Beban Asuransi', 'created_at' => now(), 'updated_at' => now()],
                    ['journal_entry_id' => $entry_id, 'coa_id' => $coas['112'], 'debit' => 0, 'credit' => 3250000, 'memo' => 'Pembayaran Gaji', 'created_at' => now(), 'updated_at' => now()]
                ]);
                $this->line("✓ Created Penggajian 5 (Entry ID: $entry_id)");
            } else {
                $this->info('Payroll entries already exist');
            }

            if ($existing_expense == 0) {
                $this->info('Creating expense payment entries...');

                // Pembayaran Beban 1: Sewa
                $entry_id = DB::table('journal_entries')->insertGetId([
                    'ref_type' => 'expense_payment',
                    'ref_id' => 1,
                    'tanggal' => '2026-04-24',
                    'memo' => 'Pembayaran Beban',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                DB::table('journal_lines')->insert([
                    ['journal_entry_id' => $entry_id, 'coa_id' => $coas['551'], 'debit' => 1500000, 'credit' => 0, 'memo' => 'BOP Sewa Tempat', 'created_at' => now(), 'updated_at' => now()],
                    ['journal_entry_id' => $entry_id, 'coa_id' => $coas['112'], 'debit' => 0, 'credit' => 1500000, 'memo' => 'Pembayaran via Kas', 'created_at' => now(), 'updated_at' => now()]
                ]);
                $this->line("✓ Created Pembayaran Beban 1 (Entry ID: $entry_id)");

                // Pembayaran Beban 2: Listrik
                $entry_id = DB::table('journal_entries')->insertGetId([
                    'ref_type' => 'expense_payment',
                    'ref_id' => 2,
                    'tanggal' => '2026-04-29',
                    'memo' => 'Pembayaran Beban',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                DB::table('journal_lines')->insert([
                    ['journal_entry_id' => $entry_id, 'coa_id' => $coas['550'], 'debit' => 2030000, 'credit' => 0, 'memo' => 'BOP Listrik', 'created_at' => now(), 'updated_at' => now()],
                    ['journal_entry_id' => $entry_id, 'coa_id' => $coas['112'], 'debit' => 0, 'credit' => 2030000, 'memo' => 'Pembayaran via Kas', 'created_at' => now(), 'updated_at' => now()]
                ]);
                $this->line("✓ Created Pembayaran Beban 2 (Entry ID: $entry_id)");
            } else {
                $this->info('Expense payment entries already exist');
            }

            // Verify totals
            $totals = DB::table('journal_entries')
                ->leftJoin('journal_lines', 'journal_lines.journal_entry_id', '=', 'journal_entries.id')
                ->selectRaw('SUM(journal_lines.debit) as total_debit, SUM(journal_lines.credit) as total_credit')
                ->first();

            $this->info("Total Debit: Rp " . number_format($totals->total_debit));
            $this->info("Total Credit: Rp " . number_format($totals->total_credit));

            if ($totals->total_debit == $totals->total_credit) {
                $this->info('✓ Journal is balanced!');
            } else {
                $this->error('✗ Journal is not balanced!');
            }

            $this->info('All missing journal entries have been created successfully!');

        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}