<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Penjualan;
use App\Models\ReturPenjualan;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\PDF;

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
        $produkId = $request->get('produk_id');
        $sortBy = $request->get('sort_by', 'tanggal');
        $sortOrder = $request->get('sort_order', 'desc');

        $produks = \App\Models\Produk::where('user_id', auth()->id())->get();

        // Build query for penjualan - CRITICAL: Filter by logged-in user (owner)
        $query = Penjualan::with(['produk', 'details.produk', 'returPenjualans.detailReturPenjualans'])
            ->where('user_id', auth()->id()) // CRITICAL: Multi-tenant isolation
            ->whereBetween('tanggal', [$tanggalMulai, $tanggalSelesai]);

        if ($metodePembayaran) {
            $query->where('payment_method', $metodePembayaran);
        }

        if ($produkId) {
            $query->where(function($q) use ($produkId) {
                $q->where('produk_id', $produkId)
                  ->orWhereHas('details', function($q2) use ($produkId) {
                      $q2->where('produk_id', $produkId);
                  });
            });
        }

        $allowedSorts = ['nomor_penjualan', 'tanggal', 'payment_method', 'total', 'grand_total'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'tanggal';
        }

        $penjualans = $query->orderBy($sortBy, $sortOrder)->paginate(5);

        // Calculate summary data
        $summaryData = $this->calculateSummary($tanggalMulai, $tanggalSelesai, $metodePembayaran, $statusTransaksi, $produkId, $periode);

        // Get retur data
        $returSortBy = $request->get('sort_by_retur', 'tanggal');
        $returSortOrder = $request->get('sort_order_retur', 'desc');
        $returData = $this->getReturData($tanggalMulai, $tanggalSelesai, $produkId, $returSortBy, $returSortOrder);

        return view('laporan.penjualan', compact(
            'penjualans',
            'summaryData',
            'returData',
            'tanggalMulai',
            'tanggalSelesai',
            'periode',
            'metodePembayaran',
            'statusTransaksi',
            'produks',
            'produkId'
        ));
    }
    private function calculateSummary($tanggalMulai, $tanggalSelesai, $metodePembayaran = null, $statusTransaksi = null, $produkId = null, $periode = 'bulanan')
    {
        $query = Penjualan::with(['details.produk', 'produk'])
            ->where('user_id', auth()->id()) // CRITICAL: Multi-tenant isolation
            ->whereBetween('tanggal', [$tanggalMulai, $tanggalSelesai]);

        if ($metodePembayaran) {
            $query->where('payment_method', $metodePembayaran);
        }

        if ($produkId) {
            $query->where(function($q) use ($produkId) {
                $q->where('produk_id', $produkId)
                  ->orWhereHas('details', function($q2) use ($produkId) {
                      $q2->where('produk_id', $produkId);
                  });
            });
        }

        $penjualans = $query->orderBy('tanggal', 'asc')->get();

        $totalPenjualanProduk = 0;
        $totalOngkir = 0;
        $totalPPN = 0;
        $totalDiskon = 0;
        $totalTransaksi = $penjualans->count();

        $chartGrouped = [];
        $start = \Carbon\Carbon::parse($tanggalMulai);
        $end = \Carbon\Carbon::parse($tanggalSelesai);
        
        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            if ($periode == 'bulanan') {
                $key = $date->format('M Y');
            } elseif ($periode == 'mingguan') {
                $key = 'W' . $date->weekOfYear . ' ' . $date->format('Y');
            } else {
                $key = $date->format('d/m/Y');
            }
            if (!isset($chartGrouped[$key])) {
                $chartGrouped[$key] = ['produk' => 0, 'ongkir' => 0, 'ppn' => 0];
            }
        }

        foreach ($penjualans as $penjualan) {
            $hasMatchingProduct = false;
            $transactionTotalProduk = 0;
            $transactionTotalDiskon = 0;

            // Calculate product subtotal berdasarkan details atau header
            $detailCount = $penjualan->details->count();
            
            if ($detailCount > 0) {
                foreach ($penjualan->details as $detail) {
                    if ($produkId && $detail->produk_id != $produkId) {
                        continue;
                    }
                    $hasMatchingProduct = true;
                    $subtotal = $detail->jumlah * $detail->harga_satuan;
                    $transactionTotalProduk += $subtotal;
                    $transactionTotalDiskon += $detail->diskon_nominal ?? 0;
                }
            } else {
                // Header level data
                if (!$produkId || $penjualan->produk_id == $produkId) {
                    $hasMatchingProduct = true;
                    $hdrHarga = $penjualan->harga_satuan;
                    if (is_null($hdrHarga) && ($penjualan->jumlah ?? 0) > 0) {
                        $hdrHarga = ((float)$penjualan->total + (float)($penjualan->diskon_nominal ?? 0)) / (float)$penjualan->jumlah;
                    }
                    $subtotal = ($penjualan->jumlah ?? 0) * ($hdrHarga ?? 0);
                    $transactionTotalProduk += $subtotal;
                    $transactionTotalDiskon += $penjualan->diskon_nominal ?? 0;
                }
            }

            if ($hasMatchingProduct) {
                $totalPenjualanProduk += $transactionTotalProduk;
                $totalDiskon += $transactionTotalDiskon;
                
                $ongkir = $penjualan->biaya_ongkir ?? 0;
                $totalOngkir += $ongkir;
                
                $ppn = ($transactionTotalProduk + $ongkir) * 0.11;
                $totalPPN += $ppn;

                $date = \Carbon\Carbon::parse($penjualan->tanggal);
                if ($periode == 'bulanan') {
                    $key = $date->format('M Y');
                } elseif ($periode == 'mingguan') {
                    $key = 'W' . $date->weekOfYear . ' ' . $date->format('Y');
                } else {
                    $key = $date->format('d/m/Y');
                }

                if (!isset($chartGrouped[$key])) {
                    $chartGrouped[$key] = ['produk' => 0, 'ongkir' => 0, 'ppn' => 0];
                }
                $chartGrouped[$key]['produk'] += $transactionTotalProduk;
                $chartGrouped[$key]['ongkir'] += $ongkir;
                $chartGrouped[$key]['ppn'] += $ppn;
            }
        }

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
            'chart' => [
                'labels' => array_keys($chartGrouped),
                'produk' => array_column($chartGrouped, 'produk'),
                'ongkir' => array_column($chartGrouped, 'ongkir'),
                'ppn' => array_column($chartGrouped, 'ppn'),
            ]
        ];
    }
    private function getReturData($tanggalMulai, $tanggalSelesai, $produkId = null, $sortBy = 'tanggal', $sortOrder = 'desc')
    {
        try {
            $returQuery = ReturPenjualan::with(['penjualan', 'detailReturPenjualans.produk'])
                ->whereHas('penjualan', function ($query) {
                    $query->where('user_id', auth()->id()); // CRITICAL: Multi-tenant isolation
                })
                ->whereBetween('tanggal', [$tanggalMulai, $tanggalSelesai]);

            if ($produkId) {
                $returQuery->whereHas('detailReturPenjualans', function($q) use ($produkId) {
                    $q->where('produk_id', $produkId);
                });
            }

            $allowedSorts = ['nomor_retur', 'tanggal', 'jenis_retur', 'status', 'total_retur'];
            if (!in_array($sortBy, $allowedSorts)) {
                $sortBy = 'tanggal';
            }

            $returs = $returQuery->orderBy($sortBy, $sortOrder)->get();

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
        // Get filter parameters
        $tanggalMulai = $request->get('tanggal_mulai', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $tanggalSelesai = $request->get('tanggal_selesai', Carbon::now()->format('Y-m-d'));
        $metodePembayaran = $request->get('metode_pembayaran');
        $produkId = $request->get('produk_id');
        $periode = $request->get('periode', 'bulanan');

        // Build query for penjualan
        $query = Penjualan::with(['produk', 'details.produk', 'returPenjualans.detailReturPenjualans'])
            ->where('user_id', auth()->id())
            ->whereBetween('tanggal', [$tanggalMulai, $tanggalSelesai]);

        if ($metodePembayaran) {
            $query->where('payment_method', $metodePembayaran);
        }

        if ($produkId) {
            $query->where(function($q) use ($produkId) {
                $q->where('produk_id', $produkId)
                  ->orWhereHas('details', function($q2) use ($produkId) {
                      $q2->where('produk_id', $produkId);
                  });
            });
        }

        $penjualans = $query->orderBy('tanggal', 'desc')->get();

        // Calculate summary data
        $summaryData = $this->calculateSummary($tanggalMulai, $tanggalSelesai, $metodePembayaran, null, $produkId, $periode);

        // Get retur data
        $returData = $this->getReturData($tanggalMulai, $tanggalSelesai, $produkId);

        // Create PDF
        $pdf = \PDF::loadView('laporan.penjualan-pdf', compact(
            'penjualans',
            'summaryData',
            'returData',
            'tanggalMulai',
            'tanggalSelesai',
            'metodePembayaran',
            'produkId'
        ));

        $pdf->setPaper('A4', 'landscape');

        // Return PDF download
        return $pdf->download('Laporan-Penjualan-' . date('Y-m-d-His') . '.pdf');
    }
}