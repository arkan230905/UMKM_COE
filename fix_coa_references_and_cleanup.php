<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Memperbaiki Referensi COA dan Cleanup ===\n\n";

// Step 1: Buat mapping dari COA NULL ke COA company 1
echo "Step 1: Membuat mapping COA NULL -> COA Company 1...\n";
$coasNull = DB::table('coas')->whereNull('company_id')->get();
$coasCompany1 = DB::table('coas')->where('company_id', 1)->get()->keyBy('kode_akun');

$mapping = [];
foreach ($coasNull as $coaNull) {
    $kodeAkun = $coaNull->kode_akun;
    if (isset($coasCompany1[$kodeAkun])) {
        $mapping[$coaNull->id] = $coasCompany1[$kodeAkun]->id;
        echo "  COA ID {$coaNull->id} ({$kodeAkun}) -> ID {$coasCompany1[$kodeAkun]->id}\n";
    } else {
        echo "  ⚠ COA ID {$coaNull->id} ({$kodeAkun}) tidak ada padanan di company 1\n";
    }
}

echo "\nTotal mapping: " . count($mapping) . "\n\n";

// Step 2: Update referensi di tabel-tabel yang menggunakan COA
echo "Step 2: Mengupdate referensi foreign key...\n";

$tablesWithCoaFK = [
    'pembayaran_beban' => ['akun_kas_id', 'akun_beban_id'],
    'pelunasan_utang' => ['akun_kas_id'],
    'penjualans' => ['akun_kas_id'],
    'pembelians' => ['akun_hutang_id'],
    'jurnal_umums' => ['coa_id'],
    'buku_besars' => ['coa_id'],
    'bahan_bakus' => ['coa_id'],
    'bahan_pendukungs' => ['coa_id'],
    'produksis' => ['coa_wip_id'],
];

$totalUpdated = 0;

foreach ($tablesWithCoaFK as $table => $columns) {
    foreach ($columns as $column) {
        foreach ($mapping as $oldId => $newId) {
            try {
                $updated = DB::table($table)
                    ->where($column, $oldId)
                    ->update([$column => $newId]);
                
                if ($updated > 0) {
                    echo "  ✓ $table.$column: $updated baris diupdate ($oldId -> $newId)\n";
                    $totalUpdated += $updated;
                }
            } catch (\Exception $e) {
                // Tabel mungkin tidak ada atau kolom tidak ada
            }
        }
    }
}

echo "\nTotal referensi diupdate: $totalUpdated\n\n";

// Step 3: Hapus COA dengan company_id = NULL
echo "Step 3: Menghapus COA dengan company_id = NULL...\n";
try {
    $deleted = DB::table('coas')->whereNull('company_id')->delete();
    echo "✓ Dihapus: $deleted akun\n\n";
} catch (\Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n\n";
}

// Step 4: Verifikasi
echo "Step 4: Verifikasi hasil...\n";
$totalAfter = DB::table('coas')->count();
$nullAfter = DB::table('coas')->whereNull('company_id')->count();
$company1After = DB::table('coas')->where('company_id', 1)->count();

echo "  Total COA: $totalAfter\n";
echo "  Company NULL: $nullAfter\n";
echo "  Company 1: $company1After\n\n";

if ($nullAfter == 0) {
    echo "✓ Berhasil! Tidak ada COA dengan company_id NULL lagi!\n";
} else {
    echo "⚠ Masih ada $nullAfter COA dengan company_id NULL\n";
}
