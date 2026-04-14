<?php

/**
 * Script untuk memperbaiki mapping COA penyusutan
 * Berdasarkan gambar, terlihat ada masalah dengan COA yang digunakan
 */

require_once 'vendor/autoload.php';

use App\Models\Aset;
use App\Models\Coa;
use Illuminate\Support\Facades\DB;

echo "=== PERBAIKAN MAPPING COA PENYUSUTAN ===\n\n";

// Berdasarkan gambar, COA yang digunakan:
$expectedCoaMapping = [
    'mesin' => [
        'expense_coa' => '555', // BOP TL - Biaya Penyusutan Mesin
        'accum_coa' => '126',   // Akumulasi Penyusutan Mesin
        'keywords' => ['mesin', 'produksi']
    ],
    'peralatan' => [
        'expense_coa' => '553', // BOP TL - Biaya Penyusutan Peralatan  
        'accum_coa' => '120',   // Akumulasi Penyusutan Peralatan
        'keywords' => ['peralatan', 'produksi']
    ],
    'kendaraan' => [
        'expense_coa' => '554', // BOP TL - Biaya Penyusutan Kendaraan
        'accum_coa' => '124',   // Akumulasi Penyusutan Kendaraan
        'keywords' => ['kendaraan', 'pengangkut', 'barang']
    ]
];

echo "1. MEMERIKSA COA YANG ADA...\n\n";

// Periksa apakah COA sudah ada
foreach ($expectedCoaMapping as $category => $mapping) {
    echo "Kategori: " . strtoupper($category) . "\n";
    
    // Cek COA Beban Penyusutan
    $expenseCoa = Coa::where('kode_akun', $mapping['expense_coa'])->first();
    if ($expenseCoa) {
        echo "  ✓ COA Beban ({$mapping['expense_coa']}): {$expenseCoa->nama_akun}\n";
    } else {
        echo "  ✗ COA Beban ({$mapping['expense_coa']}): TIDAK DITEMUKAN\n";
    }
    
    // Cek COA Akumulasi Penyusutan
    $accumCoa = Coa::where('kode_akun', $mapping['accum_coa'])->first();
    if ($accumCoa) {
        echo "  ✓ COA Akumulasi ({$mapping['accum_coa']}): {$accumCoa->nama_akun}\n";
    } else {
        echo "  ✗ COA Akumulasi ({$mapping['accum_coa']}): TIDAK DITEMUKAN\n";
    }
    
    echo "\n";
}

echo "2. MEMERIKSA MAPPING ASET...\n\n";

$asets = Aset::whereNotNull('metode_penyusutan')->get();
$needsMapping = [];

foreach ($asets as $aset) {
    echo "Aset: {$aset->nama_aset}\n";
    
    // Tentukan kategori berdasarkan nama aset
    $category = null;
    $namaLower = strtolower($aset->nama_aset);
    
    foreach ($expectedCoaMapping as $cat => $mapping) {
        foreach ($mapping['keywords'] as $keyword) {
            if (strpos($namaLower, $keyword) !== false) {
                $category = $cat;
                break 2;
            }
        }
    }
    
    if (!$category) {
        echo "  ⚠ Kategori tidak dapat ditentukan otomatis\n";
        $category = 'peralatan'; // Default
    } else {
        echo "  → Kategori: {$category}\n";
    }
    
    // Periksa mapping saat ini
    $currentExpenseCoa = $aset->expense_coa_id;
    $currentAccumCoa = $aset->accum_depr_coa_id;
    
    $expectedExpenseCoa = Coa::where('kode_akun', $expectedCoaMapping[$category]['expense_coa'])->first();
    $expectedAccumCoa = Coa::where('kode_akun', $expectedCoaMapping[$category]['accum_coa'])->first();
    
    $needsUpdate = false;
    
    if (!$currentExpenseCoa || ($expectedExpenseCoa && $currentExpenseCoa != $expectedExpenseCoa->id)) {
        echo "  ✗ COA Beban perlu diperbaiki\n";
        $needsUpdate = true;
    } else {
        echo "  ✓ COA Beban sudah benar\n";
    }
    
    if (!$currentAccumCoa || ($expectedAccumCoa && $currentAccumCoa != $expectedAccumCoa->id)) {
        echo "  ✗ COA Akumulasi perlu diperbaiki\n";
        $needsUpdate = true;
    } else {
        echo "  ✓ COA Akumulasi sudah benar\n";
    }
    
    if ($needsUpdate) {
        $needsMapping[] = [
            'aset' => $aset,
            'category' => $category,
            'expected_expense_coa' => $expectedExpenseCoa,
            'expected_accum_coa' => $expectedAccumCoa
        ];
    }
    
    echo "\n";
}

