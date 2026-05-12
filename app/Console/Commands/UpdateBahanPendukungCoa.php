<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\BahanPendukung;

class UpdateBahanPendukungCoa extends Command
{
    protected $signature = 'update:bahan-pendukung-coa';
    protected $description = 'Update COA persediaan for bahan pendukung items';

    public function handle()
    {
        $mapping = [
            'Tepung Terigu' => '1152',
            'Tepung Maizena' => '1153',
            'Lada' => '1154',
            'Bubuk Kaldu Ayam' => '1155',
            'Bubuk Bawang Putih' => '1156',
        ];

        foreach ($mapping as $nama => $coaId) {
            $item = BahanPendukung::where('nama_bahan', $nama)->first();
            if ($item) {
                $item->update(['coa_persediaan_id' => $coaId]);
                $this->info("Updated {$nama} with COA {$coaId}");
            } else {
                $this->warn("Item {$nama} not found");
            }
        }

        $this->info('All bahan pendukung COA updated successfully!');
    }
}
