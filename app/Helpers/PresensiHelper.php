<?php

namespace App\Helpers;

use Carbon\Carbon;

class PresensiHelper
{
    /**
     * Hitung durasi kerja dari jam masuk dan jam keluar
     * Hasil dibulatkan ke kelipatan 30 menit (0,5 jam) terdekat
     *
     * @param Carbon $jamMasuk
     * @param Carbon $jamKeluar
     * @return array ['jumlah_menit_kerja' => int, 'jumlah_jam_kerja' => float]
     */
    public static function hitungDurasiKerja(Carbon $jamMasuk, Carbon $jamKeluar): array
    {
        // Hitung selisih dalam menit
        $jumlahMenitKerja = $jamMasuk->diffInMinutes($jamKeluar);

        // Konversi menit ke jam dengan pembulatan ke 0,5 jam terdekat
        $jumlahJamKerja = self::bulatkanKeSetengahJam($jumlahMenitKerja);

        return [
            'jumlah_menit_kerja' => $jumlahMenitKerja,
            'jumlah_jam_kerja' => $jumlahJamKerja,
        ];
    }

    /**
     * Bulatkan menit ke jam dengan kelipatan 0,5 jam terdekat
     *
     * Logika:
     * - 0-14 menit → 0 jam
     * - 15-44 menit → 0,5 jam
     * - 45-74 menit → 1 jam
     * - 75-104 menit → 1,5 jam
     * - dst...
     *
     * @param int $menit
     * @return float
     */
    public static function bulatkanKeSetengahJam(int $menit): float
    {
        // Konversi menit ke jam dalam bentuk desimal
        $jamDesimal = $menit / 60;

        // Bulatkan ke 0,5 jam terdekat
        // Rumus: round(jam * 2) / 2
        $jamBulat = round($jamDesimal * 2) / 2;

        return $jamBulat;
    }

    /**
     * Format jam kerja untuk tampilan (misal: 7, 7.5, 8)
     *
     * @param float $jamKerja
     * @return string
     */
    public static function formatJamKerja(float $jamKerja): string
    {
        // Jika jam adalah bilangan bulat, tampilkan tanpa desimal
        if ($jamKerja == (int)$jamKerja) {
            return (int)$jamKerja . ' jam';
        }

        // Jika ada desimal (0,5), tampilkan dengan satu desimal
        return number_format($jamKerja, 1, ',', '') . ' jam';
    }

    /**
     * Hitung gaji per jam berdasarkan gaji pokok bulanan
     *
     * Asumsi:
     * - 1 bulan = 22 hari kerja
     * - 1 hari kerja = 8 jam
     * - Total jam kerja per bulan = 22 * 8 = 176 jam
     *
     * @param float $gajiPokokBulanan
     * @param int $jumlahHariKerjaBulanan (default: 22)
     * @param int $jamPerHari (default: 8)
     * @return float
     */
    public static function hitungGajiPerJam(
        float $gajiPokokBulanan,
        int $jumlahHariKerjaBulanan = 22,
        int $jamPerHari = 8
    ): float
    {
        $totalJamPerBulan = $jumlahHariKerjaBulanan * $jamPerHari;
        return $gajiPokokBulanan / $totalJamPerBulan;
    }

    /**
     * Hitung total gaji dengan formula:
     * total_gaji = (gaji_per_jam * total_jam_kerja) + tunjangan + bonus - potongan
     *
     * @param float $gajiPerJam
     * @param float $totalJamKerja
     * @param float $tunjangan
     * @param float $bonus
     * @param float $potongan
     * @return float
     */
    public static function hitungTotalGaji(
        float $gajiPerJam,
        float $totalJamKerja,
        float $tunjangan = 0,
        float $bonus = 0,
        float $potongan = 0
    ): float
    {
        return ($gajiPerJam * $totalJamKerja) + $tunjangan + $bonus - $potongan;
    }
}
