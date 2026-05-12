<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Presensi;
use App\Models\Pegawai;

class PresensiDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tanggal = '2026-03-06';

        // Data presensi sesuai dengan data yang diberikan
        $presensiData = [
            [
                'nama' => 'Budi Susanto',
                'jam_masuk' => '08:00',
                'jam_keluar' => '15:00',
                'status' => 'hadir',
                'jumlah_jam' => 7,
                'keterangan' => 'Hadir lengkap',
            ],
            [
                'nama' => 'Ahmad Suryanto',
                'jam_masuk' => '08:00',
                'jam_keluar' => '12:00',
                'status' => 'hadir',
                'jumlah_jam' => 4,
                'keterangan' => 'Hadir lengkap',
            ],
            [
                'nama' => 'Rina Wijaya',
                'jam_masuk' => '08:00',
                'jam_keluar' => '11:00',
                'status' => 'hadir',
                'jumlah_jam' => 3,
                'keterangan' => 'Hadir lengkap',
            ],
            [
                'nama' => 'Dedi Gunawan',
                'jam_masuk' => null,
                'jam_keluar' => null,
                'status' => 'alpha',
                'jumlah_jam' => 0,
                'keterangan' => 'Tidak hadir',
            ],
        ];

        foreach ($presensiData as $data) {
            // Cari pegawai berdasarkan nama
            $pegawai = Pegawai::where('nama', $data['nama'])->first();

            if (!$pegawai) {
                echo "⚠️  Pegawai tidak ditemukan: {$data['nama']}\n";
                continue;
            }

            // Cek apakah presensi sudah ada untuk tanggal ini dan pegawai ini
            $existing = Presensi::where('pegawai_id', $pegawai->id)
                ->whereDate('tgl_presensi', $tanggal)
                ->first();

            if ($existing) {
                echo "⏭️  Presensi sudah ada: {$pegawai->nama} pada {$tanggal}\n";
                continue;
            }

            // Buat presensi baru
            Presensi::create([
                'pegawai_id' => $pegawai->id,
                'tgl_presensi' => $tanggal,
                'jam_masuk' => $data['jam_masuk'],
                'jam_keluar' => $data['jam_keluar'],
                'status' => $data['status'],
                'jumlah_jam' => $data['jumlah_jam'],
                'keterangan' => $data['keterangan'],
                'verifikasi_wajah' => true, // asumsi sudah diverifikasi
            ]);

            echo "✅ Presensi dibuat: {$pegawai->nama} - {$data['status']} ({$tanggal})\n";
        }

        echo "\n✅ Seeding data presensi selesai.\n";
    }
}
