<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Adding custom COA accounts...\n\n";

// COA data yang akan ditambahkan
$coaData = [
    ['kode_akun' => '1110', 'nama_akun' => 'Kas', 'tipe_akun' => 'Asset', 'kategori_akun' => 'Aktiva Lancar'],
    ['kode_akun' => '1120', 'nama_akun' => 'Bank', 'tipe_akun' => 'Asset', 'kategori_akun' => 'Aktiva Lancar'],
    ['kode_akun' => '1130', 'nama_akun' => 'PPN Masukan', 'tipe_akun' => 'Asset', 'kategori_akun' => 'Aktiva Lancar'],
    ['kode_akun' => '1210', 'nama_akun' => 'Persediaan Bahan Baku', 'tipe_akun' => 'Asset', 'kategori_akun' => 'Aktiva Lancar'],
    ['kode_akun' => '12111', 'nama_akun' => 'Persediaan Ayam Potong', 'tipe_akun' => 'Asset', 'kategori_akun' => 'Aktiva Lancar'],
    ['kode_akun' => '12112', 'nama_akun' => 'Persediaan Ayam Kampung', 'tipe_akun' => 'Asset', 'kategori_akun' => 'Aktiva Lancar'],
    ['kode_akun' => '1220', 'nama_akun' => 'Persediaan Bahan Penolong', 'tipe_akun' => 'Asset', 'kategori_akun' => 'Aktiva Lancar'],
    ['kode_akun' => '122111', 'nama_akun' => 'Persediaan Air', 'tipe_akun' => 'Asset', 'kategori_akun' => 'Aktiva Lancar'],
    ['kode_akun' => '122112', 'nama_akun' => 'Persediaan Minyak Goreng', 'tipe_akun' => 'Asset', 'kategori_akun' => 'Aktiva Lancar'],
    ['kode_akun' => '122113', 'nama_akun' => 'Persediaan Gas', 'tipe_akun' => 'Asset', 'kategori_akun' => 'Aktiva Lancar'],
    ['kode_akun' => '122114', 'nama_akun' => 'Persediaan Ketumbar', 'tipe_akun' => 'Asset', 'kategori_akun' => 'Aktiva Lancar'],
    ['kode_akun' => '122115', 'nama_akun' => 'Persediaan Cabe Merah', 'tipe_akun' => 'Asset', 'kategori_akun' => 'Aktiva Lancar'],
    ['kode_akun' => '122116', 'nama_akun' => 'Persediaan Bawang Putih', 'tipe_akun' => 'Asset', 'kategori_akun' => 'Aktiva Lancar'],
    ['kode_akun' => '122117', 'nama_akun' => 'Persediaan Tepung Maizena', 'tipe_akun' => 'Asset', 'kategori_akun' => 'Aktiva Lancar'],
    ['kode_akun' => '122118', 'nama_akun' => 'Persediaan Merica Bubuk', 'tipe_akun' => 'Asset', 'kategori_akun' => 'Aktiva Lancar'],
    ['kode_akun' => '122119', 'nama_akun' => 'Persediaan Listrik', 'tipe_akun' => 'Asset', 'kategori_akun' => 'Aktiva Lancar'],
    ['kode_akun' => '122120', 'nama_akun' => 'Bawang Merah', 'tipe_akun' => 'Asset', 'kategori_akun' => 'Aktiva Lancar'],
    ['kode_akun' => '122121', 'nama_akun' => 'Kemasan', 'tipe_akun' => 'Asset', 'kategori_akun' => 'Aktiva Lancar'],
    ['kode_akun' => '2110', 'nama_akun' => 'Utang Penjualan', 'tipe_akun' => 'Liability', 'kategori_akun' => 'Kewajiban Lancar'],
    ['kode_akun' => '2111', 'nama_akun' => 'Utang Gaji', 'tipe_akun' => 'Liability', 'kategori_akun' => 'Kewajiban Lancar'],
    ['kode_akun' => '2120', 'nama_akun' => 'Utang Pajak', 'tipe_akun' => 'Liability', 'kategori_akun' => 'Kewajiban Lancar'],
    ['kode_akun' => '3110', 'nama_akun' => 'Modal', 'tipe_akun' => 'Equity', 'kategori_akun' => 'Modal'],
    ['kode_akun' => '4110', 'nama_akun' => 'Pendapatan Penjualan', 'tipe_akun' => 'Revenue', 'kategori_akun' => 'Pendapatan'],
    ['kode_akun' => '4120', 'nama_akun' => 'Retur Penjualan', 'tipe_akun' => 'Revenue', 'kategori_akun' => 'Pendapatan'],
    ['kode_akun' => '5110', 'nama_akun' => 'Beban Gaji', 'tipe_akun' => 'Expense', 'kategori_akun' => 'Beban Usaha'],
    ['kode_akun' => '5120', 'nama_akun' => 'Beban Pajak', 'tipe_akun' => 'Expense', 'kategori_akun' => 'Beban Usaha'],
    ['kode_akun' => '5130', 'nama_akun' => 'Beban Sewa', 'tipe_akun' => 'Expense', 'kategori_akun' => 'Beban Usaha'],
    ['kode_akun' => '5140', 'nama_akun' => 'Beban Listrik', 'tipe_akun' => 'Expense', 'kategori_akun' => 'Beban Usaha']
];

