<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Retur;
use App\Models\Pembelian;
use App\Models\Produk;

class ReturController extends Controller
{
    public function index()
    {
        $returs = Retur::with('produk')->get(); // pastikan relasi dimuat
        return view('transaksi.retur.index', compact('returs'));
    }

    public function create()
    {
        $produks = Produk::all();
        $pembelians = Pembelian::all();
        return view('transaksi.retur.create', compact('produks','pembelians'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'pembelian_id' => 'required|exists:pembelians,id',
            'produk_id' => 'required|exists:produks,id',
            'jumlah' => 'required|numeric|min:1',
        ]);

        Retur::create($request->all());
        return redirect()->route('transaksi.retur.index')->with('success', 'Data retur berhasil ditambahkan.');
    }

    public function edit(Retur $retur)
    {
        $produks = Produk::all();
        $pembelians = Pembelian::all();
        return view('transaksi.retur.edit', compact('retur', 'produks', 'pembelians'));
    }

    public function update(Request $request, Retur $retur)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'pembelian_id' => 'required|exists:pembelians,id',
            'produk_id' => 'required|exists:produks,id',
            'jumlah' => 'required|numeric|min:1',
        ]);

        $retur->update($request->all());
        return redirect()->route('transaksi.retur.index')->with('success', 'Data retur berhasil diperbarui.');
    }

    public function destroy(Retur $retur)
    {
        $retur->delete();
        return redirect()->route('transaksi.retur.index')->with('success', 'Data retur berhasil dihapus.');
    }
}
