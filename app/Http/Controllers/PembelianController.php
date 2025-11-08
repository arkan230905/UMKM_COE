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
        $satuans = \App\Models\Satuan::all();
        return view('transaksi.pembelian.create', compact('vendors', 'bahanBakus', 'satuans'));
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
                'faktor_konversi' => 'nullable|array',
            ]);

            // Hitung total terlebih dahulu untuk validasi kas
            $computedTotal = 0.0;
            foreach ($request->bahan_baku_id as $i => $bbId) {
                $qtyInput = (float) ($request->jumlah[$i] ?? 0);
                $pricePerInputUnit = (float) ($request->harga_satuan[$i] ?? 0);
                $computedTotal += $qtyInput * $pricePerInputUnit;
            }

            // Cek saldo kas jika pembayaran tunai
            if ($request->payment_method === 'cash') {
                $cashCode = '101';
                $saldoAwal = (float) (\App\Models\Coa::where('kode_akun', $cashCode)->value('saldo_awal') ?? 0);
                $acc = \App\Models\Account::where('code', $cashCode)->first();
                $journalBalance = 0.0;
                if ($acc) {
                    $journalBalance = (float) (\App\Models\JournalLine::where('account_id', $acc->id)
                        ->selectRaw('COALESCE(SUM(debit - credit),0) as bal')->value('bal') ?? 0);
                }
                $cashBalance = $saldoAwal + $journalBalance;
                if ($cashBalance + 1e-6 < $computedTotal) {
                    return back()->withErrors([
                        'kas' => 'Saldo kas tidak cukup untuk pembelian tunai. Saldo kas saat ini: Rp '.number_format($cashBalance,0,',','.').' ; Total pembelian: Rp '.number_format($computedTotal,0,',','.'),
                    ])->withInput();
                }
            }

            return DB::transaction(function () use ($request, $stock, $journal, $computedTotal) {
                DB::beginTransaction();

                try {
                    // 1. Buat header pembelian
                    $pembelian = new Pembelian([
                        'vendor_id' => $request->vendor_id,
                        'tanggal' => $request->tanggal,
                        'total_harga' => $computedTotal,
                        'status' => 'draft',
                        'payment_method' => $request->payment_method,
                        'keterangan' => $request->keterangan,
                    ]);
                    $pembelian->save();

                    // 2. Proses setiap item
                    foreach ($request->bahan_baku_id as $i => $bbId) {
                        $bahanBaku = BahanBaku::findOrFail($bbId);
                        $qtyInput = (float) ($request->jumlah[$i] ?? 0);
                        $pricePerInputUnit = (float) ($request->harga_satuan[$i] ?? 0);
                        $faktorKonversi = (float) ($request->faktor_konversi[$i] ?? 1);
                        
                        // Hitung jumlah dalam satuan dasar
                        $qtyInBaseUnit = $qtyInput * $faktorKonversi;
                        
                        // Hitung harga per satuan dasar
                        $pricePerBaseUnit = $pricePerInputUnit / $faktorKonversi;
                        
                        $subtotal = $qtyInput * $pricePerInputUnit;

                        // Ambil data bahan baku
                        $bahan = BahanBaku::findOrFail($bbId);
                        
                        // Update moving average harga bahan & stok
                        $stokLama = (float) ($bahan->stok ?? 0);
                        $hargaLama = (float) ($bahan->harga_satuan ?? 0);
                        $stokBaru = $stokLama + $qtyInBaseUnit;
                        $hargaBaru = $stokBaru > 0 ? (($stokLama * $hargaLama) + $subtotal) / $stokBaru : $pricePerBaseUnit;

                        $bahan->stok = $stokBaru;
                        $bahan->harga_satuan = $hargaBaru;
                        $bahan->save();

                        // FIFO layer IN + movement
                        $unitStr = (string)($bahan->satuan->kode ?? $bahan->satuan->nama ?? $bahan->satuan ?? 'pcs');
                        $stock->addLayer('material', $bahan->id, $qtyInBaseUnit, $unitStr, $pricePerBaseUnit, 'purchase', $pembelian->id, $request->tanggal);
                    }

                    // Commit transaksi database
                    DB::commit();

                    // Jurnal: Dr Persediaan Bahan Baku (121) ; Cr Kas/Bank (101) atau Hutang Usaha (201) jika kredit
                    $creditAcc = $request->payment_method === 'credit' ? '201' : '101';
                    $journal->post($request->tanggal, 'purchase', (int)$pembelian->id, 'Pembelian Bahan Baku', [
                        ['code' => '121', 'debit' => (float)$pembelian->total_harga, 'credit' => 0],
                        ['code' => $creditAcc, 'debit' => 0, 'credit' => (float)$pembelian->total_harga],
                    ]);

                    return redirect()->route('transaksi.pembelian.index')
                        ->with('success', 'Data pembelian bahan baku berhasil disimpan!');
                } catch (\Exception $e) {
                    DB::rollBack();
                    return back()
                        ->withInput()
                        ->with('error', 'Gagal menyimpan pembelian: ' . $e->getMessage());
                }
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