try {
    $successCount = 0;
    $skipCount = 0;
    $errorCount = 0;
    
    foreach ($coaData as $coa) {
        try {
            // Check if COA already exists
            $existing = \DB::table('coas')
                ->where('kode_akun', $coa['kode_akun'])
                ->first();
            
            if ($existing) {
                // Update existing COA
                \DB::table('coas')
                    ->where('kode_akun', $coa['kode_akun'])
                    ->update([
                        'nama_akun' => $coa['nama_akun'],
                        'tipe_akun' => $coa['tipe_akun'],
                        'kategori_akun' => $coa['kategori_akun'],
                        'saldo_normal' => in_array($coa['tipe_akun'], ['Asset', 'Expense']) ? 'debit' : 'kredit',
                        'updated_at' => now()
                    ]);
                echo "✓ Updated: {$coa['kode_akun']} - {$coa['nama_akun']}\n";
                $successCount++;
            } else {
                // Insert new COA
                \DB::table('coas')->insert([
                    'kode_akun' => $coa['kode_akun'],
                    'nama_akun' => $coa['nama_akun'],
                    'tipe_akun' => $coa['tipe_akun'],
                    'kategori_akun' => $coa['kategori_akun'],
                    'is_akun_header' => 0,
                    'saldo_normal' => in_array($coa['tipe_akun'], ['Asset', 'Expense']) ? 'debit' : 'kredit',
                    'saldo_awal' => 0,
                    'posted_saldo_awal' => 0,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                echo "+ Added: {$coa['kode_akun']} - {$coa['nama_akun']}\n";
                $successCount++;
            }
            
        } catch (\Exception $e) {
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                echo "- Skipped (duplicate): {$coa['kode_akun']} - {$coa['nama_akun']}\n";
                $skipCount++;
            } else {
                echo "✗ Error: {$coa['kode_akun']} - {$coa['nama_akun']} - " . $e->getMessage() . "\n";
                $errorCount++;
            }
        }
    }
    
    echo "\n=== COA IMPORT COMPLETE ===\n";
    echo "Success/Updated: $successCount accounts\n";
    echo "Skipped: $skipCount accounts\n";
    echo "Errors: $errorCount accounts\n\n";
    
    // Show summary by type
    echo "Summary by Account Type:\n";
    echo str_repeat("=", 40) . "\n";
    
    $summary = \DB::table('coas')
        ->selectRaw('tipe_akun, COUNT(*) as count')
        ->groupBy('tipe_akun')
        ->orderBy('tipe_akun')
        ->get();
    
    foreach ($summary as $item) {
        echo sprintf("%-15s: %d accounts\n", $item->tipe_akun, $item->count);
    }
    
    echo str_repeat("=", 40) . "\n";
    $totalCount = \DB::table('coas')->count();
    echo "Total COA accounts: $totalCount\n";
    
    echo "\n✅ COA accounts have been successfully added/updated!\n";
    
} catch (\Exception $e) {
    echo "COA import failed: " . $e->getMessage() . "\n";
}
