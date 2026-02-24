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
        $pembelian = Pembelian::with([
            'vendor', 
            'details.bahanBaku.satuan',
            'details.bahanBaku.coaPembelian',
            'details.bahanPendukung.satuan',
            'details.bahanPendukung.coaPembelian'
        ])->findOrFail($id);
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
            $accountCode = $this->mapCoaToAccountCodeHelper($akun->kode_akun);
            $account = DB::table('accounts')->where('code', $accountCode)->first();
            
            if ($account) {
                $journalKeluar = DB::table('journal_lines')
                    ->join('journal_entries', 'journal_lines.journal_entry_id', '=', 'journal_entries.id')
                    ->where('journal_lines.account_id', $account->id)
                    ->where('journal_lines.credit', '>', 0)
                    ->whereBetween('journal_entries.tanggal', [$startDate, $endDate])
                    ->sum('journal_lines.credit');
                    
                $totalKeluar += (float) ($journalKeluar ?? 0);
            }
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
            
            // Total dari bahan pendukung (gunakan harga total)
            if ($isBahanPendukung) {
                foreach ($request->bahan_pendukung_id as $i => $bpId) {
                    if (empty($bpId)) continue;
                    $hargaTotal = (float) ($request->harga_total_pendukung[$i] ?? 0);
                    $computedTotal += $hargaTotal;
                }
            }

            // Cek saldo kas jika pembayaran tunai atau transfer
            if ($request->bank_id !== 'credit') {
                $bankId = $request->bank_id;
                $bank = \App\Models\Coa::find($bankId);
                
                if ($bank) {
                    // Hitung saldo real-time akun yang dipilih menggunakan helper methods
                    $startDate = now()->startOfMonth()->format('Y-m-d');
                    $endDate = now()->endOfMonth()->format('Y-m-d');
                    
                    $saldoAwal = $this->getSaldoAwalHelper($bank, $startDate);
                    $transaksiMasuk = $this->getTransaksiMasukHelper($bank, $startDate, $endDate);
                    $transaksiKeluar = $this->getTransaksiKeluarHelper($bank, $startDate, $endDate);
                    $saldoRealtime = $saldoAwal + $transaksiMasuk - $transaksiKeluar;
                    
                    if ($saldoRealtime + 1e-6 < $computedTotal) {
                        return back()->withErrors([
                            'kas' => 'Saldo '.$bank->nama_akun.' tidak cukup untuk pembelian. Saldo saat ini: Rp '.number_format($saldoRealtime,0,',','.').' ; Total pembelian: Rp '.number_format($computedTotal,0,',','.'),
                        ])->withInput();
                    }
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
                            
                            // Hitung harga per satuan utama
                            $pricePerBaseUnit = $qtyInBaseUnit > 0 ? $hargaTotal / $qtyInBaseUnit : 0;
                            
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
                            $hargaTotal = (float) ($request->harga_total_pendukung[$i] ?? 0);
                            $qtyInBaseUnit = (float) ($request->jumlah_satuan_utama_pendukung[$i] ?? 0);
                            
                            // Hitung harga per satuan utama
                            $pricePerBaseUnit = $qtyInBaseUnit > 0 ? $hargaTotal / $qtyInBaseUnit : 0;
                            
                            $subtotal = $hargaTotal;
                            $totalBahanPendukung += $subtotal;

                            // SIMPAN DETAIL PEMBELIAN KE DATABASE
                            $detail = PembelianDetail::create([
                                'pembelian_id' => $pembelian->id,
                                'bahan_baku_id' => null,
                                'bahan_pendukung_id' => $bpId,
                                'jumlah' => $qtyInBaseUnit, // Simpan dalam satuan utama
                                'satuan' => $bahanPendukung->satuan->nama ?? 'pcs',
                                'harga_satuan' => $pricePerBaseUnit,
                                'subtotal' => $subtotal,
                            ]);
                            
                            // Update moving average harga bahan & stok
                            $stokLama = (float) ($bahanPendukung->stok ?? 0);
                            $stokBaru = $stokLama + $qtyInBaseUnit;
                            
                            // Update harga rata-rata
                            if ($stokBaru > 0) {
                                $hargaLama = (float) ($bahanPendukung->harga_satuan ?? 0);
                                $hargaBaru = (($stokLama * $hargaLama) + $subtotal) / $stokBaru;
                                $bahanPendukung->harga_satuan = $hargaBaru;
                            }

                            $bahanPendukung->stok = $stokBaru;
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
                    
                    // Buat jurnal akuntansi untuk pembelian cash/transfer
                    if ($paymentMethod === 'cash' || $paymentMethod === 'transfer') {
                        $journalData = [
                            'tanggal' => $request->tanggal,
                            'keterangan' => 'Pembelian ' . ucfirst($paymentMethod) . ' - ' . $pembelian->nomor_pembelian,
                            'ref_id' => $pembelian->id,
                            'ref_type' => 'pembelian',
                            'details' => []
                        ];
                        
                        // Debit: Kas/Bank (uang keluar)
                        $debitCoa = Coa::find($request->bank_id);
                        if ($debitCoa) {
                            $journalData['details'][] = [
                                'coa_id' => $debitCoa->id,
                                'debit' => $computedTotal,
                                'credit' => 0,
                                'keterangan' => 'Pembelian ' . ucfirst($paymentMethod) . ' - ' . ($vendor->nama_vendor ?? 'Vendor')
                            ];
                        }
                        
                        // Credit: Persediaan (barang masuk)
                        // Ambil COA persediaan dari detail pembelian
                        $details = PembelianDetail::with(['bahanBaku.coa', 'bahanPendukung.coa'])
                            ->where('pembelian_id', $pembelian->id)
                            ->get();
                            
                        foreach ($details as $detail) {
                            $coa = null;
                            if ($detail->bahanBaku && $detail->bahanBaku->coa) {
                                $coa = $detail->bahanBaku->coa;
                            } elseif ($detail->bahanPendukung && $detail->bahanPendukung->coa) {
                                $coa = $detail->bahanPendukung->coa;
                            }
                            
                            if ($coa) {
                                $journalData['details'][] = [
                                    'coa_id' => $coa->id,
                                    'debit' => 0,
                                    'credit' => $detail->subtotal,
                                    'keterangan' => 'Persediaan ' . ($detail->bahanBaku->nama_bahan ?? $detail->bahanPendukung->nama_bahan ?? 'Material')
                                ];
                            }
                        }
                        
                        // Buat jurnal
                        try {
                            $journal->create($journalData);
                            \Log::info('Jurnal pembelian berhasil dibuat', [
                                'pembelian_id' => $pembelian->id,
                                'payment_method' => $paymentMethod,
                                'total' => $computedTotal
                            ]);
                        } catch (\Exception $e) {
                            \Log::error('Gagal membuat jurnal pembelian: ' . $e->getMessage());
                            // Tetap lanjutkan, jurnal gagal tidak harus gagalkan pembelian
                        }
                    }
                    
                    \Log::info('Pembelian berhasil dengan detail', [
                        'pembelian_id' => $pembelian->id,
                        'detail_count' => $savedDetails,
                        'total_bahan_baku' => $totalBahanBaku,
                        'total_bahan_pendukung' => $totalBahanPendukung,
                    ]);

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
}