<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use App\Models\BahanBaku;
use App\Models\Pembelian;
use App\Models\PembelianDetail;
use Illuminate\Http\Request;

class PembelianController extends Controller
{
    public function index()
    {
        $pembelians = Pembelian::with(['vendor', 'details.bahanBaku'])->get();
        return view('transaksi.pembelian.index', compact('pembelians'));
    }

    public function create()
    {
        $vendors = Vendor::all();
        $bahanBakus = BahanBaku::all();
        return view('transaksi.pembelian.create', compact('vendors', 'bahanBakus'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'vendor_id' => 'required',
            'tanggal' => 'required|date',
            'bahan_baku_id.*' => 'required|exists:bahan_bakus,id',
            'jumlah.*' => 'required|numeric|min:1',
            'harga_satuan.*' => 'required|numeric|min:0',
        ]);

        $total = 0;
        foreach ($request->jumlah as $i => $jumlah) {
            $total += $jumlah * $request->harga_satuan[$i];
        }

        $pembelian = Pembelian::create([
            'vendor_id' => $request->vendor_id,
            'tanggal' => $request->tanggal,
            'total' => $total,
        ]);

        foreach ($request->bahan_baku_id as $i => $bahanId) {
            PembelianDetail::create([
                'pembelian_id' => $pembelian->id,
                'bahan_baku_id' => $bahanId,
                'jumlah' => $request->jumlah[$i],
                'harga_satuan' => $request->harga_satuan[$i],
                'subtotal' => $request->jumlah[$i] * $request->harga_satuan[$i],
            ]);
        }

        return redirect()->route('transaksi.pembelian.index')
                         ->with('success', 'Pembelian berhasil disimpan.');
    }

    public function show($id)
    {
        $pembelian = Pembelian::with(['vendor', 'details.bahanBaku'])->findOrFail($id);
        return view('transaksi.pembelian.show', compact('pembelian'));
    }

    public function destroy($id)
    {
        $pembelian = Pembelian::findOrFail($id);
        $pembelian->details()->delete();
        $pembelian->delete();

        return redirect()->route('transaksi.pembelian.index')
                         ->with('success', 'Pembelian berhasil dihapus.');
    }
}
