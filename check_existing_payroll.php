<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Penggajian;
use App\Models\Pegawai;

echo "=== CHECK EXISTING PAYROLL ===\n\n";

// Find Budi Susanto
$pegawai = Pegawai::where('nama', 'like', '%Budi Susanto%')->first();

if (!$pegawai) {
    echo "Employee not found!\n";
    exit;
}

echo "Checking existing payroll records for: {$pegawai->nama} (ID: {$pegawai->id})\n\n";

// Find existing payroll records
$payrollRecords = Penggajian::where('pegawai_id', $pegawai->id)
    ->orderBy('tanggal_penggajian', 'desc')
    ->get();

echo "Found {$payrollRecords->count()} payroll records:\n\n";

foreach ($payrollRecords as $record) {
    echo "ID: {$record->id}\n";
    echo "Tanggal: {$record->tanggal_penggajian}\n";
    echo "Gaji Pokok: " . ($record->gaji_pokok ?? 'NULL') . "\n";
    echo "Tarif per Jam: " . ($record->tarif_per_jam ?? 'NULL') . "\n";
    echo "Total Jam Kerja: " . ($record->total_jam_kerja ?? 'NULL') . "\n";
    echo "Tunjangan: " . ($record->tunjangan ?? 'NULL') . "\n";
    echo "Asuransi: " . ($record->asuransi ?? 'NULL') . "\n";
    echo "Bonus: " . ($record->bonus ?? 'NULL') . "\n";
    echo "Potongan: " . ($record->potongan ?? 'NULL') . "\n";
    echo "Total Gaji: " . ($record->total_gaji ?? 'NULL') . "\n";
    echo "Status Pembayaran: " . ($record->status_pembayaran ?? 'NULL') . "\n";
    echo "----------------------------------------\n";
}

if ($payrollRecords->isEmpty()) {
    echo "No existing payroll records found.\n";
    echo "The user might be looking at a new payroll form that's not working.\n";
} else {
    echo "\n=== ISSUE IDENTIFIED ===\n";
    echo "The user is looking at an EXISTING payroll record that was saved with incorrect values.\n";
    echo "The frontend fixes won't affect existing records - they need to create a new payroll or update the existing one.\n";
}
