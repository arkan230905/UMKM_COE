<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Services\JournalService;

echo "=== CREATING PAYROLL JOURNAL ENTRIES ===\n\n";

// Get all penggajian records
$penggajian = DB::table('penggajians')->get();

echo "Total penggajian records: " . count($penggajian) . "\n\n";

foreach ($penggajian as $pg) {
    echo "Processing Penggajian ID {$pg->id}: {$pg->tanggal_penggajian}, Total: {$pg->total_gaji}\n";
    
    // Check if journal entry already exists
    $existing = DB::table('journal_entries')
        ->where('ref_type', 'payroll')
        ->where('ref_id', $pg->id)
        ->first();
    
    if ($existing) {
        echo "  ✓ Journal entry already exists (ID: {$existing->id})\n\n";
        continue;
    }
    
    // Create journal entry for payroll
    try {
        $lines = [];
        
        // BTKL (Biaya Tenaga Kerja Langsung)
        if ($pg->gaji_pokok > 0) {
            $btkl_coa = DB::table('coas')->where('kode_akun', '52')->first();
            $lines[] = [
                'coa_id' => $btkl_coa->id,
                'debit' => $pg->gaji_pokok,
                'credit' => 0,
                'memo' => 'Gaji Pokok'
            ];
        }
        
        // Beban Tunjangan
        if ($pg->total_tunjangan > 0) {
            $tunjangan_coa = DB::table('coas')->where('kode_akun', '513')->first();
            $lines[] = [
                'coa_id' => $tunjangan_coa->id,
                'debit' => $pg->total_tunjangan,
                'credit' => 0,
                'memo' => 'Beban Tunjangan'
            ];
        }
        
        // Beban Asuransi
        if ($pg->asuransi > 0) {
            $asuransi_coa = DB::table('coas')->where('kode_akun', '514')->first();
            $lines[] = [
                'coa_id' => $asuransi_coa->id,
                'debit' => $pg->asuransi,
                'credit' => 0,
                'memo' => 'Beban Asuransi'
            ];
        }
        
        // Beban Bonus
        if ($pg->bonus > 0) {
            $bonus_coa = DB::table('coas')->where('kode_akun', '515')->first();
            $lines[] = [
                'coa_id' => $bonus_coa->id,
                'debit' => $pg->bonus,
                'credit' => 0,
                'memo' => 'Beban Bonus'
            ];
        }
        
        // Kas (credit side)
        $kas_coa = DB::table('coas')->where('kode_akun', '112')->first();
        $lines[] = [
            'coa_id' => $kas_coa->id,
            'debit' => 0,
            'credit' => $pg->total_gaji,
            'memo' => 'Pembayaran Gaji'
        ];
        
        // Create journal entry
        $service = new JournalService();
        $entry = $service->post(
            $pg->tanggal_penggajian,
            'payroll',
            $pg->id,
            "Penggajian",
            $lines
        );
        
        echo "  ✓ Created journal entry ID: {$entry->id}\n";
        
        // Verify balance
        $totalDebit = collect($lines)->sum('debit');
        $totalCredit = collect($lines)->sum('credit');
        echo "    Debit: {$totalDebit}, Credit: {$totalCredit}\n\n";
        
    } catch (\Exception $e) {
        echo "  ✗ Error: " . $e->getMessage() . "\n\n";
    }
}

echo "\n=== VERIFICATION ===\n\n";

// Verify all payroll entries
$payrollEntries = DB::table('journal_entries')
    ->where('ref_type', 'payroll')
    ->get();

echo "Total payroll journal entries: " . count($payrollEntries) . "\n";

foreach ($payrollEntries as $entry) {
    $lines = DB::table('journal_lines')
        ->where('journal_entry_id', $entry->id)
        ->get();
    
    $totalDebit = $lines->sum('debit');
    $totalCredit = $lines->sum('credit');
    $status = ($totalDebit == $totalCredit) ? '✓' : '✗';
    
    echo "{$status} Entry {$entry->id} ({$entry->tanggal}): Debit {$totalDebit}, Credit {$totalCredit}\n";
}
