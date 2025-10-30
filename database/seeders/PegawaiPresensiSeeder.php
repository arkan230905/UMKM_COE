<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PegawaiPresensiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing data
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('presensis')->truncate();
        DB::table('pegawais')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // Insert test pegawais
        $nomorIndukPegawai = 'EMP' . str_pad(1, 3, '0', STR_PAD_LEFT);
        
        // Insert pegawai and get the nomor_induk_pegawai
        DB::table('pegawais')->insert([
            'nomor_induk_pegawai' => $nomorIndukPegawai,
            'nama' => 'John Doe',
            'email' => 'john.doe@example.com',
            'no_telp' => '081234567890',
            'alamat' => 'Jl. Contoh No. 123',
            'jenis_kelamin' => 'L',
            'jabatan' => 'Staff IT',
            'kategori_tenaga_kerja' => 'BTKL',
            'gaji_pokok' => 5000000,
            'tarif_per_jam' => 50000,
            'jam_kerja_per_minggu' => 40,
            'tunjangan' => 1000000,
            'jenis_pegawai' => 'Tetap',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Insert test presensi for the pegawai
        $today = Carbon::today();
        
        // Add presensi for the last 7 days
        for ($i = 0; $i < 7; $i++) {
            $date = $today->copy()->subDays($i);
            
            DB::table('presensis')->insert([
                'pegawai_id' => $nomorIndukPegawai,
                'tanggal' => $date->format('Y-m-d'),
                'jam_masuk' => '08:00:00',
                'jam_pulang' => '17:00:00',
                'jumlah_jam' => 8,
                'keterangan' => 'Hadir',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        
        $this->command->info('Test data for pegawais and presensi has been seeded!');
    }
}
