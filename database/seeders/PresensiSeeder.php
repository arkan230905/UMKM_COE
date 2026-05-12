<?php

namespace Database\Seeders;

use App\Models\Pegawai;
use App\Models\Presensi;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PresensiSeeder extends Seeder
{
    public function run()
    {
        $pegawais = Pegawai::take(5)->get();
        
        if ($pegawais->isEmpty()) {
            $this->command->info('Tidak ada data pegawai. Silakan jalankan PegawaiSeeder terlebih dahulu.');
            return;
        }
        
        $statuses = ['Hadir', 'Absen', 'Izin', 'Sakit'];
        $today = now();
        
        foreach ($pegawais as $pegawai) {
            // Buat data presensi untuk 30 hari terakhir
            for ($i = 0; $i < 30; $i++) {
                $date = $today->copy()->subDays($i);
                $status = $statuses[array_rand($statuses)];
                
                $jamMasuk = null;
                $jamKeluar = null;
                $jumlahJam = 0; // Default to 0 for non-attendance statuses
                
                if ($status === 'Hadir') {
                    $jamMasuk = $date->copy()->setTime(rand(7, 9), rand(0, 59), 0);
                    $jamKeluar = $jamMasuk->copy()->addHours(rand(7, 9))->addMinutes(rand(0, 59));
                    $jumlahJam = $jamKeluar->diffInHours($jamMasuk, true);
                }
                
                Presensi::create([
                    'pegawai_id' => $pegawai->id, // Using id instead of nomor_induk_pegawai
                    'tgl_presensi' => $date->format('Y-m-d'),
                    'jam_masuk' => $jamMasuk ? $jamMasuk->format('H:i:s') : '08:00:00',
                    'jam_keluar' => $jamKeluar ? $jamKeluar->format('H:i:s') : null,
                    'status' => $status,
                    'jumlah_jam' => $jumlahJam,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
        
        $this->command->info('Data presensi berhasil ditambahkan!');
    }
}