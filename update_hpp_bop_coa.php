<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

// Update semua data di harga_pokok_produksi_bop yang punya komponen dengan COA 536
$hppBops = DB::table('harga_pokok_produksi_bop')->where('user_id', 2)->get();

foreach ($hppBops as $hpp) {
    $bopProses = DB::table('bop_proses')->where('id', $hpp->bop_proses_id)->first();
    
    if ($bopProses && $bopProses->komponen_bop) {
        $komponen = json_decode($bopProses->komponen_bop, true);
        $updated = false;
        
        foreach ($komponen as &$k) {
            if (isset($k['coa_kode']) && $k['coa_kode'] == '536') {
                echo "Found COA 536 in BOP ID {$bopProses->id}, updating to 57...\n";
                $k['coa_kode'] = '57';
                $k['coa_nama'] = 'Biaya Air & Kebersihan';
                $updated = true;
            }
        }
        
        if ($updated) {
            DB::table('bop_proses')->where('id', $bopProses->id)->update([
                'komponen_bop' => json_encode($komponen),
                'updated_at' => now()
            ]);
            echo "✅ Updated BOP ID {$bopProses->id}\n";
        }
    }
}

echo "\n✅ Selesai update HPP BOP!\n";
