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

<<<<<<< HEAD
=======
        // Kirim semua ke view dashboard
>>>>>>> 73ecd34c0ff44e1b46e8fcae2de615861d360f74
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

  public function tentangPerusahaan()
{
    // Data perusahaan (disimpan sementara di session agar bisa diubah manual)
    $dataPerusahaan = session('dataPerusahaan', (object)[
        'nama' => 'PT Madusem Digital Nusantara',
        'alamat' => 'Jl. Merdeka No. 123, Bandung, Jawa Barat',
        'email' => 'info@madusem.co.id',
        'telepon' => '(022) 123-4567'
    ]);

    return view('tentang-perusahaan', compact('dataPerusahaan'));
}

public function updatePerusahaan(Request $request)
{
    // Ambil data dari form dan simpan ke session (sementara, belum database)
    $dataPerusahaan = (object)[
        'nama' => $request->nama,
        'alamat' => $request->alamat,
        'email' => $request->email,
        'telepon' => $request->telepon,
    ];

    session(['dataPerusahaan' => $dataPerusahaan]);

    return redirect()->route('tentang-perusahaan')->with('success', 'Data perusahaan berhasil diperbarui!');
}

}