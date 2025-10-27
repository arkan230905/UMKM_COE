<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pegawai;
use App\Models\Presensi;
use App\Models\Produk;
use App\Models\Vendor;
use App\Models\BahanBaku;
use App\Models\Bom;
use App\Models\Aset;
use App\Models\Pembelian;
use App\Models\Penjualan;
use App\Models\Retur;

class DashboardController extends Controller
{
    public function index()
    {
        $totalPegawai     = Pegawai::count();
        $totalPresensi    = Presensi::count();
        $totalProduk      = Produk::count();
        $totalVendor      = Vendor::count();
        $totalBahanBaku   = BahanBaku::count();
        $totalBOM         = Bom::count();
        $totalAset        = Aset::count(); // Tambahan Aset

        $totalPembelian   = Pembelian::count();
        $totalPenjualan   = Penjualan::count();
        $totalRetur       = Retur::sum('jumlah');

        return view('dashboard', compact(
            'totalPegawai',
            'totalPresensi',
            'totalProduk',
            'totalVendor',
            'totalBahanBaku',
            'totalBOM',
            'totalAset',        // Kirim data Aset ke view
            'totalPembelian',
            'totalPenjualan',
            'totalRetur'
        ));
    }
}
