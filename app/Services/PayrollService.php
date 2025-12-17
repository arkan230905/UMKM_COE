<?php

namespace App\Services;

use App\Models\Pegawai;
use App\Models\Presensi;
use App\Models\Penggajian;
use App\Models\Jabatan;
use Carbon\Carbon;

class PayrollService
{
    /**
     * Hitung total tunjangan untuk pegawai berdasarkan jabatannya
     */
    public function hitungTotalTunjangan(Pegawai $pegawai): float
    {
        if (!$pegawai->jabatan_id) {
            return 0;
        }

        $jabatan = Jabatan::find($pegawai->jabatan_id);
        if (!$jabatan) {
            return 0;
        }

        // Hitung dari tunjangan detail di tabel klasifikasi_tunjangan
        $totalTunjangan = $jabatan->tunjangans()
            ->where('is_active', true)
            ->sum('nilai_tunjangan');

        // Tambahkan tunjangan legacy jika ada
        if ($jabatan->tunjangan > 0) {
            $totalTunjangan += (float)$jabatan->tunjangan;
        }

        return (float)$totalTunjangan;
    }

    /**
     * Hitung gaji pegawai berdasarkan jenis pegawai
     */
    public function hitungGajiPegawai(
        Pegawai $pegawai,
        float $bonus = 0,
        float $potongan = 0,
        ?int $month = null,
        ?int $year = null
    ): array {
        $month = $month ?? Carbon::now()->month;
        $year = $year ?? Carbon::now()->year;

        $jenis = strtolower($pegawai->jenis_pegawai ?? 'btktl');
        $totalTunjangan = $this->hitungTotalTunjangan($pegawai);
        $asuransi = (float)($pegawai->asuransi ?? 0);

        if ($jenis === 'btkl') {
            return $this->hitungGajiBTKL($pegawai, $totalTunjangan, $asuransi, $bonus, $potongan, $month, $year);
        } else {
            return $this->hitungGajiBTKTL($pegawai, $totalTunjangan, $asuransi, $bonus, $potongan);
        }
    }

    /**
     * Hitung gaji BTKL (Biaya Tenaga Kerja Langsung)
     */
    private function hitungGajiBTKL(
        Pegawai $pegawai,
        float $totalTunjangan,
        float $asuransi,
        float $bonus,
        float $potongan,
        int $month,
        int $year
    ): array {
        $tarifPerJam = (float)($pegawai->tarif_per_jam ?? 0);

        // Hitung total jam kerja dari presensi
        $totalJamKerja = Presensi::where('pegawai_id', $pegawai->id)
            ->whereMonth('tgl_presensi', $month)
            ->whereYear('tgl_presensi', $year)
            ->sum('jumlah_jam');

        $gajiDasar = $tarifPerJam * (float)$totalJamKerja;
        // Formula: Gaji Dasar + Tunjangan + Asuransi + Bonus - Potongan - Potongan Tambahan
        $totalGaji = $gajiDasar + $totalTunjangan + $asuransi + $bonus - $potongan;

        return [
            'jenis_pegawai' => 'btkl',
            'gaji_dasar' => $gajiDasar,
            'tarif_per_jam' => $tarifPerJam,
            'total_jam_kerja' => (float)$totalJamKerja,
            'total_tunjangan' => $totalTunjangan,
            'asuransi' => $asuransi,
            'bonus' => $bonus,
            'potongan' => $potongan,
            'total_gaji' => max(0, $totalGaji),
        ];
    }

    /**
     * Hitung gaji BTKTL (Biaya Tenaga Kerja Tidak Langsung)
     */
    private function hitungGajiBTKTL(
        Pegawai $pegawai,
        float $totalTunjangan,
        float $asuransi,
        float $bonus,
        float $potongan
    ): array {
        $gajiPokok = (float)($pegawai->gaji_pokok ?? 0);
        // Formula: Gaji Pokok + Tunjangan + Asuransi + Bonus - Potongan - Potongan Tambahan
        $totalGaji = $gajiPokok + $totalTunjangan + $asuransi + $bonus - $potongan;

        return [
            'jenis_pegawai' => 'btktl',
            'gaji_pokok' => $gajiPokok,
            'total_tunjangan' => $totalTunjangan,
            'asuransi' => $asuransi,
            'bonus' => $bonus,
            'potongan' => $potongan,
            'total_gaji' => max(0, $totalGaji),
        ];
    }

    /**
     * Generate penggajian untuk semua pegawai dalam bulan tertentu
     */
    public function generatePenggajian(int $month, int $year, string $coaKasBank = '101'): array
    {
        $pegawais = Pegawai::where('status_aktif', true)->get();
        $results = [];
        $errors = [];

        foreach ($pegawais as $pegawai) {
            try {
                $gajiData = $this->hitungGajiPegawai($pegawai, 0, 0, $month, $year);

                $penggajian = Penggajian::updateOrCreate(
                    [
                        'pegawai_id' => $pegawai->id,
                        'tanggal_penggajian' => Carbon::createFromDate($year, $month, 1)->endOfMonth(),
                    ],
                    [
                        'coa_kasbank' => $coaKasBank,
                        'gaji_pokok' => $gajiData['gaji_pokok'] ?? 0,
                        'tarif_per_jam' => $gajiData['tarif_per_jam'] ?? 0,
                        'tunjangan' => $gajiData['total_tunjangan'],
                        'asuransi' => $gajiData['asuransi'],
                        'bonus' => 0,
                        'potongan' => 0,
                        'total_jam_kerja' => $gajiData['total_jam_kerja'] ?? 0,
                        'total_gaji' => $gajiData['total_gaji'],
                    ]
                );

                $results[] = [
                    'pegawai_id' => $pegawai->id,
                    'nama' => $pegawai->nama,
                    'total_gaji' => $gajiData['total_gaji'],
                    'status' => 'success',
                ];
            } catch (\Exception $e) {
                $errors[] = [
                    'pegawai_id' => $pegawai->id,
                    'nama' => $pegawai->nama,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return [
            'success' => $results,
            'errors' => $errors,
            'total_gaji' => collect($results)->sum('total_gaji'),
        ];
    }

    /**
     * Dapatkan detail tunjangan untuk pegawai
     */
    public function getTunjanganDetail(Pegawai $pegawai): array
    {
        if (!$pegawai->jabatan_id) {
            return [];
        }

        $jabatan = Jabatan::find($pegawai->jabatan_id);
        if (!$jabatan) {
            return [];
        }

        $tunjangans = $jabatan->tunjangans()
            ->where('is_active', true)
            ->get()
            ->map(function ($t) {
                return [
                    'id' => $t->id,
                    'nama' => $t->nama_tunjangan,
                    'nilai' => (float)$t->nilai_tunjangan,
                ];
            })
            ->toArray();

        // Tambahkan tunjangan legacy jika ada
        if ($jabatan->tunjangan > 0) {
            $tunjangans[] = [
                'id' => 'legacy',
                'nama' => 'Tunjangan (Legacy)',
                'nilai' => (float)$jabatan->tunjangan,
            ];
        }

        return $tunjangans;
    }
}
