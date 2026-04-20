<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

// Get COA IDs
$coa_52 = DB::table('coas')->where('kode_akun', '52')->first();
$coa_54 = DB::table('coas')->where('kode_akun', '54')->first();
$coa_112 = DB::table('coas')->where('kode_akun', '112')->first();
$coa_513 = DB::table('coas')->where('kode_akun', '513')->first();
$coa_514 = DB::table('coas')->where('kode_akun', '514')->first();
$coa_515 = DB::table('coas')->where('kode_akun', '515')->first();

echo "COA IDs:\n";
echo "52 (BTKL): {$coa_52->id}\n";
echo "54 (BOP): {$coa_54->id}\n";
echo "112 (Kas): {$coa_112->id}\n";
echo "513 (Tunjangan): {$coa_513->id}\n";
echo "514 (Asuransi): {$coa_514->id}\n";
echo "515 (Bonus): {$coa_515->id}\n\n";

// Penggajian 1
echo "Creating Penggajian 1...\n";
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
echo "✓ Created entry {$entry1}\n\n";

// Penggajian 3
echo "Creating Penggajian 3...\n";
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
echo "✓ Created entry {$entry3}\n\n";

// Penggajian 4
echo "Creating Penggajian 4...\n";
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
echo "✓ Created entry {$entry4}\n\n";

// Penggajian 5
echo "Creating Penggajian 5...\n";
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
echo "✓ Created entry {$entry5}\n\n";

echo "All payroll entries created successfully!\n";
