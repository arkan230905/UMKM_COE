<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CreatePayrollJournals extends Command
{
    protected $signature = 'journal:create-payroll';
    protected $description = 'Create journal entries for payroll records';

    public function handle()
    {
        // Get COA IDs
        $coa_52 = DB::table('coas')->where('kode_akun', '52')->first();
        $coa_54 = DB::table('coas')->where('kode_akun', '54')->first();
        $coa_112 = DB::table('coas')->where('kode_akun', '112')->first();
        $coa_513 = DB::table('coas')->where('kode_akun', '513')->first();
        $coa_514 = DB::table('coas')->where('kode_akun', '514')->first();
        $coa_515 = DB::table('coas')->where('kode_akun', '515')->first();

        $this->info('Creating payroll journal entries...');

        // Penggajian 1
        $entry1 = DB::table('journal_entries')->insertGetId([
            'ref_type' => 'payroll',
            'ref_id' => 1,
            'tanggal' => '2026-04-24',
            'memo' => 'Penggajian',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('journal_lines')->insert([
            ['journal_entry_id' => $entry1, 'coa_id' => $coa_52->id, 'debit' => 140000, 'credit' => 0, 'memo' => 'Gaji Pokok', 'created_at' => now(), 'updated_at' => now()],
            ['journal_entry_id' => $entry1, 'coa_id' => $coa_513->id, 'debit' => 525000, 'credit' => 0, 'memo' => 'Beban Tunjangan', 'created_at' => now(), 'updated_at' => now()],
            ['journal_entry_id' => $entry1, 'coa_id' => $coa_514->id, 'debit' => 100000, 'credit' => 0, 'memo' => 'Beban Asuransi', 'created_at' => now(), 'updated_at' => now()],
            ['journal_entry_id' => $entry1, 'coa_id' => $coa_112->id, 'debit' => 0, 'credit' => 765000, 'memo' => 'Pembayaran Gaji', 'created_at' => now(), 'updated_at' => now()]
        ]);
        $this->line("✓ Created entry {$entry1}");

        // Penggajian 3
        $entry3 = DB::table('journal_entries')->insertGetId([
            'ref_type' => 'payroll',
            'ref_id' => 3,
            'tanggal' => '2026-04-24',
            'memo' => 'Penggajian',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('journal_lines')->insert([
            ['journal_entry_id' => $entry3, 'coa_id' => $coa_52->id, 'debit' => 72000, 'credit' => 0, 'memo' => 'Gaji Pokok', 'created_at' => now(), 'updated_at' => now()],
            ['journal_entry_id' => $entry3, 'coa_id' => $coa_513->id, 'debit' => 495000, 'credit' => 0, 'memo' => 'Beban Tunjangan', 'created_at' => now(), 'updated_at' => now()],
            ['journal_entry_id' => $entry3, 'coa_id' => $coa_514->id, 'debit' => 80000, 'credit' => 0, 'memo' => 'Beban Asuransi', 'created_at' => now(), 'updated_at' => now()],
            ['journal_entry_id' => $entry3, 'coa_id' => $coa_515->id, 'debit' => 200000, 'credit' => 0, 'memo' => 'Beban Bonus', 'created_at' => now(), 'updated_at' => now()],
            ['journal_entry_id' => $entry3, 'coa_id' => $coa_112->id, 'debit' => 0, 'credit' => 847000, 'memo' => 'Pembayaran Gaji', 'created_at' => now(), 'updated_at' => now()]
        ]);
        $this->line("✓ Created entry {$entry3}");

        // Penggajian 4
        $entry4 = DB::table('journal_entries')->insertGetId([
            'ref_type' => 'payroll',
            'ref_id' => 4,
            'tanggal' => '2026-04-25',
            'memo' => 'Penggajian',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('journal_lines')->insert([
            ['journal_entry_id' => $entry4, 'coa_id' => $coa_52->id, 'debit' => 51000, 'credit' => 0, 'memo' => 'Gaji Pokok', 'created_at' => now(), 'updated_at' => now()],
            ['journal_entry_id' => $entry4, 'coa_id' => $coa_513->id, 'debit' => 475000, 'credit' => 0, 'memo' => 'Beban Tunjangan', 'created_at' => now(), 'updated_at' => now()],
            ['journal_entry_id' => $entry4, 'coa_id' => $coa_112->id, 'debit' => 0, 'credit' => 526000, 'memo' => 'Pembayaran Gaji', 'created_at' => now(), 'updated_at' => now()]
        ]);
        $this->line("✓ Created entry {$entry4}");

        // Penggajian 5
        $entry5 = DB::table('journal_entries')->insertGetId([
            'ref_type' => 'payroll',
            'ref_id' => 5,
            'tanggal' => '2026-04-26',
            'memo' => 'Penggajian',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('journal_lines')->insert([
            ['journal_entry_id' => $entry5, 'coa_id' => $coa_54->id, 'debit' => 2500000, 'credit' => 0, 'memo' => 'BOP Tenaga Kerja Tidak Langsung', 'created_at' => now(), 'updated_at' => now()],
            ['journal_entry_id' => $entry5, 'coa_id' => $coa_513->id, 'debit' => 600000, 'credit' => 0, 'memo' => 'Beban Tunjangan', 'created_at' => now(), 'updated_at' => now()],
            ['journal_entry_id' => $entry5, 'coa_id' => $coa_514->id, 'debit' => 150000, 'credit' => 0, 'memo' => 'Beban Asuransi', 'created_at' => now(), 'updated_at' => now()],
            ['journal_entry_id' => $entry5, 'coa_id' => $coa_112->id, 'debit' => 0, 'credit' => 3250000, 'memo' => 'Pembayaran Gaji', 'created_at' => now(), 'updated_at' => now()]
        ]);
        $this->line("✓ Created entry {$entry5}");

        $this->info('All payroll entries created successfully!');
    }
}
