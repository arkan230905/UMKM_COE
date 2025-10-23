<?php

namespace App\Http\Controllers;

use App\Models\Pembelian;
use App\Models\Vendor;
use Illuminate\Http\Request;

class PembelianController extends Controller
{
    public function index()
    {
        $pembelians = Pembelian::with('vendor')->get();
        return view('transaksi.pembelian.index', compact('pembelians'));
    }

    public function create()
    {
        $vendors = Vendor::all();
        return view('transaksi.pembelian.create', compact('vendors'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'vendor_id' => 'required|exists:vendors,id',
            'total' => 'required|numeric',
        ]);

        Pembelian::create($request->all());
        return redirect()->route('transaksi.pembelian.index')->with('success', 'Pembelian berhasil ditambahkan.');
    }

    public function edit(Pembelian $pembelian)
    {
        $vendors = Vendor::all();
        return view('transaksi.pembelian.edit', compact('pembelian','vendors'));
    }

    public function update(Request $request, Pembelian $pembelian)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'vendor_id' => 'required|exists:vendors,id',
            'total' => 'required|numeric',
        ]);

        $pembelian->update($request->all());
        return redirect()->route('transaksi.pembelian.index')->with('success', 'Pembelian berhasil diupdate.');
    }

    public function destroy(Pembelian $pembelian)
    {
        $pembelian->delete();
        return redirect()->route('transaksi.pembelian.index')->with('success', 'Pembelian berhasil dihapus.');
    }
}
