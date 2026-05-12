<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SatuanGrup;
use App\Models\Satuan;
use App\Services\SatuanKonversiService;

class SatuanKonversiSeeder extends Seeder
{
    public function run(): void
    {
        // Inisialisasi grup satuan default
        SatuanKonversiService::inisialisasiGrupDefault();
        
        $this->command->info('Grup satuan default berhasil diinisialisasi!');
    }
}
