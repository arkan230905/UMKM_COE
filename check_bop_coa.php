<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

// Cek BOP data
$bops = DB::table('bop_proses')->where('user_id', 2)->get();

foreach ($bops as $bop) {
    echo "ID: {$bop->id} - {$bop->nama_bop_proses}\n";
    $komponen = json_decode($bop->komponen_bop, true);
    if ($komponen) {
        $updated = false;
        foreach ($komponen as &$k) {
            $nama = $k['nama_komponen'] ?? $k['nama'] ?? 'Unknown';
            $coa = $k['coa_kode'] ?? $k['coa'] ?? '';
            echo "  - {$nama}: COA {$coa}\n";
            if ($coa == '536') {
                echo "    ⚠️ FOUND COA 536! Updating to 57...\n";
                if (isset($k['coa_kode'])) {
                    $k['coa_kode'] = '57';
                } else {
                    $k['coa'] = '57';
                }
                $updated = true;
            }
        }
        // Update
        if ($updated) {
            DB::table('bop_proses')->where('id', $bop->id)->update([
                'komponen_bop' => json_encode($komponen),
                'updated_at' => now()
            ]);
            echo "  ✅ Updated!\n";
        }
    }
}

echo "\n✅ Selesai!\n";
