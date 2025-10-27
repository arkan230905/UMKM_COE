<?php

namespace App\Http\Controllers;

use App\Models\Pembelian;
use App\Models\Penjualan;
use App\Models\Produk;

class LaporanController extends Controller
{
    // === LAPORAN PEMBELIAN ===
    public function pembelian()
    {
        $pembelian = Pembelian::with('vendor', 'produk')
            ->orderBy('tanggal', 'desc')
            ->get();

        return view('laporan.pembelian.index', compact('pembelian'));
    }

    // === LAPORAN PENJUALAN ===
    public function penjualan()
    {
        $penjualan = Penjualan::with('produk')
            ->orderBy('tanggal', 'desc')
            ->get();

        return view('laporan.penjualan.index', compact('penjualan'));
    }

    // === LAPORAN STOK ===
    public function stok()
    {
        $produk = Produk::orderBy('nama_produk', 'asc')->get();

        return view('laporan.stok.index', compact('produk'));
    }
}
