<?php

namespace App\Services;

use App\Models\Presensi;
use App\Models\Penggajian;
use App\Models\Pegawai;
use App\Models\KalenderKerja;
use App\Models\RekapPresensiBulanan;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PenggajianService
{
    /**
     * Generate penggajian bulanan untuk semua pegawai
     */
    public function generatePenggajianBulanan($bulan, $tahun, $tanggalPenggajian = null)
    {
        if (!$tanggalPenggajian) {
            $tanggalPenggajian = Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth();
        }

        // Get all active pegawai
        $pegawaiList = Pegawai::where('status', 'aktif')->get();
        $results = [];
        $failures = [];

        foreach ($pegawaiList as $pegawai) {
            DB::beginTransaction();
            try {
                // Check if payroll already exists for this periode
                $existingPayroll = Penggajian::where('pegawai_id', $pegawai->id)
                    ->where('periode_bulan', $bulan)
                    ->where('periode_tahun', $tahun)
                    ->first();

                if ($existingPayroll) {
                    // Update existing payroll
                    $results[] = $this->updatePenggajian($pegawai, $bulan, $tahun, $tanggalPenggajian);
                } else {
                    // Create new payroll
                    $results[] = $this->createPenggajian($pegawai, $bulan, $tahun, $tanggalPenggajian);
                }
                
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                $failures[] = [
                    'pegawai_id' => $pegawai->id,
                    'nama' => $pegawai->nama,
                    'error' => $e->getMessage()
                ];
            }
        }

        $message = 'Penggajian bulanan selesai. Berhasil: ' . count($results) . ' pegawai.';
        if (count($failures) > 0) {
            $message .= ' Gagal: ' . count($failures) . ' pegawai. Silakan cek laporan kegagalan.';
        }

        return [
            'success' => count($results) > 0,
            'message' => $message,
            'data' => $results,
            'failures' => $failures
        ];
    }

    /**
     * Create penggajian baru untuk pegawai
     */
    private function createPenggajian($pegawai, $bulan, $tahun, $tanggalPenggajian)
    {
        // Generate rekap presensi bulanan
        $rekap = RekapPresensiBulanan::generateRekap($pegawai->id, $bulan, $tahun);

        // Get tarif per jam dari jabatan
        $tarifPerJam = $pegawai->jabatan ? $pegawai->jabatan->tarif_btkl : 0;

        // Calculate gaji pokok based on actual hours
        $totalJam = $rekap->total_jam_bulanan;
        $gajiPokok = $totalJam * $tarifPerJam;

        // Get tunjangan (default 0, bisa di-customize)
        $tunjangan = $this->getTunjangan($pegawai);
        $bonus = 0;
        $potongan = 0;

        // Calculate total gaji
        $totalGaji = $gajiPokok + $tunjangan + $bonus - $potongan;

        // Create penggajian
        $penggajian = Penggajian::create([
            'pegawai_id' => $pegawai->id,
            'periode_bulan' => $bulan,
            'periode_tahun' => $tahun,
            'total_hari_hadir' => $rekap->total_hari_hadir,
            'total_alpha' => $rekap->total_alpha,
            'total_jam' => $totalJam,
            'tanggal_penggajian' => $tanggalPenggajian,
            'coa_kasbank' => '101', // Default COA Kas
            'gaji_pokok' => $gajiPokok,
            'tarif_per_jam' => $tarifPerJam,
            'tunjangan' => $tunjangan,
            'tunjangan_jabatan' => 0,
            'tunjangan_transport' => 0,
            'tunjangan_konsumsi' => 0,
            'total_tunjangan' => $tunjangan,
            'asuransi' => 0,
            'bonus' => $bonus,
            'potongan' => $potongan,
            'total_jam_kerja' => $totalJam,
            'total_gaji' => $totalGaji,
            'status_pembayaran' => 'belum_lunas',
            'metode_pembayaran' => 'transfer_bank',
        ]);

        return $penggajian;
    }

    /**
     * Update penggajian yang sudah ada
     */
    private function updatePenggajian($pegawai, $bulan, $tahun, $tanggalPenggajian)
    {
        $penggajian = Penggajian::where('pegawai_id', $pegawai->id)
            ->where('periode_bulan', $bulan)
            ->where('periode_tahun', $tahun)
            ->first();

        // Generate rekap presensi bulanan
        $rekap = RekapPresensiBulanan::generateRekap($pegawai->id, $bulan, $tahun);

        // Get tarif per jam dari jabatan
        $tarifPerJam = $pegawai->jabatan ? $pegawai->jabatan->tarif_btkl : 0;

        // Calculate gaji pokok based on actual hours
        $totalJam = $rekap->total_jam_bulanan;
        $gajiPokok = $totalJam * $tarifPerJam;

        // Get tunjangan
        $tunjangan = $this->getTunjangan($pegawai);
        $bonus = $penggajian->bonus ?? 0;
        $potongan = $penggajian->potongan ?? 0;

        // Calculate total gaji
        $totalGaji = $gajiPokok + $tunjangan + $bonus - $potongan;

        // Update penggajian
        $penggajian->update([
            'total_hari_hadir' => $rekap->total_hari_hadir,
            'total_alpha' => $rekap->total_alpha,
            'total_jam' => $totalJam,
            'tanggal_penggajian' => $tanggalPenggajian,
            'gaji_pokok' => $gajiPokok,
            'tarif_per_jam' => $tarifPerJam,
            'tunjangan' => $tunjangan,
            'total_tunjangan' => $tunjangan,
            'total_jam_kerja' => $totalJam,
            'total_gaji' => $totalGaji,
        ]);

        return $penggajian;
    }

    /**
     * Get tunjangan untuk pegawai (bisa di-customize sesuai kebijakan)
     */
    private function getTunjangan($pegawai)
    {
        // Default: tidak ada tunjangan
        // Bisa di-customize berdasarkan jabatan, departemen, dll
        return 0;
    }

    /**
     * Hitung gaji berbasis produk (NEW)
     * 
     * @param int $pegawaiId
     * @param int $produk1_5 Jumlah produk hari 1-5
     * @param int $produk6_10 Jumlah produk hari 6-10
     * @param int $produk11_20 Jumlah produk hari 11-20
     * @param int $produk21_30 Jumlah produk hari 21-30
     * @return array Detail perhitungan gaji
     */
    public function hitungGajiProduk($pegawaiId, $produk1_5, $produk6_10, $produk11_20, $produk21_30)
    {
        // 1. Ambil data pegawai + kualifikasi
        $pegawai = Pegawai::find($pegawaiId);
        if (!$pegawai) {
            throw new \Exception('Pegawai tidak ditemukan');
        }

        $jabatan = $pegawai->jabatanRelasi;
        if (!$jabatan) {
            throw new \Exception('Pegawai tidak memiliki kualifikasi/jabatan');
        }

        // 2. Ambil tarif_produk dari kualifikasi
        $tarifProduk = $jabatan->tarif_produk ?? 0;
        if ($tarifProduk <= 0) {
            throw new \Exception('Kualifikasi tidak memiliki tarif/produk yang valid');
        }

        // 3. Hitung total produk
        $totalProduk = $produk1_5 + $produk6_10 + $produk11_20 + $produk21_30;

        // 4. Hitung gaji bruto (total_produk × tarif_produk)
        $gajiBruto = $totalProduk * $tarifProduk;

        // 5. Ambil komponen tunjangan dari kualifikasi
        $tunjanganJabatan = $jabatan->tunjangan ?? 0;
        $tunjanganTransport = $jabatan->tunjangan_transport ?? 0;
        $tunjanganKonsumsi = $jabatan->tunjangan_konsumsi ?? 0;
        $totalTunjangan = $tunjanganJabatan + $tunjanganTransport + $tunjanganKonsumsi;

        // 6. Ambil asuransi
        $asuransi = $jabatan->asuransi ?? 0;

        // 7. Hitung total gaji (gaji_bruto + tunjangan - asuransi)
        $totalGaji = $gajiBruto + $totalTunjangan - $asuransi;

        // 8. Return detail perhitungan
        return [
            'pegawai_id' => $pegawaiId,
            'nama_pegawai' => $pegawai->nama,
            'nama_kualifikasi' => $jabatan->nama,
            'tarif_produk' => $tarifProduk,
            'produk_hari_1_5' => $produk1_5,
            'produk_hari_6_10' => $produk6_10,
            'produk_hari_11_20' => $produk11_20,
            'produk_hari_21_30' => $produk21_30,
            'total_produk' => $totalProduk,
            'gaji_bruto' => $gajiBruto,
            'tunjangan_jabatan' => $tunjanganJabatan,
            'tunjangan_transport' => $tunjanganTransport,
            'tunjangan_konsumsi' => $tunjanganKonsumsi,
            'total_tunjangan' => $totalTunjangan,
            'asuransi' => $asuransi,
            'total_gaji' => $totalGaji,
        ];
    }

    /**
     * Simpan penggajian berbasis produk (NEW)
     */
    public function savePenggajianProduk($pegawaiId, $tanggalPenggajian, $produk1_5, $produk6_10, $produk11_20, $produk21_30, $metodePayment = 'transfer_bank')
    {
        // Hitung gaji terlebih dahulu
        $detail = $this->hitungGajiProduk($pegawaiId, $produk1_5, $produk6_10, $produk11_20, $produk21_30);

        // Ambil pegawai
        $pegawai = Pegawai::find($pegawaiId);

        // Buat record penggajian
        $penggajian = Penggajian::create([
            'pegawai_id' => $pegawaiId,
            'periode_bulan' => Carbon::parse($tanggalPenggajian)->month,
            'periode_tahun' => Carbon::parse($tanggalPenggajian)->year,
            'tanggal_penggajian' => $tanggalPenggajian,
            'coa_kasbank' => '101', // Default COA Kas
            'gaji_pokok' => $detail['gaji_bruto'],
            'tarif_per_jam' => 0, // Tidak digunakan untuk sistem produk
            'tunjangan' => $detail['total_tunjangan'],
            'tunjangan_jabatan' => $detail['tunjangan_jabatan'],
            'tunjangan_transport' => $detail['tunjangan_transport'],
            'tunjangan_konsumsi' => $detail['tunjangan_konsumsi'],
            'total_tunjangan' => $detail['total_tunjangan'],
            'asuransi' => $detail['asuransi'],
            'bonus' => 0,
            'potongan' => 0,
            'total_jam_kerja' => 0, // Tidak digunakan untuk sistem produk
            'total_gaji' => $detail['total_gaji'],
            'status_pembayaran' => 'belum_lunas',
            'metode_pembayaran' => $metodePayment,
            // NEW: Produk fields
            'produk_hari_1_5' => $produk1_5,
            'produk_hari_6_10' => $produk6_10,
            'produk_hari_11_20' => $produk11_20,
            'produk_hari_21_30' => $produk21_30,
            'total_produk_bulan' => $detail['total_produk'],
            'tarif_produk' => $detail['tarif_produk'],
        ]);

        return $penggajian;
    }

    /**
     * Get rekap presensi untuk dashboard pegawai
     */
    public function getRekapPegawaiCurrentMonth($pegawaiId)
    {
        $now = Carbon::now();
        $bulan = $now->month;
        $tahun = $now->year;

        $rekap = RekapPresensiBulanan::where('pegawai_id', $pegawaiId)
            ->where('periode_bulan', $bulan)
            ->where('periode_tahun', $tahun)
            ->first();

        if (!$rekap) {
            // Generate if not exists
            $rekap = RekapPresensiBulanan::generateRekap($pegawaiId, $bulan, $tahun);
        }

        return $rekap;
    }

    /**
     * Get estimasi gaji untuk pegawai bulan ini
     */
    public function getEstimasiGajiCurrentMonth($pegawaiId)
    {
        $rekap = $this->getRekapPegawaiCurrentMonth($pegawaiId);
        $pegawai = Pegawai::find($pegawaiId);
        $tarifPerJam = $pegawai && $pegawai->jabatan ? $pegawai->jabatan->tarif_btkl : 0;

        return $rekap->total_jam_bulanan * $tarifPerJam;
    }

    /**
     * Get riwayat penggajian dengan filter
     */
    public function getRiwayatPenggajian($filters = [])
    {
        $query = Penggajian::with('pegawai')
            ->orderBy('tanggal_penggajian', 'desc')
            ->orderBy('id', 'desc');

        // Filter by pegawai
        if (isset($filters['pegawai_id'])) {
            $query->where('pegawai_id', $filters['pegawai_id']);
        }

        // Filter by periode
        if (isset($filters['periode_bulan'])) {
            $query->where('periode_bulan', $filters['periode_bulan']);
        }

        if (isset($filters['periode_tahun'])) {
            $query->where('periode_tahun', $filters['periode_tahun']);
        }

        // Filter by status pembayaran
        if (isset($filters['status_pembayaran'])) {
            $query->where('status_pembayaran', $filters['status_pembayaran']);
        }

        // Filter by date range
        if (isset($filters['tanggal_dari'])) {
            $query->whereDate('tanggal_penggajian', '>=', $filters['tanggal_dari']);
        }

        if (isset($filters['tanggal_sampai'])) {
            $query->whereDate('tanggal_penggajian', '<=', $filters['tanggal_sampai']);
        }

        return $query;
    }

    /**
     * Get detail penggajian dengan breakdown
     */
    public function getDetailPenggajian($penggajianId)
    {
        $penggajian = Penggajian::with('pegawai')->find($penggajianId);

        if (!$penggajian) {
            return null;
        }

        // Get presensi detail untuk periode ini
        $presensiDetail = Presensi::where('pegawai_id', $penggajian->pegawai_id)
            ->where('periode_bulan', $penggajian->periode_bulan)
            ->where('periode_tahun', $penggajian->periode_tahun)
            ->orderBy('tgl_presensi')
            ->get();

        return [
            'penggajian' => $penggajian,
            'presensi_detail' => $presensiDetail,
            'breakdown' => [
                'gaji_pokok' => $penggajian->gaji_pokok,
                'tunjangan' => $penggajian->total_tunjangan,
                'bonus' => $penggajian->bonus,
                'potongan' => $penggajian->potongan,
                'total' => $penggajian->total_gaji,
            ]
        ];
    }

    /**
     * Mark penggajian as paid
     */
    public function markAsPaid($penggajianId, $tanggalDibayar = null, $metodePembayaran = null)
    {
        $penggajian = Penggajian::find($penggajianId);

        if (!$penggajian) {
            return [
                'success' => false,
                'message' => 'Penggajian tidak ditemukan'
            ];
        }

        $penggajian->update([
            'status_pembayaran' => 'lunas',
            'tanggal_dibayar' => $tanggalDibayar ?? Carbon::now(),
            'metode_pembayaran' => $metodePembayaran ?? $penggajian->metode_pembayaran,
        ]);

        return [
            'success' => true,
            'message' => 'Penggajian berhasil ditandai sebagai lunas',
            'data' => $penggajian
        ];
    }

    /**
     * Get summary penggajian untuk periode tertentu
     */
    public function getSummaryPeriode($bulan, $tahun)
    {
        $penggajianList = Penggajian::where('periode_bulan', $bulan)
            ->where('periode_tahun', $tahun)
            ->get();

        return [
            'total_pegawai' => $penggajianList->count(),
            'total_gaji' => $penggajianList->sum('total_gaji'),
            'total_jam' => $penggajianList->sum('total_jam'),
            'rata_rata_gaji' => $penggajianList->count() > 0 ? $penggajianList->sum('total_gaji') / $penggajianList->count() : 0,
            'status_pembayaran' => [
                'lunas' => $penggajianList->where('status_pembayaran', 'lunas')->count(),
                'belum_lunas' => $penggajianList->where('status_pembayaran', 'belum_lunas')->count(),
            ]
        ];
    }
}
