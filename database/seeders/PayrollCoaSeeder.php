<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Coa;

class PayrollCoaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $config = config('penggajian_journal');

        // COA yang perlu ditambahkan jika belum ada
        $coasToCreate = [
            [
                'kode_akun' => $config['beban_tunjangan'],
                'nama_akun' => 'Beban Tunjangan',
                'kategori_akun' => 'Beban Operasional',
                'tipe_akun' => 'Beban',
                'saldo_normal' => 'debit',
                'keterangan' => 'Akun untuk mencatat beban tunjangan karyawan',
                'saldo_awal' => 0,
            ],
            [
                'kode_akun' => $config['beban_asuransi'],
                'nama_akun' => 'Beban Asuransi',
                'kategori_akun' => 'Beban Operasional',
                'tipe_akun' => 'Beban',
                'saldo_normal' => 'debit',
                'keterangan' => 'Akun untuk mencatat beban asuransi/BPJS karyawan',
                'saldo_awal' => 0,
            ],
        ];

        foreach ($coasToCreate as $coaData) {
            $existing = Coa::where('kode_akun', $coaData['kode_akun'])->first();
            
            if (!$existing) {
                Coa::create($coaData);
                echo "✅ COA dibuat: {$coaData['kode_akun']} - {$coaData['nama_akun']}\n";
            } else {
                echo "⏭️  COA sudah ada: {$coaData['kode_akun']} - {$existing->nama_akun}\n";
            }
        }

        echo "\n✅ Seeding COA Penggajian selesai.\n";
    }
}
