<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pembelian;
use App\Models\PembelianDetail;
use App\Models\Vendor;
use App\Models\Produk;
use App\Models\BahanBaku;
use App\Models\BahanPendukung;
use App\Models\Coa;
use App\Helpers\AccountHelper;
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
        $pembelian = Pembelian::with(['vendor', 'details.bahanBaku', 'details.bahanPendukung'])->findOrFail($id);
        return view('transaksi.pembelian.show', compact('pembelian'));
    }

    public function create()
    {
        $vendors = Vendor::all();
        $bahanBakus = BahanBaku::with('satuan')->get();
        $bahanPendukungs = \App\Models\BahanPendukung::with('satuan')->get();
        $satuans = \App\Models\Satuan::all();
        
        // Ambil data COA untuk kas dan bank yang relevan saja
        $kasbank = \App\Models\Coa::where('tipe_akun', 'Asset')
            ->where(function($query) {
                $query->where(function($subQuery) {
                          $subQuery->where('nama_akun', 'like', '%kas%')
                                 ->orWhere('nama_akun', 'like', '%tunai%')
                                 ->orWhere('nama_akun', 'like', '%cash%');
                      })
                      ->orWhere(function($subQuery) {
                          $subQuery->where('nama_akun', 'like', '%bank%')
                                 ->orWhere('nama_akun', 'like', '%bca%')
                                 ->orWhere('nama_akun', 'like', '%bni%')
                                 ->orWhere('nama_akun', 'like', '%bri%')
                                 ->orWhere('nama_akun', 'like', '%mandiri%');
                      });
            })
            ->where('nama_akun', '!=', '')
            ->where(function($query) {
                $query->whereNot('nama_akun', 'like', '%persediaan%')
                      ->whereNot('nama_akun', 'like', '%inventory%')
                      ->whereNot('nama_akun', 'like', '%stok%')
                      ->whereNot('nama_akun', 'like', '%barang%');
            })
            ->where(function($query) {
                $query->where('kode_akun', 'like', '1%')
                      ->orWhere('kode_akun', 'like', '11%');
            })
            ->orderBy('kode_akun')
            ->get();
            
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
                'bank_id' => 'required',
            ];
            
            if ($isBahanBaku) {
                $rules['bahan_baku_id'] = 'required|array';
                $rules['jumlah'] = 'required|array';
                $rules['satuan_pembelian'] = 'required|array';
                $rules['harga_total'] = 'required|array';
                $rules['jumlah_satuan_utama'] = 'required|array';
                $rules['harga_satuan_utama'] = 'required|array';
            }
            
            if ($isBahanPendukung) {
                $rules['bahan_pendukung_id'] = 'required|array';
                $rules['jumlah_pendukung'] = 'required|array';
                $rules['satuan_pembelian_pendukung'] = 'required|array';
                $rules['harga_total_pendukung'] = 'required|array';
                $rules['jumlah_satuan_utama_pendukung'] = 'required|array';
            }
            
            $request->validate($rules);

            // Hitung total terlebih dahulu untuk validasi kas
            $computedTotal = 0.0;
            
            // Total dari bahan baku (gunakan harga total)
            if ($isBahanBaku) {
                foreach ($request->bahan_baku_id as $i => $bbId) {
                    if (empty($bbId)) continue;
                    $hargaTotal = (float) ($request->harga_total[$i] ?? 0);
                    $computedTotal += $hargaTotal;
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
            if ($request->bank_id !== 'credit') {
                $bankId = $request->bank_id;
                
                // Hitung saldo akun yang dipilih
                $saldoAwal = (float) (\App\Models\Coa::where('id', $bankId)->value('saldo_awal') ?? 0);
                $saldoAkun = $saldoAwal;
                
                // Ambil nama akun untuk pesan error
                $namaAkun = \App\Models\Coa::where('id', $bankId)->value('nama_akun') ?? 'Akun '.$bankId;
                
                if ($saldoAkun + 1e-6 < $computedTotal) {
                    return back()->withErrors([
                        'kas' => 'Saldo '.$namaAkun.' tidak cukup untuk pembelian. Saldo saat ini: Rp '.number_format($saldoAkun,0,',','.').' ; Total pembelian: Rp '.number_format($computedTotal,0,',','.'),
                    ])->withInput();
                }
            }

            return DB::transaction(function () use ($request, $stock, $journal, $computedTotal, $isBahanBaku, $isBahanPendukung) {
                DB::beginTransaction();

                try {
                    // Tentukan payment method berdasarkan bank_id
                    $paymentMethod = 'transfer'; // default
                    if ($request->bank_id === 'credit') {
                        $paymentMethod = 'credit';
                    } else {
                        // Cek apakah ini kas atau bank
                        $bank = \App\Models\Coa::find($request->bank_id);
                        if ($bank && (str_contains(strtolower($bank->nama_akun), 'kas') || str_starts_with($bank->kode_akun, '1'))) {
                            $paymentMethod = 'cash';
                        } else {
                            $paymentMethod = 'transfer';
                        }
                    }
                    
                    // Tentukan tipe pembelian berdasarkan vendor
                    $vendor = Vendor::find($request->vendor_id);
                    $tipePembelian = $vendor->kategori ?? 'Bahan Baku';
                    
                    // 1. Buat header pembelian
                    $pembelian = new Pembelian([
                        'vendor_id' => $request->vendor_id,
                        'nomor_faktur' => $request->nomor_faktur,
                        'tanggal' => $request->tanggal,
                        'total_harga' => $computedTotal,
                        'terbayar' => ($paymentMethod === 'cash' || $paymentMethod === 'transfer') ? $computedTotal : 0,
                        'sisa_pembayaran' => ($paymentMethod === 'cash' || $paymentMethod === 'transfer') ? 0 : $computedTotal,
                        'status' => ($paymentMethod === 'cash' || $paymentMethod === 'transfer') ? 'lunas' : 'belum_lunas',
                        'payment_method' => $paymentMethod,
                        'bank_id' => $request->bank_id === 'credit' ? null : $request->bank_id,
                        'keterangan' => $request->keterangan,
                    ]);
                    $pembelian->save();

                    $totalBahanBaku = 0;
                    $totalBahanPendukung = 0;

                    // 2. Proses bahan baku
                    if ($isBahanBaku) {
                        foreach ($request->bahan_baku_id as $i => $bbId) {
                            if (empty($bbId)) continue;
                            
                            $bahanBaku = BahanBaku::findOrFail($bbId);
                            
                            // Get input values from new form structure
                            $qtyInput = (float) ($request->jumlah[$i] ?? 0);
                            $satuanPembelian = strtolower(trim($request->satuan_pembelian[$i] ?? ''));
                            $hargaTotal = (float) ($request->harga_total[$i] ?? 0);
                            $qtyInBaseUnit = (float) ($request->jumlah_satuan_utama[$i] ?? 0);
                            $pricePerBaseUnit = (float) ($request->harga_satuan_utama[$i] ?? 0);
                            
                            // Hitung subtotal menggunakan harga total
                            $subtotal = $hargaTotal;
                            $totalBahanBaku += $subtotal;
                            
                            // Simpan detail pembelian
                            PembelianDetail::create([
                                'pembelian_id' => $pembelian->id,
                                'bahan_baku_id' => $bbId,
                                'jumlah' => $qtyInBaseUnit, // Simpan dalam satuan utama
                                'satuan' => $bahanBaku->satuan->nama ?? 'KG', // Satuan utama
                                'harga_satuan' => $pricePerBaseUnit, // Harga per satuan utama
                                'subtotal' => $subtotal
                            ]);
                            
                            // Update moving average harga bahan & stok
                            $stokLama = (float) ($bahanBaku->stok ?? 0);
                            $stokBaru = $stokLama + $qtyInBaseUnit;
                            
                            // Update harga rata-rata menggunakan method dari model
                            if ($stokBaru > 0) {
                                $bahanBaku->updateHargaRataRata($pricePerBaseUnit, $qtyInBaseUnit);
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
            return back()->with('error', 'Minimal harus memilih satu item (Bahan Baku atau Bahan Pendukung)!')->withInput();
        }
    }

    public function edit($id)
    {
        $pembelian = Pembelian::with(['vendor', 'details.bahanBaku.satuan', 'details.bahanPendukung.satuanRelation'])->findOrFail($id);
        $vendors = Vendor::all();
        $bahanBakus = BahanBaku::with('satuan')->get();
        $bahanPendukungs = BahanPendukung::with('satuanRelation')->get();
        $satuans = Satuan::all();
        
        // Ambil data COA untuk kas dan bank (sama seperti create)
        $kasbank = Coa::where('tipe_akun', 'Asset')
            ->where('is_akun_header', '!=', 1)
            ->where(function($query) {
                $query->where('nama_akun', 'like', '%kas%')
                      ->orWhere(function($subQuery) {
                          $subQuery->where('nama_akun', 'like', '%bank%')
                                 ->orWhere('nama_akun', 'like', '%bca%')
                                 ->orWhere('nama_akun', 'like', '%bni%')
                                 ->orWhere('nama_akun', 'like', '%bri%')
                                 ->orWhere('nama_akun', 'like', '%mandiri%');
                      });
            })
            ->where('nama_akun', '!=', '')
            ->where(function($query) {
                $query->whereNot('nama_akun', 'like', '%persediaan%')
                      ->whereNot('nama_akun', 'like', '%inventory%')
                      ->whereNot('nama_akun', 'like', '%stok%')
                      ->whereNot('nama_akun', 'like', '%barang%');
            })
            ->where(function($query) {
                $query->where('kode_akun', 'like', '1%')
                      ->orWhere('kode_akun', 'like', '11%');
            })
            ->orderBy('kode_akun')
            ->get();
        
        // Calculate current balances (sama seperti create)
        $currentBalances = [];
        foreach ($kasbank as $bank) {
            // Use COA saldo_awal as starting point
            $currentBalances[$bank->kode_akun] = $bank->saldo_awal ?? 0;
        }
        
        return view('transaksi.pembelian.edit', compact('pembelian', 'vendors', 'bahanBakus', 'bahanPendukungs', 'satuans', 'kasbank', 'currentBalances'));
    }

    /**
     * Get saldo awal untuk periode tertentu (sesuai LaporanKasBankController)
     */
    private function getSaldoAwal($akun, $startDate)
    {
        // Use COA saldo_awal as starting point (like admin display)
        return $akun->saldo_awal ?? 0;
    }

    /**
     * Get transaksi masuk untuk periode tertentu (sesuai LaporanKasBankController)
     */
    private function getTransaksiMasuk($akun, $startDate, $endDate)
    {
        // Ambil semua journal entry dalam periode
        $journalEntries = \App\Models\JournalEntry::whereBetween('tanggal', [$startDate, $endDate])->pluck('id');
        
        // Ambil journal lines untuk akun ini dengan debit
        $transaksiMasuk = \App\Models\JournalLine::whereIn('journal_entry_id', $journalEntries)
            ->whereHas('account', function($query) use ($akun) {
                $query->where('code', $akun->kode_akun);
            })->where('debit', '>', 0)->sum('debit');
        
        return $transaksiMasuk;
    }

    /**
     * Get transaksi keluar untuk periode tertentu (sesuai LaporanKasBankController)
     */
    private function getTransaksiKeluar($akun, $startDate, $endDate)
    {
        // Ambil semua journal entry dalam periode
        $journalEntries = \App\Models\JournalEntry::whereBetween('tanggal', [$startDate, $endDate])->pluck('id');
        
        // Ambil journal lines untuk akun ini dengan credit
        $transaksiKeluar = \App\Models\JournalLine::whereIn('journal_entry_id', $journalEntries)
            ->whereHas('account', function($query) use ($akun) {
                $query->where('code', $akun->kode_akun);
            })->where('credit', '>', 0)->sum('credit');
        
        return $transaksiKeluar;
    }

    public function update(Request $request, $id)
    {
        // Cek apakah ini pembelian bahan baku atau bahan pendukung
        $isBahanBaku = is_array($request->bahan_baku_id) && count(array_filter($request->bahan_baku_id)) > 0;
        $isBahanPendukung = is_array($request->bahan_pendukung_id) && count(array_filter($request->bahan_pendukung_id)) > 0;
        
        if ($isBahanBaku || $isBahanPendukung) {
            // Validasi dasar
            $rules = [
                'vendor_id' => 'required|exists:vendors,id',
                'tanggal' => 'required|date',
                'bank_id' => 'required',
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
            if ($request->bank_id !== 'credit') {
                $bankId = $request->bank_id;
                
                // Hitung saldo akun yang dipilih
                $saldoAwal = (float) (\App\Models\Coa::where('id', $bankId)->value('saldo_awal') ?? 0);
                $saldoAkun = $saldoAwal;
                
                // Ambil nama akun untuk pesan error
                $namaAkun = \App\Models\Coa::where('id', $bankId)->value('nama_akun') ?? 'Akun '.$bankId;
                
                if ($saldoAkun + 1e-6 < $computedTotal) {
                    return back()->withErrors([
                        'kas' => 'Saldo '.$namaAkun.' tidak cukup untuk pembelian. Saldo saat ini: Rp '.number_format($saldoAkun,0,',','.').' ; Total pembelian: Rp '.number_format($computedTotal,0,',','.'),
                    ])->withInput();
                }
            }

            return DB::transaction(function () use ($request, $computedTotal, $isBahanBaku, $isBahanPendukung) {
                DB::beginTransaction();

                try {
                    $pembelian = Pembelian::findOrFail($id);
                    
                    // Tentukan payment method berdasarkan bank_id
                    $paymentMethod = 'transfer'; // default
                    if ($request->bank_id === 'credit') {
                        $paymentMethod = 'credit';
                    } else {
                        // Cek apakah ini kas atau bank
                        $bank = \App\Models\Coa::find($request->bank_id);
                        if ($bank && (str_contains(strtolower($bank->nama_akun), 'kas') || str_starts_with($bank->kode_akun, '1'))) {
                            $paymentMethod = 'cash';
                        } else {
                            $paymentMethod = 'transfer';
                        }
                    }
                    
                    // Tentukan tipe pembelian berdasarkan vendor
                    $vendor = Vendor::find($request->vendor_id);
                    $tipePembelian = $vendor->kategori ?? 'Bahan Baku';
                    
                    // Update pembelian data
                    \Log::info('UPDATE STEP 1 - Before update', [
                        'pembelian_id' => $pembelian->id,
                        'old_total_harga' => $pembelian->total_harga,
                        'new_computed_total' => $computedTotal,
                    ]);
                    
                    $pembelian->update([
                        'vendor_id' => $request->vendor_id,
                        'nomor_faktur' => $request->nomor_faktur,
                        'tanggal' => $request->tanggal,
                        'total_harga' => $computedTotal,
                        'terbayar' => ($paymentMethod === 'cash' || $paymentMethod === 'transfer') ? $computedTotal : 0,
                        'sisa_pembayaran' => ($paymentMethod === 'cash' || $paymentMethod === 'transfer') ? 0 : $computedTotal,
                        'status' => ($paymentMethod === 'cash' || $paymentMethod === 'transfer') ? 'lunas' : 'belum_lunas',
                        'payment_method' => $paymentMethod,
                        'bank_id' => $request->bank_id === 'credit' ? null : $request->bank_id,
                        'keterangan' => $request->keterangan,
                    ]);
                    
                    \Log::info('UPDATE STEP 2 - After first update', [
                        'pembelian_id' => $pembelian->id,
                        'total_harga_after_update' => $pembelian->total_harga,
                    ]);

                    // Delete existing details
                    PembelianDetail::where('pembelian_id', $pembelian->id)->delete();

                    $actualTotal = 0;

                    // Proses bahan baku
                    if ($isBahanBaku) {
                        foreach ($request->bahan_baku_id as $i => $bbId) {
                            if (empty($bbId)) continue;
                            
                            $bahanBaku = BahanBaku::findOrFail($bbId);
                            
                            // Get input values
                            $qtyInput = (float) ($request->jumlah[$i] ?? 0);
                            $satuanPembelian = strtolower(trim($request->satuan_pembelian[$i] ?? ''));
                            $hargaPembelian = (float) ($request->harga_satuan_pembelian[$i] ?? 0);
                            
                            // Konversi ke satuan utama menggunakan method dari model
                            $konversiResult = $bahanBaku->konversiKeSatuanUtama($hargaPembelian, $satuanPembelian, $qtyInput);
                            $qtyInBaseUnit = $konversiResult['quantity'];
                            $pricePerBaseUnit = $konversiResult['harga_per_satuan_utama'];
                            
                            // Create detail
                            PembelianDetail::create([
                                'pembelian_id' => $pembelian->id,
                                'bahan_baku_id' => $bbId,
                                'jumlah' => $qtyInBaseUnit,
                                'harga_satuan' => $pricePerBaseUnit,
                            ]);
                            
                            // Tambah ke actual total (gunakan harga yang disimpan)
                            $actualTotal += $qtyInBaseUnit * $pricePerBaseUnit;
                        }
                    }

                    // Proses bahan pendukung
                    if ($isBahanPendukung) {
                        foreach ($request->bahan_pendukung_id as $i => $bpId) {
                            if (empty($bpId)) continue;
                            
                            $bahanPendukung = BahanPendukung::findOrFail($bpId);
                            
                            // Get input values
                            $qtyInput = (float) ($request->jumlah_pendukung[$i] ?? 0);
                            $hargaPembelian = (float) ($request->harga_satuan_pendukung[$i] ?? 0);
                            
                            // Create detail
                            PembelianDetail::create([
                                'pembelian_id' => $pembelian->id,
                                'bahan_pendukung_id' => $bpId,
                                'jumlah' => $qtyInput,
                                'harga_satuan' => $hargaPembelian,
                            ]);
                            
                            // Tambah ke actual total (gunakan harga yang disimpan)
                            $actualTotal += $qtyInput * $hargaPembelian;
                        }
                    }

                    // Update total_harga dengan actual total (untuk konsistensi)
                    \Log::info('UPDATE STEP 3 - Before final update', [
                        'pembelian_id' => $pembelian->id,
                        'actual_total' => $actualTotal,
                        'computed_total' => $computedTotal,
                        'current_total_harga' => $pembelian->total_harga,
                    ]);
                    
                    $pembelian->update(['total_harga' => $actualTotal]);
                    
                    \Log::info('UPDATE STEP 4 - After final update', [
                        'pembelian_id' => $pembelian->id,
                        'final_total_harga' => $pembelian->total_harga,
                        'actual_total' => $actualTotal,
                        'computed_total' => $computedTotal,
                    ]);

                    DB::commit();

                    // Debug logging
                    \Log::info('Pembelian updated successfully', [
                        'pembelian_id' => $pembelian->id,
                        'computed_total' => $computedTotal,
                        'actual_total' => $actualTotal,
                        'payment_method' => $paymentMethod,
                        'details_count' => $pembelian->details()->count()
                    ]);

                    return redirect()->route('transaksi.pembelian.index')->with('success', 'Data pembelian berhasil diperbarui!');
                } catch (\Exception $e) {
                    DB::rollBack();
                    return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
                }
            });
        } else {
            return redirect()->back()->with('error', 'Minimal harus memilih satu item (Bahan Baku atau Bahan Pendukung)!')->withInput();
        }
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
