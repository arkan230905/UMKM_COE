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
        $query = Pembelian::with(['vendor', 'details.bahanBaku.satuan', 'details.bahanPendukung.satuan', 'details.satuanRelation']);
        
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
        $pembelian = Pembelian::with([
            'vendor', 
            'details.bahanBaku.satuan',
            'details.bahanBaku.subSatuan1',
            'details.bahanBaku.subSatuan2', 
            'details.bahanBaku.subSatuan3',
            'details.bahanBaku.coaPembelian',
            'details.bahanPendukung.satuanRelation',
            'details.bahanPendukung.subSatuan1',
            'details.bahanPendukung.subSatuan2',
            'details.bahanPendukung.subSatuan3',
            'details.bahanPendukung.coaPembelian',
            'details.satuanRelation',
            'details.konversiManual.satuan'
        ])->findOrFail($id);
        return view('transaksi.pembelian.show', compact('pembelian'));
    }

    public function create()
    {
        $vendors = Vendor::all();
        $bahanBakus = BahanBaku::with([
            'satuan', 
            'subSatuan1', 
            'subSatuan2', 
            'subSatuan3'
        ])->get();
        $bahanPendukungs = \App\Models\BahanPendukung::with([
            'satuanRelation', 
            'subSatuan1', 
            'subSatuan2', 
            'subSatuan3'
        ])->get();
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
            
        // Hitung saldo real-time untuk setiap akun kas/bank
        $startDate = now()->startOfMonth()->format('Y-m-d');
        $endDate = now()->endOfMonth()->format('Y-m-d');
        
        foreach ($kasbank as $akun) {
            // Gunakan method helper lokal
            $saldoAwal = $this->getSaldoAwalHelper($akun, $startDate);
            $transaksiMasuk = $this->getTransaksiMasukHelper($akun, $startDate, $endDate);
            $transaksiKeluar = $this->getTransaksiKeluarHelper($akun, $startDate, $endDate);
            
            // Saldo akhir real-time
            $akun->saldo_realtime = $saldoAwal + $transaksiMasuk - $transaksiKeluar;
        }
            
        return view('transaksi.pembelian.create', compact('vendors', 'bahanBakus', 'bahanPendukungs', 'satuans', 'kasbank'));
    }
    
    /**
     * Helper method untuk getSaldoAwal (copy dari LaporanKasBankController)
     */
    private function getSaldoAwalHelper($akun, $startDate)
    {
        // 1. Cari periode yang sesuai dengan start date
        $periode = \App\Models\CoaPeriod::where('periode', date('Y-m', strtotime($startDate)))->first();
        
        if ($periode) {
            // 2. Cari saldo di CoaPeriodBalance untuk periode tersebut
            $balance = \App\Models\CoaPeriodBalance::where('period_id', $periode->id)
                ->where('kode_akun', $akun->kode_akun)
                ->first();
                
            if ($balance) {
                return (float) $balance->saldo_awal;
            }
        }
        
        // 3. Fallback ke saldo awal COA
        return (float) ($akun->saldo_awal ?? 0);
    }
    
    /**
     * Helper method untuk getTransaksiMasuk (copy dari LaporanKasBankController)
     */
    private function getTransaksiMasukHelper($akun, $startDate, $endDate)
    {
        $totalMasuk = 0;
        
        // 1. Penjualan (cash/transfer masuk ke kas/bank)
        $penjualanMasuk = DB::table('penjualans')
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->where(function($query) use ($akun) {
                $query->where(function($subQuery) use ($akun) {
                    // Jika akun adalah Kas (mengandung kata 'kas')
                    if (stripos($akun->nama_akun, 'kas') !== false) {
                        $subQuery->where('payment_method', 'cash');
                    }
                    // Jika akun adalah Bank (mengandung kata 'bank')
                    elseif (stripos($akun->nama_akun, 'bank') !== false) {
                        $subQuery->where('payment_method', 'transfer');
                    }
                });
            })
            ->sum('total');
            
        $totalMasuk += (float) ($penjualanMasuk ?? 0);
        
        // 2. Pelunasan Utang (pembayaran utang masuk ke kas/bank)
        try {
            $pelunasanUtangMasuk = DB::table('pelunasan_utangs')
                ->whereBetween('tanggal', [$startDate, $endDate])
                ->where(function($query) use ($akun) {
                    $query->where(function($subQuery) use ($akun) {
                        // Jika akun adalah Kas (mengandung kata 'kas')
                        if (stripos($akun->nama_akun, 'kas') !== false) {
                            $subQuery->where('metode_bayar', 'tunai');
                        }
                        // Jika akun adalah Bank (mengandung kata 'bank')
                        elseif (stripos($akun->nama_akun, 'bank') !== false) {
                            $subQuery->where('metode_bayar', 'transfer');
                        }
                    });
                })
                ->sum('dibayar_bersih');
                
            $totalMasuk += (float) ($pelunasanUtangMasuk ?? 0);
        } catch (\Exception $e) {
            // Tabel tidak ada, skip
        }
        
        return $totalMasuk;
    }
    
    /**
     * Helper method untuk getTransaksiKeluar (copy dari LaporanKasBankController)
     */
    private function getTransaksiKeluarHelper($akun, $startDate, $endDate)
    {
        $totalKeluar = 0;
        
        // Prioritas 1: Ambil dari journal lines (jurnal akuntansi)
        try {
            $journalKeluar = DB::table('journal_lines')
                ->join('journal_entries', 'journal_lines.journal_entry_id', '=', 'journal_entries.id')
                ->where('journal_lines.coa_id', $akun->id)
                ->where('journal_lines.credit', '>', 0)
                ->whereBetween('journal_entries.tanggal', [$startDate, $endDate])
                ->sum('journal_lines.credit');
                
            $totalKeluar += (float) ($journalKeluar ?? 0);
        } catch (\Exception $e) {
            // Skip journal errors, fallback to direct transactions
        }
        
        // Prioritas 2: Ambil dari transaksi langsung (jika journal tidak ada)
        // 1. Pembelian (cash/transfer keluar dari kas/bank ke persediaan)
        $pembelianKeluar = DB::table('pembelians')
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->where(function($query) use ($akun) {
                $query->where(function($subQuery) use ($akun) {
                    // Jika akun adalah Kas (mengandung kata 'kas')
                    if (stripos($akun->nama_akun, 'kas') !== false) {
                        $subQuery->where('payment_method', 'cash');
                    }
                    // Jika akun adalah Bank (mengandung kata 'bank')
                    elseif (stripos($akun->nama_akun, 'bank') !== false) {
                        $subQuery->where('payment_method', 'transfer');
                    }
                });
            })
            ->sum('total_harga');
            
        $totalKeluar += (float) ($pembelianKeluar ?? 0);
        
        return $totalKeluar;
    }
    
    /**
     * Helper method untuk mapCoaToAccountCode (copy dari LaporanKasBankController)
     */
    private function mapCoaToAccountCodeHelper($coaCode)
    {
        $mapping = [
            '1110' => '101',  // Kas COA -> Kas Account
            '1120' => '102',  // Bank COA -> Bank Account
            '101' => '101',  // Direct mapping
            '102' => '102',  // Direct mapping
        ];
        
        return $mapping[$coaCode] ?? $coaCode;
    }

        public function store(Request $request, StockService $stock, JournalService $journal)
    {
        // Debug: Log data yang diterima
        \Log::info('Pembelian form data received:', [
            'item_id' => $request->item_id,
            'tipe_item' => $request->tipe_item,
            'jumlah' => $request->jumlah,
            'satuan_pembelian' => $request->satuan_pembelian,
            'harga_satuan' => $request->harga_satuan,
            'subtotal' => $request->subtotal,
            'faktor_konversi' => $request->faktor_konversi,
            'vendor_id' => $request->vendor_id,
            'tanggal' => $request->tanggal,
            'bank_id' => $request->bank_id,
            'request_method' => $request->method(),
            'content_type' => $request->header('Content-Type'),
        ]);
        
        // Validasi dasar
        $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'tanggal' => 'required|date',
            'bank_id' => 'required',
        ]);
        
        // Cek manual apakah ada item yang dipilih
        $hasValidItems = false;
        if ($request->has('item_id') && is_array($request->item_id)) {
            foreach ($request->item_id as $index => $itemId) {
                if (!empty($itemId) && !empty($request->tipe_item[$index] ?? '')) {
                    $hasValidItems = true;
                    break;
                }
            }
        }
        
        if (!$hasValidItems) {
            return back()->with('error', 'Minimal harus memilih satu item (Bahan Baku atau Bahan Pendukung)!')->withInput();
        }

            // Hitung total terlebih dahulu untuk validasi kas
            $subtotal = 0.0;
            $biayaKirim = (float) ($request->biaya_kirim ?? 0);
            $ppnPersen = (float) ($request->ppn_persen ?? 0);
            
            \Log::info('Calculation debug:', [
                'biaya_kirim' => $biayaKirim,
                'ppn_persen' => $ppnPersen,
                'request_biaya_kirim' => $request->biaya_kirim,
                'request_ppn_persen' => $request->ppn_persen,
            ]);
            
            // Hitung subtotal dari item yang dipilih
            foreach ($request->item_id as $i => $itemId) {
                if (!empty($itemId) && !empty($request->tipe_item[$i])) {
                    $hargaTotal = (float) ($request->subtotal[$i] ?? 0);
                    $subtotal += $hargaTotal;
                    
                    \Log::info("Item $i calculation:", [
                        'item_id' => $itemId,
                        'subtotal_raw' => $request->subtotal[$i] ?? 'null',
                        'subtotal_float' => $hargaTotal,
                        'running_subtotal' => $subtotal,
                    ]);
                }
            }
            
            // Hitung PPN dari (subtotal + biaya kirim)
            $basePPN = $subtotal + $biayaKirim;
            $ppnNominal = $basePPN * ($ppnPersen / 100);
            
            // Total akhir = subtotal + biaya kirim + PPN
            $computedTotal = $subtotal + $biayaKirim + $ppnNominal;
            
            \Log::info('Final calculation:', [
                'subtotal' => $subtotal,
                'biaya_kirim' => $biayaKirim,
                'ppn_persen' => $ppnPersen,
                'base_ppn' => $basePPN,
                'ppn_nominal' => $ppnNominal,
                'computed_total' => $computedTotal,
            ]);

            // Cek saldo kas jika pembayaran tunai atau transfer
            if ($request->bank_id !== 'credit') {
                $bankId = $request->bank_id;
                $bank = \App\Models\Coa::find($bankId);
                
                if ($bank) {
                    // Hitung saldo real-time menggunakan journal entries (lebih akurat)
                    $journalLines = \App\Models\JournalLine::where('coa_id', $bank->id)
                        ->with('entry')
                        ->get();
                    
                    $totalDebit = $journalLines->sum('debit');
                    $totalCredit = $journalLines->sum('credit');
                    
                    // Hitung saldo berdasarkan saldo normal akun
                    if ($bank->saldo_normal == 'debit') {
                        $saldoRealtime = $bank->saldo_awal + $totalDebit - $totalCredit;
                    } else {
                        $saldoRealtime = $bank->saldo_awal + $totalCredit - $totalDebit;
                    }
                    
                    \Log::info('Balance validation:', [
                        'account' => $bank->nama_akun,
                        'initial_balance' => $bank->saldo_awal,
                        'total_debit' => $totalDebit,
                        'total_credit' => $totalCredit,
                        'current_balance' => $saldoRealtime,
                        'required_amount' => $computedTotal,
                        'sufficient' => $saldoRealtime >= $computedTotal
                    ]);
                    
                    if ($saldoRealtime + 1e-6 < $computedTotal) {
                        return back()->withErrors([
                            'kas' => 'Saldo '.$bank->nama_akun.' tidak cukup untuk pembelian. Saldo saat ini: Rp '.number_format($saldoRealtime,0,',','.').' ; Total pembelian: Rp '.number_format($computedTotal,0,',','.'),
                        ])->withInput();
                    }
                }
            }

            return DB::transaction(function () use ($request, $stock, $journal, $computedTotal, $subtotal, $biayaKirim, $ppnPersen, $ppnNominal) {
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
                        'subtotal' => $subtotal,
                        'biaya_kirim' => $biayaKirim,
                        'ppn_persen' => $ppnPersen,
                        'ppn_nominal' => $ppnNominal,
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

                    // Proses semua item yang dipilih
                    foreach ($request->item_id as $i => $itemId) {
                        if (empty($itemId) || empty($request->tipe_item[$i])) continue;
                        
                        $tipeItem = $request->tipe_item[$i];
                        $qtyInput = (float) ($request->jumlah[$i] ?? 0);
                        $satuanPembelian = $request->satuan_pembelian[$i] ?? '';
                        $hargaSatuan = (float) ($request->harga_satuan[$i] ?? 0);
                        $hargaTotal = (float) ($request->subtotal[$i] ?? 0);
                        $faktorKonversi = (float) ($request->faktor_konversi[$i] ?? 1);
                        
                        // Hitung jumlah dalam satuan utama
                        $qtyInBaseUnit = $qtyInput * $faktorKonversi;
                        
                        // Hitung harga per satuan utama
                        $pricePerBaseUnit = $qtyInBaseUnit > 0 ? $hargaTotal / $qtyInBaseUnit : 0;
                            
                            if ($tipeItem === 'bahan_baku') {
                                $bahanBaku = BahanBaku::findOrFail($itemId);
                                $totalBahanBaku += $hargaTotal;
                                
                                // Debug: Log data sebelum disimpan
                                \Log::info("Saving PembelianDetail for item $i:", [
                                    'pembelian_id' => $pembelian->id,
                                    'bahan_baku_id' => $itemId,
                                    'jumlah' => $qtyInput,
                                    'satuan' => $satuanPembelian,
                                    'harga_satuan' => $hargaSatuan,
                                    'subtotal' => $hargaTotal,
                                    'faktor_konversi' => $faktorKonversi,
                                    'calculated_subtotal' => $qtyInput * $hargaSatuan,
                                ]);
                                
                                // SIMPAN DETAIL PEMBELIAN KE DATABASE
                                $detail = PembelianDetail::create([
                                    'pembelian_id' => $pembelian->id,
                                    'bahan_baku_id' => $itemId,
                                    'jumlah' => $qtyInput,
                                    'satuan' => $satuanPembelian,
                                    'harga_satuan' => $hargaSatuan,
                                    'subtotal' => $hargaTotal,
                                    'faktor_konversi' => $faktorKonversi
                                ]);
                                
                                // Debug: Log data setelah disimpan
                                \Log::info("PembelianDetail saved with ID: " . $detail->id, [
                                    'saved_jumlah' => $detail->jumlah,
                                    'saved_harga_satuan' => $detail->harga_satuan,
                                    'saved_subtotal' => $detail->subtotal,
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
                                
                            } elseif ($tipeItem === 'bahan_pendukung') {
                                $bahanPendukung = \App\Models\BahanPendukung::findOrFail($itemId);
                                $totalBahanPendukung += $hargaTotal;
                                
                                // SIMPAN DETAIL PEMBELIAN KE DATABASE
                                $detail = PembelianDetail::create([
                                    'pembelian_id' => $pembelian->id,
                                    'bahan_baku_id' => null,
                                    'bahan_pendukung_id' => $itemId,
                                    'jumlah' => $qtyInput,
                                    'satuan' => $satuanPembelian,
                                    'harga_satuan' => $hargaSatuan,
                                    'subtotal' => $hargaTotal,
                                    'faktor_konversi' => $faktorKonversi
                                ]);
                                
                                // Update moving average harga bahan & stok
                                $stokLama = (float) ($bahanPendukung->stok ?? 0);
                                $stokBaru = $stokLama + $qtyInBaseUnit;
                                
                                // Update harga rata-rata
                                if ($stokBaru > 0) {
                                    $hargaLama = (float) ($bahanPendukung->harga_satuan ?? 0);
                                    $hargaBaru = (($stokLama * $hargaLama) + $hargaTotal) / $stokBaru;
                                    $bahanPendukung->harga_satuan = $hargaBaru;
                                }

                                $bahanPendukung->stok = $stokBaru;
                                $bahanPendukung->save();

                                // FIFO layer IN + movement untuk bahan pendukung
                                $unitStr = (string)($bahanPendukung->satuanRelation->nama ?? $bahanPendukung->satuan ?? 'pcs');
                                $stock->addLayer('support', $bahanPendukung->id, $qtyInBaseUnit, $unitStr, $pricePerBaseUnit, 'purchase', $pembelian->id, $request->tanggal);
                            }
                    }

                    // Commit transaksi database
                    // (DB::transaction handles this automatically)
                    
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

                // Create journal entries for accounting integration
                try {
                    \App\Services\JournalService::createJournalFromPembelian($pembelian);
                    \Log::info('Journal entries created successfully for pembelian', [
                        'pembelian_id' => $pembelian->id,
                    ]);
                } catch (\Exception $e) {
                    \Log::error('Failed to create journal entries for pembelian', [
                        'pembelian_id' => $pembelian->id,
                        'error' => $e->getMessage(),
                    ]);
                    // Don't fail the transaction, just log the error
                }

                return redirect()->route('transaksi.pembelian.index')
                    ->with('success', 'Data pembelian berhasil disimpan!');
            });
    }

    public function edit($id)
    {
        $pembelian = Pembelian::with(['vendor', 'details.bahanBaku.satuan', 'details.bahanPendukung.satuan'])->findOrFail($id);
        
        $vendors = Vendor::orderBy('nama_vendor')->get();
        $produks = Produk::orderBy('nama_produk')->get();
        $bahanBakus = BahanBaku::with('satuan')->orderBy('nama_bahan')->get();
        $bahanPendukungs = BahanPendukung::with('satuan')->orderBy('nama_bahan')->get();
        $coas = Coa::orderBy('nama_akun')->get();
        
        // Filter COA untuk kas/bank
        $kasbank = $coas->filter(function($coa) {
            return in_array($coa->kategori_akun, ['Bank', 'Kas']) || 
                   str_contains(strtolower($coa->nama_akun), 'kas') ||
                   str_contains(strtolower($coa->nama_akun), 'bank');
        });
        
        // Get current balances for kasbank accounts
        $currentBalances = [];
        foreach ($kasbank as $bank) {
            // You might need to adjust this based on your balance calculation logic
            $currentBalances[$bank->kode_akun] = $bank->saldo_awal ?? 0;
        }
        
        return view('transaksi.pembelian.edit', compact(
            'pembelian',
            'vendors',
            'produks', 
            'bahanBakus',
            'bahanPendukungs',
            'coas',
            'kasbank',
            'currentBalances'
        ));
    }

    public function update(Request $request, $id)
    {
        $pembelian = Pembelian::findOrFail($id);
        // Hapus jurnal terkait pembelian
        $journal->deleteByRef('purchase', (int)$pembelian->id);
        // Hapus data
        $pembelian->delete();

        return redirect()->route('transaksi.pembelian.index')->with('success', 'Data pembelian dan jurnal terkait berhasil dihapus!');
    }
    
    /**
     * Helper method untuk menyimpan konversi manual sub satuan
     */
    private function simpanKonversiManual($detailId, $request, $index, $tipe)
    {
        // Tentukan prefix berdasarkan tipe
        $prefix = $tipe === 'bahan_baku' ? '' : '_pendukung';
        
        // Simpan konversi sub satuan 1
        if (isset($request->{"konversi_sub_1{$prefix}"}[$index]) && 
            !empty($request->{"konversi_sub_1{$prefix}"}[$index]) &&
            isset($request->{"konversi_sub_1{$prefix}_id"}[$index])) {
            
            $faktorKonversi = isset($request->{"faktor_konversi_1{$prefix}"}[$index]) ? 
                (float) $request->{"faktor_konversi_1{$prefix}"}[$index] : null;
            
            \App\Models\PembelianDetailKonversi::create([
                'pembelian_detail_id' => $detailId,
                'satuan_id' => $request->{"konversi_sub_1{$prefix}_id"}[$index],
                'satuan_nama' => $request->{"konversi_sub_1{$prefix}_nama"}[$index] ?? '',
                'jumlah_konversi' => (float) $request->{"konversi_sub_1{$prefix}"}[$index],
                'faktor_konversi_manual' => $faktorKonversi,
                'keterangan' => 'Konversi manual sub satuan 1'
            ]);
        }
        
        // Simpan konversi sub satuan 2
        if (isset($request->{"konversi_sub_2{$prefix}"}[$index]) && 
            !empty($request->{"konversi_sub_2{$prefix}"}[$index]) &&
            isset($request->{"konversi_sub_2{$prefix}_id"}[$index])) {
            
            $faktorKonversi = isset($request->{"faktor_konversi_2{$prefix}"}[$index]) ? 
                (float) $request->{"faktor_konversi_2{$prefix}"}[$index] : null;
            
            \App\Models\PembelianDetailKonversi::create([
                'pembelian_detail_id' => $detailId,
                'satuan_id' => $request->{"konversi_sub_2{$prefix}_id"}[$index],
                'satuan_nama' => $request->{"konversi_sub_2{$prefix}_nama"}[$index] ?? '',
                'jumlah_konversi' => (float) $request->{"konversi_sub_2{$prefix}"}[$index],
                'faktor_konversi_manual' => $faktorKonversi,
                'keterangan' => 'Konversi manual sub satuan 2'
            ]);
        }
        
        // Simpan konversi sub satuan 3
        if (isset($request->{"konversi_sub_3{$prefix}"}[$index]) && 
            !empty($request->{"konversi_sub_3{$prefix}"}[$index]) &&
            isset($request->{"konversi_sub_3{$prefix}_id"}[$index])) {
            
            $faktorKonversi = isset($request->{"faktor_konversi_3{$prefix}"}[$index]) ? 
                (float) $request->{"faktor_konversi_3{$prefix}"}[$index] : null;
            
            \App\Models\PembelianDetailKonversi::create([
                'pembelian_detail_id' => $detailId,
                'satuan_id' => $request->{"konversi_sub_3{$prefix}_id"}[$index],
                'satuan_nama' => $request->{"konversi_sub_3{$prefix}_nama"}[$index] ?? '',
                'jumlah_konversi' => (float) $request->{"konversi_sub_3{$prefix}"}[$index],
                'faktor_konversi_manual' => $faktorKonversi,
                'keterangan' => 'Konversi manual sub satuan 3'
            ]);
        }
    }
    
    /**
     * Remove the specified pembelian from storage.
     * This will cascade delete all related data to maintain data integrity.
     */
    /**
     * Remove the specified pembelian from storage.
     * This will cascade delete all related data to maintain data integrity.
     */
    public function destroy($id)
    {
        try {
            \DB::beginTransaction();
            
            // Find pembelian including soft deleted ones
            $pembelian = \App\Models\Pembelian::withTrashed()->with([
                'pembelianDetails',
                'pelunasan',
                'apSettlements'
            ])->findOrFail($id);
            
            // Log the deletion attempt
            \Log::info('Attempting to delete pembelian', [
                'id' => $pembelian->id,
                'nomor_pembelian' => $pembelian->nomor_pembelian,
                'total_harga' => $pembelian->total_harga,
                'user_id' => auth()->id()
            ]);
            
            // 1. Delete related PelunasanUtang records using raw SQL for safety
            \DB::table('pelunasan_utangs')->where('pembelian_id', $pembelian->id)->delete();
            \Log::info('Deleted pelunasan utang records');
            
            // 2. Delete related ApSettlement records
            \DB::table('ap_settlements')->where('pembelian_id', $pembelian->id)->delete();
            \Log::info('Deleted AP settlement records');
            
            // 3. Delete related PurchaseReturn records
            $purchaseReturnIds = \DB::table('purchase_returns')
                ->where('pembelian_id', $pembelian->id)
                ->pluck('id');
            
            if ($purchaseReturnIds->count() > 0) {
                // Delete return items first
                \DB::table('purchase_return_items')
                    ->whereIn('purchase_return_id', $purchaseReturnIds)
                    ->delete();
                
                // Delete returns
                \DB::table('purchase_returns')
                    ->where('pembelian_id', $pembelian->id)
                    ->delete();
                    
                \Log::info('Deleted purchase return records', ['count' => $purchaseReturnIds->count()]);
            }
            
            // 4. Delete related Retur records
            \DB::table('returs')->where('pembelian_id', $pembelian->id)->delete();
            \Log::info('Deleted retur records');
            
            // 5. Reverse stock movements for each pembelian detail
            foreach ($pembelian->pembelianDetails as $detail) {
                $itemId = $detail->bahan_baku_id ?? $detail->bahan_pendukung_id;
                
                if ($itemId) {
                    // Update stock in bahan_bakus or bahan_pendukungs table
                    if ($detail->bahan_baku_id) {
                        \DB::table('bahan_bakus')
                            ->where('id', $detail->bahan_baku_id)
                            ->decrement('stok_tersedia', $detail->jumlah);
                    } elseif ($detail->bahan_pendukung_id) {
                        \DB::table('bahan_pendukungs')
                            ->where('id', $detail->bahan_pendukung_id)
                            ->decrement('stok_tersedia', $detail->jumlah);
                    }
                    
                    // Create reverse stock movement
                    \DB::table('stock_movements')->insert([
                        'item_type' => 'material',
                        'item_id' => $itemId,
                        'direction' => 'out',
                        'qty' => $detail->jumlah,
                        'unit_cost' => $detail->harga_satuan,
                        'tanggal' => now(),
                        'keterangan' => 'Pembatalan pembelian #' . $pembelian->nomor_pembelian,
                        'ref_type' => 'pembelian_cancellation',
                        'ref_id' => $pembelian->id,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }
            
            // 6. Delete journal entries related to this pembelian
            $journalEntryIds = \DB::table('journal_entries')
                ->where('ref_type', 'pembelian')
                ->where('ref_id', $pembelian->id)
                ->pluck('id');
                
            if ($journalEntryIds->count() > 0) {
                // Delete journal lines first
                \DB::table('journal_lines')
                    ->whereIn('journal_entry_id', $journalEntryIds)
                    ->delete();
                
                // Delete journal entries
                \DB::table('journal_entries')
                    ->where('ref_type', 'pembelian')
                    ->where('ref_id', $pembelian->id)
                    ->delete();
                    
                \Log::info('Deleted journal entries', ['count' => $journalEntryIds->count()]);
            }
            
            // 7. Delete pembelian detail konversi records
            $detailIds = $pembelian->pembelianDetails->pluck('id');
            if ($detailIds->count() > 0) {
                \DB::table('pembelian_detail_konversis')
                    ->whereIn('pembelian_detail_id', $detailIds)
                    ->delete();
            }
            
            // 8. Delete pembelian details
            \DB::table('pembelian_details')->where('pembelian_id', $pembelian->id)->delete();
            \Log::info('Deleted pembelian details');
            
            // 9. Finally delete the pembelian record permanently
            \DB::table('pembelians')->where('id', $pembelian->id)->delete();
            
            \DB::commit();
            
            \Log::info('Pembelian successfully deleted', [
                'id' => $id,
                'nomor_pembelian' => $pembelian->nomor_pembelian
            ]);
            
            return redirect()->route('transaksi.pembelian.index')
                ->with('success', 'Pembelian berhasil dihapus beserta semua data terkaitnya.');
                
        } catch (\Exception $e) {
            \DB::rollBack();
            
            \Log::error('Error deleting pembelian', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('error', 'Gagal menghapus pembelian: ' . $e->getMessage());
        }
    }
}