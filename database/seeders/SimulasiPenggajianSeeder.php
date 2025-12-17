<?php

namespace Database\Seeders;

use App\Models\Pegawai;
use App\Models\Presensi;
use App\Models\Penggajian;
use App\Models\Coa;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SimulasiPenggajianSeeder extends Seeder
{
    /**
     * Simulasi penggajian lengkap dari master data
     * Mencakup: Pegawai, Presensi, dan Penggajian
     */
    public function run(): void
    {
        // Disable model events untuk performa
        Pegawai::withoutEvents(function () {
            Presensi::withoutEvents(function () {
                Penggajian::withoutEvents(function () {
                    $this->seedMasterData();
                    $this->seedPresensi();
                    $this->seedPenggajian();
                });
            });
        });

        $this->command->info('✓ Simulasi penggajian lengkap berhasil dibuat!');
    }

    /**
     * Seed data master pegawai
     */
    private function seedMasterData(): void
    {
        $this->command->info('Membuat data master pegawai...');

        $pegawais = [
            // BTKL - Biaya Tenaga Kerja Langsung (Produksi)
            [
                'nomor_induk_pegawai' => 'PGW001',
                'kode_pegawai' => 'PGW001',
                'nama' => 'Budi Santoso',
                'email' => 'budi.santoso@umkm.local',
                'no_telp' => '081234567890',
                'alamat' => 'Jl. Merdeka No. 10, Jakarta',
                'jenis_kelamin' => 'L',
                'jabatan' => 'Operator Produksi',
                'jenis_pegawai' => 'btkl',
                'kategori_tenaga_kerja' => 'BTKL',
                'gaji_pokok' => 0,
                'tarif_per_jam' => 50000, // Rp 50.000/jam
                'tunjangan' => 500000,    // Rp 500.000/bulan
                'asuransi' => 200000,     // Rp 200.000/bulan
                'bank' => 'BCA',
                'nomor_rekening' => '1234567890',
                'nama_rekening' => 'BUDI SANTOSO',
                'tanggal_masuk' => '2024-01-01',
                'status_aktif' => true,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'nomor_induk_pegawai' => 'PGW002',
                'kode_pegawai' => 'PGW002',
                'nama' => 'Siti Nurhaliza',
                'email' => 'siti.nurhaliza@umkm.local',
                'no_telp' => '081234567891',
                'alamat' => 'Jl. Sudirman No. 45, Jakarta',
                'jenis_kelamin' => 'P',
                'jabatan' => 'Operator Mesin',
                'jenis_pegawai' => 'btkl',
                'kategori_tenaga_kerja' => 'BTKL',
                'gaji_pokok' => 0,
                'tarif_per_jam' => 45000, // Rp 45.000/jam
                'tunjangan' => 400000,    // Rp 400.000/bulan
                'asuransi' => 150000,     // Rp 150.000/bulan
                'bank' => 'BCA',
                'nomor_rekening' => '1234567891',
                'nama_rekening' => 'SITI NURHALIZA',
                'tanggal_masuk' => '2024-01-15',
                'status_aktif' => true,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'nomor_induk_pegawai' => 'PGW003',
                'kode_pegawai' => 'PGW003',
                'nama' => 'Ahmad Wijaya',
                'email' => 'ahmad.wijaya@umkm.local',
                'no_telp' => '081234567892',
                'alamat' => 'Jl. Gatot Subroto No. 67, Jakarta',
                'jenis_kelamin' => 'L',
                'jabatan' => 'Helper Produksi',
                'jenis_pegawai' => 'btkl',
                'kategori_tenaga_kerja' => 'BTKL',
                'gaji_pokok' => 0,
                'tarif_per_jam' => 35000, // Rp 35.000/jam
                'tunjangan' => 300000,    // Rp 300.000/bulan
                'asuransi' => 100000,     // Rp 100.000/bulan
                'bank' => 'Mandiri',
                'nomor_rekening' => '1234567892',
                'nama_rekening' => 'AHMAD WIJAYA',
                'tanggal_masuk' => '2024-02-01',
                'status_aktif' => true,
                'created_by' => 1,
                'updated_by' => 1,
            ],

            // BTKTL - Biaya Tenaga Kerja Tidak Langsung (Admin/Support)
            [
                'nomor_induk_pegawai' => 'PGW004',
                'kode_pegawai' => 'PGW004',
                'nama' => 'Ani Wijayanti',
                'email' => 'ani.wijayanti@umkm.local',
                'no_telp' => '081234567893',
                'alamat' => 'Jl. Thamrin No. 12, Jakarta',
                'jenis_kelamin' => 'P',
                'jabatan' => 'Staff Admin',
                'jenis_pegawai' => 'btktl',
                'kategori_tenaga_kerja' => 'BTKTL',
                'gaji_pokok' => 5000000,  // Rp 5.000.000/bulan
                'tarif_per_jam' => 0,
                'tunjangan' => 1000000,   // Rp 1.000.000/bulan
                'asuransi' => 300000,     // Rp 300.000/bulan
                'bank' => 'BCA',
                'nomor_rekening' => '1234567893',
                'nama_rekening' => 'ANI WIJAYANTI',
                'tanggal_masuk' => '2024-01-10',
                'status_aktif' => true,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'nomor_induk_pegawai' => 'PGW005',
                'kode_pegawai' => 'PGW005',
                'nama' => 'Rudi Hermawan',
                'email' => 'rudi.hermawan@umkm.local',
                'no_telp' => '081234567894',
                'alamat' => 'Jl. Pahlawan No. 5, Tangerang',
                'jenis_kelamin' => 'L',
                'jabatan' => 'Kepala Gudang',
                'jenis_pegawai' => 'btktl',
                'kategori_tenaga_kerja' => 'BTKTL',
                'gaji_pokok' => 6000000,  // Rp 6.000.000/bulan
                'tarif_per_jam' => 0,
                'tunjangan' => 1500000,   // Rp 1.500.000/bulan
                'asuransi' => 400000,     // Rp 400.000/bulan
                'bank' => 'Mandiri',
                'nomor_rekening' => '1234567894',
                'nama_rekening' => 'RUDI HERMAWAN',
                'tanggal_masuk' => '2024-01-05',
                'status_aktif' => true,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'nomor_induk_pegawai' => 'PGW006',
                'kode_pegawai' => 'PGW006',
                'nama' => 'Eka Putri Lestari',
                'email' => 'eka.putri@umkm.local',
                'no_telp' => '081234567895',
                'alamat' => 'Jl. Melati No. 3, Depok',
                'jenis_kelamin' => 'P',
                'jabatan' => 'Finance Officer',
                'jenis_pegawai' => 'btktl',
                'kategori_tenaga_kerja' => 'BTKTL',
                'gaji_pokok' => 7000000,  // Rp 7.000.000/bulan
                'tarif_per_jam' => 0,
                'tunjangan' => 2000000,   // Rp 2.000.000/bulan
                'asuransi' => 500000,     // Rp 500.000/bulan
                'bank' => 'BCA',
                'nomor_rekening' => '1234567895',
                'nama_rekening' => 'EKA PUTRI LESTARI',
                'tanggal_masuk' => '2023-12-15',
                'status_aktif' => true,
                'created_by' => 1,
                'updated_by' => 1,
            ],
        ];

        foreach ($pegawais as $data) {
            Pegawai::updateOrCreate(
                ['nomor_induk_pegawai' => $data['nomor_induk_pegawai']],
                $data
            );
        }

        $this->command->info('  ✓ ' . count($pegawais) . ' pegawai berhasil dibuat');
    }

    /**
     * Seed data presensi untuk bulan berjalan
     */
    private function seedPresensi(): void
    {
        $this->command->info('Membuat data presensi...');

        $pegawais = Pegawai::where('jenis_pegawai', 'btkl')->get();
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        $daysInMonth = Carbon::now()->daysInMonth;

        $totalPresensi = 0;

        foreach ($pegawais as $pegawai) {
            // Buat presensi untuk setiap hari kerja dalam bulan ini
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $date = Carbon::createFromDate($currentYear, $currentMonth, $day);

                // Skip weekend (Sabtu & Minggu)
                if ($date->isWeekend()) {
                    continue;
                }

                // Random status: 80% Hadir, 10% Sakit, 10% Izin
                $rand = rand(1, 100);
                if ($rand <= 80) {
                    $status = 'Hadir';
                    $jamMasuk = $date->copy()->setTime(8, 0, 0);
                    $jamKeluar = $date->copy()->setTime(17, 0, 0);
                    $jumlahJam = 8; // 8 jam kerja standar
                } elseif ($rand <= 90) {
                    $status = 'Sakit';
                    $jamMasuk = null;
                    $jamKeluar = null;
                    $jumlahJam = 0;
                } else {
                    $status = 'Izin';
                    $jamMasuk = null;
                    $jamKeluar = null;
                    $jumlahJam = 0;
                }

                Presensi::updateOrCreate(
                    [
                        'pegawai_id' => $pegawai->id,
                        'tgl_presensi' => $date->format('Y-m-d'),
                    ],
                    [
                        'jam_masuk' => $jamMasuk ? $jamMasuk->format('H:i:s') : null,
                        'jam_keluar' => $jamKeluar ? $jamKeluar->format('H:i:s') : null,
                        'status' => $status,
                        'jumlah_jam' => $jumlahJam,
                        'keterangan' => $status === 'Hadir' ? 'Normal' : $status,
                    ]
                );

                $totalPresensi++;
            }
        }

        $this->command->info('  ✓ ' . $totalPresensi . ' record presensi berhasil dibuat');
    }

    /**
     * Seed data penggajian simulasi
     */
    private function seedPenggajian(): void
    {
        $this->command->info('Membuat data penggajian simulasi...');

        $pegawais = Pegawai::all();
        $tanggalPenggajian = Carbon::now()->endOfMonth();

        // Pastikan ada akun kas/bank
        $coaKasBank = Coa::where('kode_akun', '101')->first();
        if (!$coaKasBank) {
            $this->command->warn('  ⚠ Akun kas (101) tidak ditemukan, skip penggajian');
            return;
        }

        $totalPenggajian = 0;

        foreach ($pegawais as $pegawai) {
            // Hitung total jam kerja dari presensi bulan ini
            $totalJamKerja = Presensi::where('pegawai_id', $pegawai->id)
                ->whereMonth('tgl_presensi', $tanggalPenggajian->month)
                ->whereYear('tgl_presensi', $tanggalPenggajian->year)
                ->sum('jumlah_jam');

            $jenis = strtolower($pegawai->jenis_pegawai ?? 'btktl');

            // Hitung gaji berdasarkan jenis pegawai
            if ($jenis === 'btkl') {
                // BTKL = (Tarif × Jam Kerja) + Asuransi + Tunjangan + Bonus - Potongan
                $gajiDasar = ((float)$pegawai->tarif_per_jam * (float)$totalJamKerja);
                $bonus = rand(0, 1) === 1 ? 500000 : 0; // 50% chance bonus
                $potongan = rand(0, 1) === 1 ? 100000 : 0; // 50% chance potongan
                $totalGaji = $gajiDasar + (float)$pegawai->asuransi + (float)$pegawai->tunjangan + $bonus - $potongan;
            } else {
                // BTKTL = Gaji Pokok + Asuransi + Tunjangan + Bonus - Potongan
                $bonus = rand(0, 1) === 1 ? 1000000 : 0; // 50% chance bonus
                $potongan = rand(0, 1) === 1 ? 200000 : 0; // 50% chance potongan
                $totalGaji = (float)$pegawai->gaji_pokok + (float)$pegawai->asuransi + (float)$pegawai->tunjangan + $bonus - $potongan;
            }

            // Buat record penggajian
            $penggajian = Penggajian::updateOrCreate(
                [
                    'pegawai_id' => $pegawai->id,
                    'tanggal_penggajian' => $tanggalPenggajian,
                ],
                [
                    'coa_kasbank' => $coaKasBank->kode_akun,
                    'gaji_pokok' => (float)$pegawai->gaji_pokok,
                    'tarif_per_jam' => (float)$pegawai->tarif_per_jam,
                    'tunjangan' => (float)$pegawai->tunjangan,
                    'asuransi' => (float)$pegawai->asuransi,
                    'bonus' => $bonus ?? 0,
                    'potongan' => $potongan ?? 0,
                    'total_jam_kerja' => $totalJamKerja,
                    'total_gaji' => $totalGaji,
                ]
            );

            $totalPenggajian++;
        }

        $this->command->info('  ✓ ' . $totalPenggajian . ' record penggajian berhasil dibuat');
        $this->command->info('  Tanggal penggajian: ' . $tanggalPenggajian->format('d-m-Y'));
    }
}
