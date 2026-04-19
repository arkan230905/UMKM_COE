<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Jabatan;
use Illuminate\Support\Facades\DB;

class JabatanTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding jabatan templates for new tenants...');
        
        // Template jabatan yang akan dikunci untuk semua tenant baru
        $jabatanTemplates = [
            [
                'kode_jabatan' => 'BT001',
                'nama' => 'Perbumbuan',
                'kategori' => 'btkl',
                'asuransi' => 80000.00,
                'deskripsi' => 'Jabatan Perbumbuan untuk BTKL',
                'tarif' => 18000.00,
                'gaji_pokok' => 120000.00,
                'tarif_per_jam' => 18000.00,
                'tunjangan' => 0.00,
                'tunjangan_transport' => 120000.00,
                'tunjangan_konsumsi' => 375000.00,
                'gaji' => 120000.00,
                'keterangan' => 'Template jabatan perbumbuan untuk tenant baru',
                'locked' => 1,
            ],
            [
                'kode_jabatan' => 'BT002',
                'nama' => 'Pengemasan',
                'kategori' => 'btkl',
                'asuransi' => 0.00,
                'deskripsi' => 'Jabatan Pengemasan untuk BTKL',
                'tarif' => 17000.00,
                'gaji_pokok' => 100000.00,
                'tarif_per_jam' => 17000.00,
                'tunjangan' => 0.00,
                'tunjangan_transport' => 100000.00,
                'tunjangan_konsumsi' => 375000.00,
                'gaji' => 100000.00,
                'keterangan' => 'Template jabatan pengemasan untuk tenant baru',
                'locked' => 1,
            ]
        ];

        foreach ($jabatanTemplates as $template) {
            // Cek apakah template sudah ada
            $existing = DB::table('jabatans')
                ->where('kode_jabatan', $template['kode_jabatan'])
                ->first();
            
            if (!$existing) {
                DB::table('jabatans')->insert($template);
                $this->command->info("  ✓ Created template: {$template['nama']} ({$template['kode_jabatan']})");
            } else {
                // Update existing template
                DB::table('jabatans')
                    ->where('kode_jabatan', $template['kode_jabatan'])
                    ->update($template);
                $this->command->info("  ✓ Updated template: {$template['nama']} ({$template['kode_jabatan']})");
            }
        }

        // Pastikan jabatan asli juga terkunci
        DB::table('jabatans')
            ->whereIn('kode_jabatan', ['BT001', 'BT002'])
            ->update(['locked' => 1]);

        $this->command->info('Jabatan templates seeded successfully!');
        $this->command->info('Locked jabatans: BT001, BT002');
    }
}
