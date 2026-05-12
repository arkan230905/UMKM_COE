<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Penjualan;
use App\Models\ReturPenjualan;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LaporanPenjualanController extends Controller
{
    public function index(Request $request)
    {
        // Set default date range (current month)
        $tanggalMulai = $request->get('tanggal_mulai', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $tanggalSelesai = $request->get('tanggal_selesai', Carbon::now()->format('Y-m-d'));
        $periode = $request->get('periode', 'bulanan');
        $metodePembayaran = $request->get('metode_pembayaran');
        $statusTransaksi = $request->get('status_transaksi');

        // Build query for penjualan - sama seperti di PenjualanController
        $query = Penjualan::with(['produk', 'details.produk', 'returPenjualans.detailReturPenjualans'])
            ->whereBetween('tanggal', [$tanggalMulai, $tanggalSelesai]);

        if ($metodePembayaran) {
            $query->where('payment_method', $metodePembayaran);
        }

        $penjualans = $query->orderBy('tanggal', 'desc')->paginate(5);

        // Calculate summary data
        $summaryData = $this->calculateSummary($tanggalMulai, $tanggalSelesai, $metodePembayaran, $statusTransaksi);

        // Get retur data
        $returData = $this->getReturData($tanggalMulai, $tanggalSelesai);

        return view('laporan.penjualan', compact(
            'penjualans',
            'summaryData',
            'returData',
            'tanggalMulai',
            'tanggalSelesai',
            'periode',
            'metodePembayaran',
            'statusTransaksi'
        ));
    }
    private function calculateSummary($tanggalMulai, $tanggalSelesai, $metodePembayaran = null, $statusTransaksi = null)
    {
        $query = Penjualan::with(['details.produk', 'produk'])
            ->whereBetween('tanggal', [$tanggalMulai, $tanggalSelesai]);

        if ($metodePembayaran) {
            $query->where('payment_method', $metodePembayaran);
        }

        $penjualans = $query->get();

        $totalPenjualanProduk = 0;
        $totalOngkir = 0;
        $totalPPN = 0;
        $totalDiskon = 0;
        $totalTransaksi = $penjualans->count();

        foreach ($penjualans as $penjualan) {
            // Calculate product subtotal berdasarkan details atau header
            $detailCount = $penjualan->details->count();
            
            if ($detailCount > 1) {
                // Multiple details
                foreach ($penjualan->details as $detail) {
                    $subtotal = $detail->jumlah * $detail->harga_satuan;
                    $totalPenjualanProduk += $subtotal;
                    $totalDiskon += $detail->diskon_nominal ?? 0;
                }
            } elseif ($detailCount === 1) {
                // Single detail
                $detail = $penjualan->details[0];
                $subtotal = $detail->jumlah * $detail->harga_satuan;
                $totalPenjualanProduk += $subtotal;
                $totalDiskon += $detail->diskon_nominal ?? 0;
            } else {
                // Header level data
                $hdrHarga = $penjualan->harga_satuan;
                if (is_null($hdrHarga) && ($penjualan->jumlah ?? 0) > 0) {
                    $hdrHarga = ((float)$penjualan->total + (float)($penjualan->diskon_nominal ?? 0)) / (float)$penjualan->jumlah;
                }
                $subtotal = ($penjualan->jumlah ?? 0) * ($hdrHarga ?? 0);
                $totalPenjualanProduk += $subtotal;
                $totalDiskon += $penjualan->diskon_nominal ?? 0;
            }

            $totalOngkir += $penjualan->biaya_ongkir ?? 0;
        }

        // Calculate PPN (11% of product subtotal + ongkir)
        $totalPPN = ($totalPenjualanProduk + $totalOngkir) * 0.11;

        $totalPendapatanKotor = $totalPenjualanProduk + $totalOngkir + $totalPPN;
        $totalPendapatanBersih = $totalPendapatanKotor - $totalDiskon;

        return [
            'total_penjualan_produk' => $totalPenjualanProduk,
            'total_ongkir' => $totalOngkir,
            'total_ppn' => $totalPPN,
            'total_diskon' => $totalDiskon,
            'total_transaksi' => $totalTransaksi,
            'total_pendapatan_kotor' => $totalPendapatanKotor,
            'total_pendapatan_bersih' => $totalPendapatanBersih,
        ];
    }
    private function getReturData($tanggalMulai, $tanggalSelesai)
    {
        try {
            $returQuery = ReturPenjualan::with(['penjualan', 'detailReturPenjualans.produk'])
                ->whereBetween('tanggal', [$tanggalMulai, $tanggalSelesai]);
            $returs = $returQuery->get();

            $totalRetur = $returs->count();
            $totalNilaiRetur = $returs->sum('total_retur');
            $totalRefund = $returs->where('jenis_retur', 'refund')->count();
            $totalTukarBarang = $returs->where('jenis_retur', 'tukar_barang')->count();

            return [
                'total_retur' => $totalRetur,
                'total_nilai_retur' => $totalNilaiRetur,
                'total_refund' => $totalRefund,
                'total_tukar_barang' => $totalTukarBarang,
                'retur_list' => $returs, // Tambahkan data retur untuk ditampilkan di tabel
            ];
        } catch (\Exception $e) {
            // Return default data if model doesn't exist
            return [
                'total_retur' => 0,
                'total_nilai_retur' => 0,
                'total_refund' => 0,
                'total_tukar_barang' => 0,
                'retur_list' => collect([]), // Empty collection
            ];
        }
    }

    public function exportPdf(Request $request)
    {
        // TODO: Implement PDF export functionality
        return response()->json(['message' => 'Export PDF functionality will be implemented']);
    }
}