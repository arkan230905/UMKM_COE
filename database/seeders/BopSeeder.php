<?php

namespace Database\Seeders;

use App\Models\Bop;
use App\Models\Coa;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class BopSeeder extends Seeder
{
    public function run()
    {
        // Ambil akun-akun BOP (dengan kode 51xx)
        $akunBop = Coa::where('kode_akun', 'like', '51%')->get();
        
        $periode = now()->format('Y-m');
        
        foreach ($akunBop as $akun) {
            // Set budget default untuk setiap akun BOP
            Bop::updateOrCreate(
                [
                    'kode_akun' => $akun->kode_akun,
                    'periode' => $periode
                ],
                [
                    'nama_biaya' => $akun->nama_akun,
                    'jumlah' => 0, // Diisi saat ada transaksi
                ]
            );
        }
    }
}
