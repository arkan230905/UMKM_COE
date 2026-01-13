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
    public function index(Request $request)
    {
        $query = Pembelian::with(['vendor', 'details.bahanBaku.satuan']);
        
        // Filter by nomor transaksi
        if ($request->filled('nomor_transaksi')) {
            $query->where('nomor_pembelian', 'like', '%' . $request->nomor_transaksi . '%');
        }
        
        // Filter by tanggal
        if ($request->filled('tanggal_mulai')) {
            $query->whereDate('tanggal', '>=', $request->tanggal_mulai);
        }
        if ($request->filled('tanggal_selesai')) {
            $query->whereDate('tanggal', '<=', $request->tanggal_selesai);
        }
        
        // Filter by vendor
        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }
        
        // Filter by payment method
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        $pembelians = $query->latest()->get();
        $vendors = Vendor::orderBy('nama_vendor')->get();
        
        return view('transaksi.pembelian.index', compact('pembelians', 'vendors'));
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
        $kasbank = \App\Helpers\AccountHelper::getKasBankAccounts();
        return view('transaksi.pembelian.create', compact('vendors', 'bahanBakus', 'satuans', 'kasbank'));
    }

    public function store(Request $request, StockService $stock, JournalService $journal)
    {
        // Dua mode: (A) pembelian bahan baku dengan detail arrays, (B) fallback lama (produk)
        if (is_array($request->bahan_baku_id)) {
            $request->validate([
                'vendor_id' => 'required|exists:vendors,id',
                'tanggal' => 'required|date',
                'payment_method' => 'required|in:cash,transfer,credit',
                'sumber_dana' => 'required_if:payment_method,cash,transfer|in:' . implode(',', \App\Helpers\AccountHelper::KAS_BANK_CODES),
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

            // Cek saldo kas jika pembayaran tunai atau transfer
            if ($request->payment_method === 'cash' || $request->payment_method === 'transfer') {
                $sumberDana = $request->sumber_dana;
                
                // Hitung saldo akun yang dipilih
                $saldoAwal = (float) (\App\Models\Coa::where('kode_akun', $sumberDana)->value('saldo_awal') ?? 0);
                $acc = \App\Models\Account::where('code', $sumberDana)->first();
                $journalBalance = 0.0;
                if ($acc) {
                    $journalBalance = (float) (\App\Models\JournalLine::where('account_id', $acc->id)
                        ->selectRaw('COALESCE(SUM(debit - credit),0) as bal')->value('bal') ?? 0);
                }
                $saldoAkun = $saldoAwal + $journalBalance;
                
                // Ambil nama akun untuk pesan error
                $namaAkun = \App\Models\Coa::where('kode_akun', $sumberDana)->value('nama_akun') ?? 'Akun '.$sumberDana;
                
                if ($saldoAkun + 1e-6 < $computedTotal) {
                    return back()->withErrors([
                        'kas' => 'Saldo '.$namaAkun.' tidak cukup untuk pembelian. Saldo saat ini: Rp '.number_format($saldoAkun,0,',','.').' ; Total pembelian: Rp '.number_format($computedTotal,0,',','.'),
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
                        'terbayar' => $request->payment_method === 'cash' ? $computedTotal : 0,
                        'sisa_pembayaran' => $request->payment_method === 'cash' ? 0 : $computedTotal,
                        'status' => $request->payment_method === 'cash' ? 'lunas' : 'belum_lunas',
                        'payment_method' => $request->payment_method,
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
                        
                        // SIMPAN DETAIL PEMBELIAN KE DATABASE - CRITICAL!
                        $detail = PembelianDetail::create([
                            'pembelian_id' => $pembelian->id,
                            'bahan_baku_id' => $bbId,
                            'jumlah' => $qtyInput,
                            'satuan' => $request->satuan[$i] ?? $bahan->satuan,
                            'harga_satuan' => $pricePerInputUnit,
                            'subtotal' => $subtotal,
                            'faktor_konversi' => $faktorKonversi,
                        ]);
                        
                        // Log untuk debugging
                        \Log::info('Pembelian Detail Created', [
                            'pembelian_id' => $pembelian->id,
                            'detail_id' => $detail->id,
                            'bahan_baku_id' => $bbId,
                            'jumlah' => $qtyInput,
                        ]);
                        
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
                    
                    // VALIDASI: Pastikan detail tersimpan
                    $savedDetails = PembelianDetail::where('pembelian_id', $pembelian->id)->count();
                    if ($savedDetails === 0) {
                        \Log::error('CRITICAL: Pembelian detail tidak tersimpan!', [
                            'pembelian_id' => $pembelian->id,
                            'expected_items' => count($request->bahan_baku_id),
                        ]);
                        throw new \Exception('Gagal menyimpan detail pembelian. Silakan coba lagi.');
                    }
                    
                    \Log::info('Pembelian berhasil dengan detail', [
                        'pembelian_id' => $pembelian->id,
                        'detail_count' => $savedDetails,
                    ]);

                    // Jurnal: Dr Persediaan Bahan Baku (1104) ; Cr Kas/Bank/Utang Usaha
                    // Tentukan akun kredit berdasarkan metode pembayaran
                    if ($request->payment_method === 'cash' || $request->payment_method === 'transfer') {
                        // Gunakan sumber dana yang dipilih user
                        $creditAccountCode = $request->sumber_dana;
                    } else {
                        $creditAccountCode = '2101';  // Hutang Usaha (kredit)
                    }
                    
                    $journal->post($request->tanggal, 'purchase', (int)$pembelian->id, 'Pembelian Bahan Baku', [
                        ['code' => '1104', 'debit' => (float)$pembelian->total_harga, 'credit' => 0],  // Dr. Persediaan Bahan Baku
                        ['code' => $creditAccountCode, 'debit' => 0, 'credit' => (float)$pembelian->total_harga],  // Cr. Kas/Bank/Hutang Usaha
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
