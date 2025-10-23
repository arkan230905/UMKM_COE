<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Penjualan;
use App\Models\Produk;

class PenjualanController extends Controller
{
    public function index()
    {
        // Ambil semua penjualan beserta relasi produk
        $penjualans = Penjualan::with('produk')->get();
        return view('transaksi.penjualan.index', compact('penjualans'));
    }

    public function create()
    {
        $produks = Produk::all();
        return view('transaksi.penjualan.create', compact('produks'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'produk_id' => 'required|exists:produks,id',
            'jumlah' => 'required|numeric|min:1',
            'total' => 'required|numeric|min:0',
            'tanggal' => 'required|date',
        ]);

        Penjualan::create($request->all());

        return redirect()->route('transaksi.penjualan.index')
                         ->with('success', 'Data penjualan berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $penjualan = Penjualan::findOrFail($id);
        $produks = Produk::all();
        return view('transaksi.penjualan.edit', compact('penjualan', 'produks'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'produk_id' => 'required|exists:produks,id',
            'jumlah' => 'required|numeric|min:1',
            'total' => 'required|numeric|min:0',
            'tanggal' => 'required|date',
        ]);

        $penjualan = Penjualan::findOrFail($id);
        $penjualan->update($request->all());

        return redirect()->route('transaksi.penjualan.index')
                         ->with('success', 'Data penjualan berhasil diupdate.');
    }

    public function destroy($id)
    {
        $penjualan = Penjualan::findOrFail($id);
        $penjualan->delete();

        return redirect()->route('transaksi.penjualan.index')
                         ->with('success', 'Data penjualan berhasil dihapus.');
    }
}
