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