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
        $query = Pembelian::with(['vendor', 'details.bahanBaku.satuan', 'details.bahanPendukung.satuan']);
        
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
        $bahanBakus = BahanBaku::with('satuan')->get();
        $bahanPendukungs = \App\Models\BahanPendukung::with('satuan')->get();
        $satuans = \App\Models\Satuan::all();
        $kasbank = \App\Helpers\AccountHelper::getKasBankAccountsWithBalance();
        return view('transaksi.pembelian.create', compact('vendors', 'bahanBakus', 'bahanPendukungs', 'satuans', 'kasbank'));
    }

    public function store(Request $request, StockService $stock, JournalService $journal)
    {
        // Cek apakah ini pembelian bahan baku atau bahan pendukung
        $isBahanBaku = is_array($request->bahan_baku_id) && count(array_filter($request->bahan_baku_id)) > 0;
        $isBahanPendukung = is_array($request->bahan_pendukung_id) && count(array_filter($request->bahan_pendukung_id)) > 0;
        
        if ($isBahanBaku || $isBahanPendukung) {
            // Validasi dasar
            $rules = [
                'vendor_id' => 'required|exists:vendors,id',
                'tanggal' => 'required|date',
                'payment_method' => 'required|in:cash,transfer,credit',
                'sumber_dana' => 'required_if:payment_method,cash,transfer|in:' . implode(',', \App\Helpers\AccountHelper::KAS_BANK_CODES),
            ];
            
            if ($isBahanBaku) {
                $rules['bahan_baku_id'] = 'required|array';
                $rules['jumlah'] = 'required|array';
                $rules['satuan_pembelian'] = 'required|array';
                $rules['harga_satuan_pembelian'] = 'required|array';
            }
            
            if ($isBahanPendukung) {
                $rules['bahan_pendukung_id'] = 'required|array';
                $rules['jumlah_pendukung'] = 'required|array';
                $rules['harga_satuan_pendukung'] = 'required|array';
            }
            
            $request->validate($rules);

            // Hitung total terlebih dahulu untuk validasi kas
            $computedTotal = 0.0;
            
            // Total dari bahan baku (gunakan harga pembelian asli)
            if ($isBahanBaku) {
                foreach ($request->bahan_baku_id as $i => $bbId) {
                    if (empty($bbId)) continue;
                    $qtyInput = (float) ($request->jumlah[$i] ?? 0);
                    $pricePerInputUnit = (float) ($request->harga_satuan_pembelian[$i] ?? 0);
                    $computedTotal += $qtyInput * $pricePerInputUnit;
                }
            }
            
            // Total dari bahan pendukung (gunakan harga pembelian asli)
            if ($isBahanPendukung) {
                foreach ($request->bahan_pendukung_id as $i => $bpId) {
                    if (empty($bpId)) continue;
                    $qtyInput = (float) ($request->jumlah_pendukung[$i] ?? 0);
                    $pricePerInputUnit = (float) ($request->harga_satuan_pendukung[$i] ?? 0);
                    $computedTotal += $qtyInput * $pricePerInputUnit;
                }
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

            return DB::transaction(function () use ($request, $stock, $journal, $computedTotal, $isBahanBaku, $isBahanPendukung) {
                DB::beginTransaction();

                try {
                    // Tentukan tipe pembelian berdasarkan vendor
                    $vendor = Vendor::find($request->vendor_id);
                    $tipePembelian = $vendor->kategori ?? 'Bahan Baku';
                    
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

                    $totalBahanBaku = 0;
                    $totalBahanPendukung = 0;

                    // 2. Proses bahan baku
                    if ($isBahanBaku) {
                        foreach ($request->bahan_baku_id as $i => $bbId) {
                            if (empty($bbId)) continue;
                            
                            $bahanBaku = BahanBaku::findOrFail($bbId);
                            
                            // Get input values
                            $qtyInput = (float) ($request->jumlah[$i] ?? 0);
                            $satuanPembelian = strtolower(trim($request->satuan_pembelian[$i] ?? ''));
                            $hargaPembelian = (float) ($request->harga_satuan_pembelian[$i] ?? 0);
                            $hargaPerSatuanUtama = (float) ($request->harga_satuan[$i] ?? 0);
                            
                            // Konversi ke satuan utama menggunakan method dari model
                            $konversiResult = $bahanBaku->konversiKeSatuanUtama($hargaPembelian, $satuanPembelian, $qtyInput);
                            $qtyInBaseUnit = $konversiResult['quantity'];
                            $pricePerBaseUnit = $konversiResult['harga_per_satuan_utama'];
                            
                            // Hitung subtotal (gunakan harga pembelian asli untuk konsistensi)
                            $subtotal = $qtyInput * $hargaPembelian;
                            $totalBahanBaku += $subtotal;
                            
                            // Simpan detail pembelian
                            PembelianDetail::create([
                                'pembelian_id' => $pembelian->id,
                                'bahan_baku_id' => $bbId,
                                'jumlah' => $qtyInput,
                                'satuan' => $satuanPembelian,
                                'harga_satuan' => $hargaPembelian,
                                'subtotal' => $qtyInput * $hargaPembelian
                            ]);
                            
                            // Update moving average harga bahan & stok
                            $stokLama = (float) ($bahanBaku->stok ?? 0);
                            $hargaRataRataLama = (float) ($bahanBaku->harga_rata_rata ?? 0);
                            $stokBaru = $stokLama + $qtyInBaseUnit;
                            
                            // Update harga rata-rata menggunakan method dari model
                            if ($stokBaru > 0) {
                                $bahanBaku->updateHargaRataRata($hargaPembelian, $qtyInBaseUnit);
                            }
                            
                            $bahanBaku->stok = $stokBaru;
                            $bahanBaku->save();

                            // FIFO layer IN + movement
                            $unitStr = $bahanBaku->satuan->nama ?? $bahanBaku->satuan ?? 'pcs';
                            $stock->addLayer('material', $bahanBaku->id, $qtyInBaseUnit, $unitStr, $pricePerBaseUnit, 'purchase', $pembelian->id, $request->tanggal);
                        }
                    }

                    // 3. Proses bahan pendukung
                    if ($isBahanPendukung) {
                        foreach ($request->bahan_pendukung_id as $i => $bpId) {
                            if (empty($bpId)) continue;
                            
                            $bahanPendukung = \App\Models\BahanPendukung::findOrFail($bpId);
                            $qtyInput = (float) ($request->jumlah_pendukung[$i] ?? 0);
                            $pricePerInputUnit = (float) ($request->harga_satuan_pendukung[$i] ?? 0);
                            $faktorKonversi = (float) ($request->faktor_konversi_pendukung[$i] ?? 1);
                            
                            // Hitung jumlah dalam satuan dasar
                            $qtyInBaseUnit = $qtyInput * $faktorKonversi;
                            
                            // Hitung harga per satuan dasar
                            $pricePerBaseUnit = $pricePerInputUnit / $faktorKonversi;
                            
                            $subtotal = $qtyInput * $pricePerInputUnit;
                            $totalBahanPendukung += $subtotal;

                            // SIMPAN DETAIL PEMBELIAN KE DATABASE
                            $detail = PembelianDetail::create([
                                'pembelian_id' => $pembelian->id,
                                'bahan_baku_id' => null,
                                'bahan_pendukung_id' => $bpId,
                                'jumlah' => $qtyInput,
                                'satuan' => $request->satuan_pendukung[$i] ?? ($bahanPendukung->satuan->nama ?? 'pcs'),
                                'harga_satuan' => $pricePerInputUnit,
                                'subtotal' => $subtotal,
                                'faktor_konversi' => $faktorKonversi,
                            ]);
                            
                            // Update moving average harga bahan & stok
                            $stokLama = (float) ($bahanPendukung->stok ?? 0);
                            $hargaLama = (float) ($bahanPendukung->harga_satuan ?? 0);
                            $stokBaru = $stokLama + $qtyInBaseUnit;
                            $hargaBaru = $stokBaru > 0 ? (($stokLama * $hargaLama) + $subtotal) / $stokBaru : $pricePerBaseUnit;

                            $bahanPendukung->stok = $stokBaru;
                            $bahanPendukung->harga_satuan = $hargaBaru;
                            $bahanPendukung->save();

                            // FIFO layer IN + movement untuk bahan pendukung
                            $unitStr = (string)($bahanPendukung->satuan->kode ?? $bahanPendukung->satuan->nama ?? $bahanPendukung->satuan ?? 'pcs');
                            $stock->addLayer('support', $bahanPendukung->id, $qtyInBaseUnit, $unitStr, $pricePerBaseUnit, 'purchase', $pembelian->id, $request->tanggal);
                        }
                    }

                    // Commit transaksi database
                    DB::commit();
                    
                    // VALIDASI: Pastikan detail tersimpan
                    $savedDetails = PembelianDetail::where('pembelian_id', $pembelian->id)->count();
                    if ($savedDetails === 0) {
                        \Log::error('CRITICAL: Pembelian detail tidak tersimpan!', [
                            'pembelian_id' => $pembelian->id,
                        ]);
                        throw new \Exception('Gagal menyimpan detail pembelian. Silakan coba lagi.');
                    }
                    
                    \Log::info('Pembelian berhasil dengan detail', [
                        'pembelian_id' => $pembelian->id,
                        'detail_count' => $savedDetails,
                        'total_bahan_baku' => $totalBahanBaku,
                        'total_bahan_pendukung' => $totalBahanPendukung,
                    ]);

                    // UPDATE BIAYA BAHAN OTOMATIS
                    // Update biaya bahan untuk semua produk yang menggunakan bahan-bahan yang dibeli
                    $this->updateBiayaBahanAfterPurchase($request);

                    // Jurnal berdasarkan tipe pembelian
                    if ($request->payment_method === 'cash' || $request->payment_method === 'transfer') {
                        $creditAccountCode = $request->sumber_dana;
                    } else {
                        $creditAccountCode = '2101';  // Hutang Usaha (kredit)
                    }
                    
                    $journalLines = [];
                    $journalDesc = 'Pembelian ';
                    
                    // Jurnal untuk bahan baku
                    if ($totalBahanBaku > 0) {
                        $journalLines[] = ['code' => '1104', 'debit' => (float)$totalBahanBaku, 'credit' => 0];  // Dr. Persediaan Bahan Baku
                        $journalDesc .= 'Bahan Baku';
                    }
                    
                    // Jurnal untuk bahan pendukung
                    if ($totalBahanPendukung > 0) {
                        $journalLines[] = ['code' => '1105', 'debit' => (float)$totalBahanPendukung, 'credit' => 0];  // Dr. Persediaan Bahan Pendukung
                        $journalDesc .= ($totalBahanBaku > 0 ? ' & ' : '') . 'Bahan Pendukung';
                    }
                    
                    // Total debit harus sama dengan total credit
                    $totalDebit = $totalBahanBaku + $totalBahanPendukung;
                    
                    // Kredit ke kas/bank/hutang (harus sama dengan total debit)
                    $journalLines[] = ['code' => $creditAccountCode, 'debit' => 0, 'credit' => (float)$totalDebit];
                    
                    // Debug log untuk memeriksa balance
                    \Log::info('Journal Balance Check', [
                        'total_debit' => $totalDebit,
                        'total_credit' => $totalDebit,
                        'computed_total' => $computedTotal,
                        'journal_lines' => $journalLines
                    ]);
                    
                    $journal->post($request->tanggal, 'purchase', (int)$pembelian->id, $journalDesc, $journalLines);

                    return redirect()->route('transaksi.pembelian.index')
                        ->with('success', 'Data pembelian berhasil disimpan!');
                } catch (\Exception $e) {
                    DB::rollBack();
                    \Log::error('Error menyimpan pembelian: ' . $e->getMessage());
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

    /**
     * Update biaya bahan untuk semua produk yang menggunakan bahan-bahan yang dibeli
     * Ini memastikan biaya bahan selalu mengikuti harga terbaru
     */
    private function updateBiayaBahanAfterPurchase($request)
    {
        try {
            $updatedProducts = [];
            
            // Update untuk bahan baku
            if ($request->has('bahan_baku_id')) {
                foreach ($request->bahan_baku_id as $bahanBakuId) {
                    if (empty($bahanBakuId)) continue;
                    
                    // Cari semua produk yang menggunakan bahan baku ini di BomJobCosting
                    $productsUsingBahan = \App\Models\Produk::whereHas('bomJobCosting.detailBBB', function($query) use ($bahanBakuId) {
                        $query->where('bahan_baku_id', $bahanBakuId);
                    })->get();
                    
                    foreach ($productsUsingBahan as $product) {
                        if ($product->bomJobCosting) {
                            // Recalculate biaya bahan untuk produk ini
                            $product->bomJobCosting->recalculate();
                            $updatedProducts[] = $product->nama_produk;
                        }
                    }
                }
            }
            
            // Update untuk bahan pendukung
            if ($request->has('bahan_pendukung_id')) {
                foreach ($request->bahan_pendukung_id as $bahanPendukungId) {
                    if (empty($bahanPendukungId)) continue;
                    
                    // Cari semua produk yang menggunakan bahan pendukung ini di BomJobCosting
                    $productsUsingBahan = \App\Models\Produk::whereHas('bomJobCosting.detailBahanPendukung', function($query) use ($bahanPendukungId) {
                        $query->where('bahan_pendukung_id', $bahanPendukungId);
                    })->get();
                    
                    foreach ($productsUsingBahan as $product) {
                        if ($product->bomJobCosting) {
                            // Recalculate biaya bahan untuk produk ini
                            $product->bomJobCosting->recalculate();
                            $updatedProducts[] = $product->nama_produk;
                        }
                    }
                }
            }
            
            // Log hasil update
            if (!empty($updatedProducts)) {
                \Log::info('Biaya bahan otomatis diupdate setelah pembelian', [
                    'updated_products' => array_unique($updatedProducts),
                    'total_products' => count(array_unique($updatedProducts))
                ]);
            }
            
        } catch (\Exception $e) {
            \Log::error('Gagal mengupdate biaya bahan setelah pembelian: ' . $e->getMessage(), [
                'request_data' => $request->all()
            ]);
        }
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
