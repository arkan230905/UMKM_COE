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

        DB::beginTransaction();
        try {
            // Get all active pegawai
            $pegawaiList = Pegawai::where('status', 'aktif')->get();
            $results = [];

            foreach ($pegawaiList as $pegawai) {
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
            }

            DB::commit();
            return [
                'success' => true,
                'message' => 'Penggajian bulanan berhasil di-generate untuk ' . count($results) . ' pegawai',
                'data' => $results
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'data' => []
            ];
        }
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
