<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pembelian;
use App\Models\PembelianDetail;
use App\Models\Vendor;
use App\Models\Produk;
use App\Models\BahanBaku;
use App\Services\StockService;
use App\Services\JournalService;
use App\Support\UnitConverter;
use Illuminate\Support\Facades\DB;

class PembelianController extends Controller
{
    public function index()
    {
        $pembelians = Pembelian::with(['vendor'])->latest()->get();
        return view('transaksi.pembelian.index', compact('pembelians'));
    }

    public function show($id)
    {
        $pembelian = Pembelian::with(['vendor', 'details.bahanBaku'])->findOrFail($id);
        return view('transaksi.pembelian.show', compact('pembelian'));
    }

    public function create()
    {
        $vendors = Vendor::all();
        $bahanBakus = BahanBaku::all();
        return view('transaksi.pembelian.create', compact('vendors', 'bahanBakus'));
    }

    public function store(Request $request, StockService $stock, JournalService $journal)
    {
        // Dua mode: (A) pembelian bahan baku dengan detail arrays, (B) fallback lama (produk)
        if (is_array($request->bahan_baku_id)) {
            $request->validate([
                'vendor_id' => 'required|exists:vendors,id',
                'tanggal' => 'required|date',
                'payment_method' => 'required|in:cash,credit',
                'bahan_baku_id' => 'required|array',
                'jumlah' => 'required|array',
                'harga_satuan' => 'required|array',
                'satuan' => 'nullable|array',
            ]);

            return DB::transaction(function () use ($request, $stock, $journal) {
                $converter = new UnitConverter();
                $total = 0;
                foreach ($request->bahan_baku_id as $i => $bbId) {
                    $qtyInput = (float) ($request->jumlah[$i] ?? 0);
                    $pricePerInputUnit = (float) ($request->harga_satuan[$i] ?? 0);
                    $total += $qtyInput * $pricePerInputUnit;
                }

                $pembelian = Pembelian::create([
                    'vendor_id' => $request->vendor_id,
                    'tanggal' => $request->tanggal,
                    'payment_method' => $request->payment_method,
                    'total' => $total,
                ]);

                foreach ($request->bahan_baku_id as $i => $bbId) {
                    $bahan = BahanBaku::findOrFail($bbId);
                    $qtyInput = (float) ($request->jumlah[$i] ?? 0);
                    $satuanInput = $request->satuan[$i] ?? $bahan->satuan;
                    $pricePerInputUnit = (float) ($request->harga_satuan[$i] ?? 0);
                    $subtotal = $qtyInput * $pricePerInputUnit;

                    // Konversi qty ke satuan bahan untuk stok & average cost
                    $qtyBase = $converter->convert($qtyInput, (string)$satuanInput, (string)$bahan->satuan);
                    $unitCostBase = $qtyBase > 0 ? ($subtotal / $qtyBase) : $pricePerInputUnit;

                    PembelianDetail::create([
                        'pembelian_id' => $pembelian->id,
                        'bahan_baku_id' => $bahan->id,
                        'jumlah' => $qtyInput,
                        'harga_satuan' => $pricePerInputUnit,
                        'subtotal' => $subtotal,
                        // Simpan satuan input agar ditampilkan di daftar
                        'satuan' => $satuanInput,
                    ]);

                    // Update moving average harga bahan & stok
                    $stokLama = (float) ($bahan->stok ?? 0);
                    $hargaLama = (float) ($bahan->harga_satuan ?? 0);
                    $stokBaru = $stokLama + $qtyBase;
                    $hargaBaru = $stokBaru > 0 ? (($stokLama * $hargaLama) + $subtotal) / $stokBaru : $unitCostBase;

                    $bahan->stok = $stokBaru;
                    $bahan->harga_satuan = $hargaBaru;
                    $bahan->save();

                    // FIFO layer IN + movement
                    $stock->addLayer('material', $bahan->id, $qtyBase, (string)$bahan->satuan, $unitCostBase, 'purchase', $pembelian->id, $request->tanggal);
                }

                // Jurnal: Dr Persediaan Bahan Baku (121) ; Cr Kas/Bank (101) atau Hutang Usaha (201) jika kredit
                $creditAcc = $request->payment_method === 'credit' ? '201' : '101';
                $journal->post($request->tanggal, 'purchase', (int)$pembelian->id, 'Pembelian Bahan Baku', [
                    ['code' => '121', 'debit' => (float)$pembelian->total, 'credit' => 0],
                    ['code' => $creditAcc, 'debit' => 0, 'credit' => (float)$pembelian->total],
                ]);

                return redirect()->route('transaksi.pembelian.index')->with('success', 'Data pembelian bahan baku berhasil disimpan!');
            });
        } else {
            // Fallback lama (jika masih digunakan)
            $request->validate([
                'supplier_id' => 'required|exists:suppliers,id',
                'produk_id'   => 'required|exists:produks,id',
                'jumlah'      => 'required|numeric|min:1',
                'harga_beli'  => 'required|numeric|min:0',
            ]);

            $total = $request->jumlah * $request->harga_beli;

            Pembelian::create([
                'supplier_id' => $request->supplier_id,
                'produk_id'   => $request->produk_id,
                'jumlah'      => $request->jumlah,
                'harga_beli'  => $request->harga_beli,
                'total'       => $total,
            ]);

            return redirect()->route('transaksi.pembelian.index')->with('success', 'Data pembelian berhasil disimpan!');
        }
    }

    public function edit($id)
    {
        $pembelian = Pembelian::findOrFail($id);
        $vendors = Vendor::all();
        $bahanBakus = BahanBaku::all();
        return view('transaksi.pembelian.edit', compact('pembelian', 'vendors', 'bahanBakus'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'produk_id'   => 'required|exists:produks,id',
            'jumlah'      => 'required|numeric|min:1',
            'harga_beli'  => 'required|numeric|min:0',
        ]);

        $pembelian = Pembelian::findOrFail($id);
        $total = $request->jumlah * $request->harga_beli;

        $pembelian->update([
            'supplier_id' => $request->supplier_id,
            'produk_id'   => $request->produk_id,
            'jumlah'      => $request->jumlah,
            'harga_beli'  => $request->harga_beli,
            'total'       => $total,
        ]);

        return redirect()->route('transaksi.pembelian.index')->with('success', 'Data pembelian berhasil diperbarui!');
    }

    public function destroy($id, JournalService $journal)
    {
        $pembelian = Pembelian::findOrFail($id);
        // Hapus jurnal terkait pembelian
        $journal->deleteByRef('purchase', (int)$pembelian->id);
        // Hapus data
        $pembelian->delete();

        return redirect()->route('transaksi.pembelian.index')->with('success', 'Data pembelian dan jurnal terkait berhasil dihapus!');
    }
}