if (empty($needsMapping)) {
    echo "✓ Semua aset sudah memiliki mapping COA yang benar!\n";
} else {
    echo "3. MEMPERBAIKI MAPPING COA...\n\n";
    
    foreach ($needsMapping as $mapping) {
        $aset = $mapping['aset'];
        echo "Memperbaiki: {$aset->nama_aset}\n";
        
        $updateData = [];
        
        if ($mapping['expected_expense_coa']) {
            $updateData['expense_coa_id'] = $mapping['expected_expense_coa']->id;
            echo "  → Set COA Beban: {$mapping['expected_expense_coa']->kode_akun} - {$mapping['expected_expense_coa']->nama_akun}\n";
        }
        
        if ($mapping['expected_accum_coa']) {
            $updateData['accum_depr_coa_id'] = $mapping['expected_accum_coa']->id;
            echo "  → Set COA Akumulasi: {$mapping['expected_accum_coa']->kode_akun} - {$mapping['expected_accum_coa']->nama_akun}\n";
        }
        
        if (!empty($updateData)) {
            // Uncomment untuk menerapkan perubahan
            // $aset->update($updateData);
            echo "  ✓ Mapping diperbaiki\n";
        }
        
        echo "\n";
    }
    
    echo "CATATAN: Uncomment baris update untuk menerapkan perubahan.\n\n";
}

echo "4. MEMBUAT COA YANG HILANG (JIKA DIPERLUKAN)...\n\n";

$missingCoas = [];

foreach ($expectedCoaMapping as $category => $mapping) {
    // Cek COA Beban
    $expenseCoa = Coa::where('kode_akun', $mapping['expense_coa'])->first();
    if (!$expenseCoa) {
        $missingCoas[] = [
            'kode_akun' => $mapping['expense_coa'],
            'nama_akun' => "BOP TL - Biaya Penyusutan " . ucfirst($category),
            'tipe_akun' => 'Expense',
            'kategori_akun' => 'Biaya Penyusutan',
            'saldo_normal' => 'debit'
        ];
    }
    
    // Cek COA Akumulasi
    $accumCoa = Coa::where('kode_akun', $mapping['accum_coa'])->first();
    if (!$accumCoa) {
        $missingCoas[] = [
            'kode_akun' => $mapping['accum_coa'],
            'nama_akun' => "Akumulasi Penyusutan " . ucfirst($category),
            'tipe_akun' => 'Asset',
            'kategori_akun' => 'Akumulasi Penyusutan',
            'saldo_normal' => 'credit'
        ];
    }
}

if (empty($missingCoas)) {
    echo "✓ Semua COA yang diperlukan sudah ada!\n";
} else {
    echo "COA yang perlu dibuat:\n\n";
    
    foreach ($missingCoas as $coa) {
        echo "- {$coa['kode_akun']}: {$coa['nama_akun']} ({$coa['tipe_akun']})\n";
        
        // Uncomment untuk membuat COA
        /*
        Coa::create([
            'kode_akun' => $coa['kode_akun'],
            'nama_akun' => $coa['nama_akun'],
            'tipe_akun' => $coa['tipe_akun'],
            'kategori_akun' => $coa['kategori_akun'],
            'saldo_normal' => $coa['saldo_normal'],
            'is_active' => true
        ]);
        */
    }
    
    echo "\nCATATAN: Uncomment kode create untuk membuat COA yang hilang.\n";
}

echo "\n=== LANGKAH SELANJUTNYA ===\n\n";
echo "1. Pastikan semua COA sudah dibuat dengan benar\n";
echo "2. Pastikan mapping aset ke COA sudah benar\n";
echo "3. Jalankan recalculate depreciation untuk semua aset\n";
echo "4. Post ulang jurnal penyusutan dengan COA yang benar\n";
echo "5. Verifikasi bahwa nominal di jurnal sudah sesuai\n\n";

echo "Command yang bisa dijalankan:\n";
echo "php artisan depreciation:fix-discrepancy --dry-run\n";
echo "php artisan depreciation:recalculate\n";
echo "php artisan depreciation:post-monthly\n\n";