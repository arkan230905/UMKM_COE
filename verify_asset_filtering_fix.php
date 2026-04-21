<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Carbon\Carbon;

echo "=== Verify Asset Filtering Fix ===" . PHP_EOL;

// Test the fixed filtering logic
echo PHP_EOL . "Testing Fixed Asset Filtering:" . PHP_EOL;

// Get COA data
$coas = DB::table('coas')
    ->select('kode_akun', 'nama_akun', 'tipe_akun', 'saldo_normal', 'saldo_awal', 'kategori_akun')
    ->groupBy('kode_akun', 'nama_akun', 'tipe_akun', 'saldo_normal', 'saldo_awal', 'kategori_akun')
    ->orderBy('kode_akun')
    ->get();

// Test the filtering logic
$from = Carbon::create(2026, 4, 1)->format('Y-m-d');
$to = Carbon::create(2026, 4, 1)->endOfMonth()->format('Y-m-d');

// Simulate the getLaporanPosisiKeuanganData logic for testing
$neracaSaldoData = DB::select("
    SELECT 
        coa_summary.kode_akun,
        coa_summary.nama_akun,
        coa_summary.tipe_akun,
        coa_summary.kategori_akun,
        coa_summary.saldo_normal,
        coa_summary.saldo_awal,
        
        -- Total mutasi sampai akhir periode dari journal_lines
        COALESCE(jl_data.total_debit, 0) as jl_total_debit,
        COALESCE(jl_data.total_kredit, 0) as jl_total_kredit,
        
        -- Total mutasi sampai akhir periode dari jurnal_umum
        COALESCE(ju_data.total_debit, 0) as ju_total_debit,
        COALESCE(ju_data.total_kredit, 0) as ju_total_kredit
        
    FROM (
        -- Subquery untuk mendapatkan summary COA tanpa duplikasi
        SELECT 
            c.kode_akun,
            MIN(c.nama_akun) as nama_akun,
            MIN(c.tipe_akun) as tipe_akun,
            MIN(c.kategori_akun) as kategori_akun,
            MIN(c.saldo_normal) as saldo_normal,
            SUM(COALESCE(c.saldo_awal, 0)) as saldo_awal
        FROM coas c
        GROUP BY c.kode_akun
    ) coa_summary
    
    -- LEFT JOIN untuk total mutasi sampai akhir periode dari journal_lines
    LEFT JOIN (
        SELECT 
            c2.kode_akun,
            SUM(jl.debit) as total_debit,
            SUM(jl.credit) as total_kredit
        FROM journal_lines jl
        JOIN journal_entries je ON jl.journal_entry_id = je.id
        JOIN coas c2 ON jl.coa_id = c2.id
        WHERE je.tanggal <= ?
        GROUP BY c2.kode_akun
    ) jl_data ON coa_summary.kode_akun = jl_data.kode_akun
    
    -- LEFT JOIN untuk total mutasi sampai akhir periode dari jurnal_umum
    LEFT JOIN (
        SELECT 
            c2.kode_akun,
            SUM(ju.debit) as total_debit,
            SUM(ju.kredit) as total_kredit
        FROM jurnal_umum ju
        JOIN coas c2 ON ju.coa_id = c2.id
        WHERE ju.tanggal <= ?
        GROUP BY c2.kode_akun
    ) ju_data ON coa_summary.kode_akun = ju_data.kode_akun
    
    ORDER BY coa_summary.kode_akun
", [$to, $to]);

$coas = collect($neracaSaldoData)->map(function($item) {
    return (object) $item;
});

// Test asset filtering
$asetLancar = $coas->filter(function($coa) {
    // Only include Asset accounts (Aktiva -> Aset)
    if (!in_array($coa->tipe_akun, ['Asset', 'asset', 'Aktiva', 'ASET'])) return false;
    
    // Current Assets: Account codes 1xx (all accounts starting with 1)
    $isCurrentAsset = substr($coa->kode_akun, 0, 1) === '1';
    if (!$isCurrentAsset) return false;
    
    return true;
});

echo "ASET LANCAR Accounts Found: " . $asetLancar->count() . PHP_EOL;
foreach ($asetLancar as $coa) {
    echo "- " . $coa->kode_akun . ": " . $coa->nama_akun . " (" . $coa->tipe_akun . ")" . PHP_EOL;
}

// Test asset tidak lancar filtering
$asetTidakLancar = $coas->filter(function($coa) {
    // Only include Asset accounts (Aktiva -> Aset)
    if (!in_array($coa->tipe_akun, ['Asset', 'asset', 'Aktiva', 'ASET'])) return false;
    
    // Non-Current Assets: Account codes 2xx (all accounts starting with 2)
    $isNonCurrentAsset = substr($coa->kode_akun, 0, 1) === '2';
    if (!$isNonCurrentAsset) return false;
    
    return true;
});

echo PHP_EOL . "ASET TIDAK LANCAR Accounts Found: " . $asetTidakLancar->count() . PHP_EOL;
foreach ($asetTidakLancar as $coa) {
    echo "- " . $coa->kode_akun . ": " . $coa->nama_akun . " (" . $coa->tipe_akun . ")" . PHP_EOL;
}

echo PHP_EOL . "=== Expected Results ===" . PHP_EOL;
echo "ASET LANCAR should now show accounts with tipe_akun = 'ASET'" . PHP_EOL;
echo "ASET TIDAK LANCAR should show accounts with tipe_akun = 'ASET' and kode starting with 2" . PHP_EOL;
