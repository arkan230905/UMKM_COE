<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pegawai;
use App\Models\Presensi;
use App\Models\Produk;
use App\Models\Vendor;
use App\Models\BahanBaku;
use App\Models\Bop;
use App\Models\Bom;
use App\Models\Coa;
use App\Models\Pembelian;
use App\Models\Penjualan;
use App\Models\Retur;
use App\Models\Penggajian;

class DashboardController extends Controller
{
    public function index()
    {
        // Master Data
        $totalPegawai     = Pegawai::count();
        $totalPresensi    = Presensi::count();
        $totalProduk      = Produk::count();
        $totalVendor      = Vendor::count();
        $totalBahanBaku   = BahanBaku::count();
        $totalSatuan      = \App\Models\Satuan::count();
        $totalBOP         = Bop::count();
        $totalBOM         = Bom::count();
        $totalCOA         = Coa::count();

        // Transaksi
        $totalPembelian   = Pembelian::count();
        $totalPenjualan   = Penjualan::count();
        $totalRetur       = Retur::sum('jumlah');
        $totalPenggajian  = Penggajian::count();

        return view('dashboard', compact(
            'totalPegawai',
            'totalPresensi',
            'totalProduk',
            'totalVendor',
            'totalBahanBaku',
            'totalSatuan',
            'totalBOP',
            'totalBOM',
            'totalCOA',
            'totalPembelian',
            'totalPenjualan',
            'totalRetur',
            'totalPenggajian'
        ));
    }
}
