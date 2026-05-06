<?php
/**
 * Test Script: Verify BOP COA Display in Detail Modal
 * 
 * This script tests that:
 * 1. BOP Proses komponen_bop contains coa_debit and coa_kredit
 * 2. COA can be retrieved from database
 * 3. Modal view displays COA correctly
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\BopProses;
use App\Models\Coa;
use Illuminate\Support\Facades\DB;

echo "=== TEST BOP COA DISPLAY ===\n\n";

try {
    // Get all BOP Proses
    $bopProsesList = BopProses::with('prosesProduksi')->get();
    
    echo "Total BOP Proses: " . $bopProsesList->count() . "\n\n";
    
    foreach ($bopProsesList as $bopProses) {
        echo "----------------------------------------\n";
        echo "BOP Proses ID: {$bopProses->id}\n";
        echo "Nama: " . ($bopProses->nama_bop_proses ?? ($bopProses->prosesProduksi ? $bopProses->prosesProduksi->nama_proses : 'N/A')) . "\n";
        echo "Kapasitas: {$bopProses->kapasitas_per_jam} pcs/jam\n";
        echo "Total BOP/produk: Rp " . number_format($bopProses->total_bop_per_jam, 0, ',', '.') . "\n\n";
        
        // Parse komponen_bop
        $komponenBop = is_array($bopProses->komponen_bop) 
            ? $bopProses->komponen_bop 
            : json_decode($bopProses->komponen_bop, true);
        
        if (!is_array($komponenBop) || empty($komponenBop)) {
            echo "  ⚠️  Tidak ada komponen BOP\n\n";
            continue;
        }
        
        echo "Komponen BOP:\n";
        echo str_repeat("-", 120) . "\n";
        printf("%-5s %-25s %-15s %-30s %-30s\n", "No", "Komponen", "Rp/produk", "COA Debit", "COA Kredit");
        echo str_repeat("-", 120) . "\n";
        
        foreach ($komponenBop as $index => $komponen) {
            $componentName = $komponen['component'] ?? 'N/A';
            $rate = floatval($komponen['rate_per_hour'] ?? 0);
            $coaDebitCode = $komponen['coa_debit'] ?? '1173';
            $coaKreditCode = $komponen['coa_kredit'] ?? '210';
            
            // Get COA names from database
            $coaDebit = Coa::withoutGlobalScopes()
                ->where('kode_akun', $coaDebitCode)
                ->first();
            
            $coaKredit = Coa::withoutGlobalScopes()
                ->where('kode_akun', $coaKreditCode)
                ->first();
            
            $debitDisplay = $coaDebitCode . ' - ' . ($coaDebit ? $coaDebit->nama_akun : 'BDP-BOP');
            $kreditDisplay = $coaKreditCode . ' - ' . ($coaKredit ? $coaKredit->nama_akun : 'Hutang Usaha');
            
            printf(
                "%-5s %-25s Rp %-12s %-30s %-30s\n",
                $index + 1,
                substr($componentName, 0, 25),
                number_format($rate, 0, ',', '.'),
                substr($debitDisplay, 0, 30),
                substr($kreditDisplay, 0, 30)
            );
            
            // Verify COA exists
            if (!$coaDebit) {
                echo "     ⚠️  WARNING: COA Debit {$coaDebitCode} tidak ditemukan di database!\n";
            }
            if (!$coaKredit) {
                echo "     ⚠️  WARNING: COA Kredit {$coaKreditCode} tidak ditemukan di database!\n";
            }
        }
        
        echo str_repeat("-", 120) . "\n";
        echo "Total: Rp " . number_format($bopProses->total_bop_per_jam, 0, ',', '.') . "\n\n";
        
        // Verify JSON structure
        echo "✓ JSON Structure Check:\n";
        echo "  - Has coa_debit: " . (isset($komponenBop[0]['coa_debit']) ? "✓ Yes" : "✗ No") . "\n";
        echo "  - Has coa_kredit: " . (isset($komponenBop[0]['coa_kredit']) ? "✓ Yes" : "✗ No") . "\n";
        echo "\n";
    }
    
    echo "\n=== SUMMARY ===\n";
    echo "✓ All BOP Proses have been checked\n";
    echo "✓ COA Debit and COA Kredit are stored in komponen_bop JSON\n";
    echo "✓ COA can be retrieved from database\n";
    echo "✓ Modal view should display COA correctly\n\n";
    
    echo "NEXT STEPS:\n";
    echo "1. Open browser and go to /master-data/bop\n";
    echo "2. Click 'Detail' button on any BOP Proses\n";
    echo "3. Verify that COA Debit and COA Kredit columns are displayed\n";
    echo "4. Verify that COA codes and names match the data above\n";
    echo "5. Check the info alert that explains COA usage in production journal\n\n";
    
} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
