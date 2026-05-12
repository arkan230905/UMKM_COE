<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Smart Import Data COA (95 Akun) ===\n\n";

// Baca file SQL asli
$sqlContent = file_get_contents(__DIR__ . '/import_coa_data.sql');

// Extract data dari INSERT statement
preg_match_all('/\((\d+),\s*\'([^\']+)\',\s*\'([^\']+)\',\s*\'([^\']+)\',\s*\'([^\']+)\',\s*(\d+),\s*(NULL|\'[^\']+\'),\s*\'([^\']+)\',\s*([\d.]+)/', $sqlContent, $matches, PREG_SET_ORDER);

$inserted = 0;
$updated = 0;
$skipped = 0;

foreach ($matches as $match) {
    $kode_akun = $match[2];
    $nama_akun = $match[3];
    $tipe_akun = $match[4];
    $kategori_akun = $match[5];
    $is_akun_header = $match[6];
    $kode_induk = $match[7] === 'NULL' ? null : trim($match[7], "'");
    $saldo_normal = $match[8];
    $saldo_awal = $match[9];
    
    try {
        $existing = DB::table('coas')
            ->where('kode_akun', $kode_akun)
            ->where('company_id', 1)
            ->first();
        
        $data = [
            'kode_akun' => $kode_akun,
            'nama_akun' => $nama_akun,
            'tipe_akun' => $tipe_akun,
            'kategori_akun' => $kategori_akun,
            'is_akun_header' => $is_akun_header,
            'kode_induk' => $kode_induk,
            'saldo_normal' => $saldo_normal,
            'saldo_awal' => $saldo_awal,
            'tanggal_saldo_awal' => null,
            'posted_saldo_awal' => 0,
            'keterangan' => null,
            'company_id' => 1,
            'updated_at' => now()
        ];
        
        if ($existing) {
            DB::table('coas')
                ->where('id', $existing->id)
                ->update($data);
            $updated++;
            echo ".";
        } else {
            $data['created_at'] = now();
            DB::table('coas')->insert($data);
            $inserted++;
            echo "+";
        }
    } catch (\Exception $e) {
        $skipped++;
        echo "x";
    }
    
    if (($inserted + $updated + $skipped) % 50 == 0) {
        echo "\n";
    }
}

echo "\n\n";
echo "✓ Inserted: $inserted akun baru\n";
echo "✓ Updated: $updated akun existing\n";
if ($skipped > 0) {
    echo "✗ Skipped: $skipped akun (error)\n";
}

$total = DB::table('coas')->where('company_id', 1)->count();
echo "\n✓ Total akun COA di database: $total\n";

// Tampilkan breakdown
echo "\nBreakdown per Tipe Akun:\n";
$breakdown = DB::table('coas')
    ->select('tipe_akun', DB::raw('COUNT(*) as jumlah'))
    ->where('company_id', 1)
    ->groupBy('tipe_akun')
    ->orderBy('tipe_akun')
    ->get();

foreach ($breakdown as $item) {
    echo "  - {$item->tipe_akun}: {$item->jumlah} akun\n";
}

echo "\n✓ Import selesai!\n";
