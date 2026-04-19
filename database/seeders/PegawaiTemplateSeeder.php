<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pegawai;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PegawaiTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding pegawai templates for new tenants...');
        
        // Template pegawai yang akan tersedia untuk semua tenant baru
        $pegawaiTemplates = [
            [
                'kode_pegawai' => 'PGW0001',
                'nama' => 'Ahmad Suryanto',
                'email' => 'ahmad@gmail.com',
                'no_telepon' => '089561985919',
                'alamat' => 'Jambi',
                'jenis_kelamin' => 'L',
                'jabatan' => 'Perbumbuan',
                'bank' => 'BRI',
                'nomor_rekening' => '2345234242',
                'nama_rekening' => 'Ahmad Suryanto',
                'gaji' => 0.00,
                'gaji_pokok' => 0.00,
                'tarif_per_jam' => 18000.00,
                'nama_bank' => 'BRI',
                'no_rekening' => '2345234242',
                'kategori' => 'BTKL',
                'asuransi' => 80000.00,
                'tarif' => 18000.00,
                'tarif_lembur' => 0.00,
                'tunjangan' => 0.00,
                'jenis_pegawai' => 'btkl',
                'keterangan' => 'Template pegawai Perbumbuan untuk tenant baru',
            ],
            [
                'kode_pegawai' => 'PGW0002',
                'nama' => 'Rina Wijaya',
                'email' => 'rina@gmail.com',
                'no_telepon' => '087645678901',
                'alamat' => 'Jalan Sukapura',
                'jenis_kelamin' => 'P',
                'jabatan' => 'Pengemasan',
                'bank' => 'BRI',
                'nomor_rekening' => '234567456343',
                'nama_rekening' => 'Rina Wijaya',
                'gaji' => 0.00,
                'gaji_pokok' => 0.00,
                'tarif_per_jam' => 17000.00,
                'nama_bank' => 'BRI',
                'no_rekening' => '234567456343',
                'kategori' => 'BTKL',
                'asuransi' => 0.00,
                'tarif' => 17000.00,
                'tarif_lembur' => 0.00,
                'tunjangan' => 0.00,
                'jenis_pegawai' => 'btkl',
                'keterangan' => 'Template pegawai Pengemasan untuk tenant baru',
            ]
        ];

        foreach ($pegawaiTemplates as $template) {
            // Cek apakah template sudah ada
            $existing = DB::table('pegawais')
                ->where('kode_pegawai', $template['kode_pegawai'])
                ->first();
            
            if (!$existing) {
                DB::table('pegawais')->insert($template);
                $this->command->info("  ✓ Created template: {$template['nama']} ({$template['kode_pegawai']})");
            } else {
                // Update existing template
                DB::table('pegawais')
                    ->where('kode_pegawai', $template['kode_pegawai'])
                    ->update($template);
                $this->command->info("  ✓ Updated template: {$template['nama']} ({$template['kode_pegawai']})");
            }
        }

        // Pastikan pegawai asli juga terkunci (jika ada kolom locked)
        if (Schema::hasColumn('pegawais', 'locked')) {
            DB::table('pegawais')
                ->whereIn('kode_pegawai', ['PGW0001', 'PGW0002'])
                ->update(['locked' => 1]);
        }

        $this->command->info('Pegawai templates seeded successfully!');
        $this->command->info('Template pegawais: PGW0001, PGW0002');
    }
}
