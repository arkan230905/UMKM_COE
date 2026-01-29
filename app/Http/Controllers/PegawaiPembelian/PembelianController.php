<?php

namespace App\Http\Controllers\PegawaiPembelian;

use App\Http\Controllers\Controller;
use App\Models\Pembelian;
use App\Models\PembelianDetail;
use App\Models\Vendor;
use App\Models\BahanBaku;
use App\Models\BahanPendukung;
use App\Models\Coa;
use App\Helpers\AccountHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PembelianController extends Controller
{
    public function index()
    {
        // Sama seperti admin PembelianController::index() dengan tambahan logic yang sama
        $query = \App\Models\Pembelian::with(['vendor', 'details.bahanBaku.satuan', 'details.bahanPendukung.satuanRelation']);
        
        // Filter berdasarkan parameter yang sama dengan admin
        if (request()->filled('start_date') && request()->filled('end_date')) {
            $query->whereBetween('tanggal', [request()->start_date, request()->end_date]);
        }
        
        if (request()->filled('vendor_id')) {
            $query->where('vendor_id', request()->vendor_id);
        }
        
        if (request()->filled('status')) {
            $query->where('status', request()->status);
        }
        
        $pembelians = $query->latest('tanggal')->paginate(15);
        $vendors = \App\Models\Vendor::orderBy('nama_vendor')->get();
        
        return view('pegawai-pembelian.pembelian.index', compact('pembelians', 'vendors'));
    }

    public function create()
    {
        $vendors = Vendor::all();
        $bahanBakus = BahanBaku::with('satuan')->get();
        $bahanPendukungs = BahanPendukung::with('satuanRelation')->get();
        
        // Ambil data COA untuk kas dan bank yang relevan saja (sesuai laporan kas/bank)
        $kasbank = Coa::whereIn('kode_akun', ['1101', '1102', '1103'])
            ->where('tipe_akun', 'Asset')
            ->where('is_akun_header', '!=', 1)
            ->orderBy('kode_akun')
            ->get();
        
        // Calculate current balances for payment display (sesuai laporan kas/bank)
        $currentBalances = [];
        foreach ($kasbank as $bank) {
            // Use the same method as LaporanKasBankController
            $startDate = '2026-01-01'; // Fixed periode sesuai admin display
            $endDate = '2026-01-31'; // Fixed periode sesuai admin display
            
            $saldoAwal = $this->getSaldoAwal($bank, $startDate);
            $transaksiMasuk = $this->getTransaksiMasuk($bank, $startDate, $endDate);
            $transaksiKeluar = $this->getTransaksiKeluar($bank, $startDate, $endDate);
            
            // Saldo Akhir = Saldo Awal + Debit (Masuk) - Kredit (Keluar)
            $saldoAkhir = $saldoAwal + $transaksiMasuk - $transaksiKeluar;
            $currentBalances[$bank->kode_akun] = $saldoAkhir;
        }
        
        return view('pegawai-pembelian.pembelian.create', compact('vendors', 'bahanBakus', 'bahanPendukungs', 'kasbank', 'currentBalances'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'tanggal' => 'required|date',
            'bank_id' => 'required',
            'keterangan' => 'nullable|string',
            'bahan_baku_id' => 'nullable|array',
            'bahan_baku_id.*' => 'nullable|exists:bahan_bakus,id',
            'jumlah' => 'nullable|array',
            'jumlah.*' => 'nullable|numeric|min:0.01',
            'harga_satuan' => 'nullable|array',
            'harga_satuan.*' => 'nullable|numeric|min:0',
            'bahan_pendukung_id' => 'nullable|array',
            'bahan_pendukung_id.*' => 'nullable|exists:bahan_pendukungs,id',
            'jumlah_pendukung' => 'nullable|array',
            'jumlah_pendukung.*' => 'nullable|numeric|min:0.01',
            'harga_satuan_pendukung' => 'nullable|array',
            'harga_satuan_pendukung.*' => 'nullable|numeric|min:0',
        ]);

        // Validasi: harus ada bahan baku ATAU bahan pendukung
        $hasBahanBaku = !empty($request->bahan_baku_id) && array_filter($request->bahan_baku_id);
        $hasBahanPendukung = !empty($request->bahan_pendukung_id) && array_filter($request->bahan_pendukung_id);
        
        if (!$hasBahanBaku && !$hasBahanPendukung) {
            return back()->withErrors(['bahan_required' => 'Harus memilih minimal satu bahan baku atau bahan pendukung']);
        }

        // Tentukan payment method berdasarkan bank_id
        $paymentMethod = 'transfer'; // default
        if ($request->bank_id === 'credit') {
            $paymentMethod = 'credit';
        } else {
            // Cek apakah ini kas atau bank
            $bank = Coa::find($request->bank_id);
            if ($bank && (str_contains(strtolower($bank->nama_akun), 'kas') || str_starts_with($bank->kode_akun, '1'))) {
                $paymentMethod = 'cash';
            } else {
                $paymentMethod = 'transfer';
            }
        }
        
        // Debug: Log payment method detection
        \Log::info('Payment Method Detection', [
            'bank_id' => $request->bank_id,
            'payment_method' => $paymentMethod,
            'bank_name' => isset($bank) ? $bank->nama_akun : 'not found',
            'bank_kode' => isset($bank) ? $bank->kode_akun : 'not found'
        ]);

        DB::beginTransaction();
        try {
            // Hitung total
            $totalHarga = 0;
            
            // Hitung total bahan baku
            if ($hasBahanBaku) {
                foreach ($request->bahan_baku_id as $index => $bahanBakuId) {
                    if ($bahanBakuId && isset($request->jumlah[$index]) && isset($request->harga_satuan[$index])) {
                        $subtotal = $request->jumlah[$index] * $request->harga_satuan[$index];
                        $totalHarga += $subtotal;
                    }
                }
            }
            
            // Hitung total bahan pendukung
            if ($hasBahanPendukung) {
                foreach ($request->bahan_pendukung_id as $index => $bahanPendukungId) {
                    if ($bahanPendukungId && isset($request->jumlah_pendukung[$index]) && isset($request->harga_satuan_pendukung[$index])) {
                        $subtotal = $request->jumlah_pendukung[$index] * $request->harga_satuan_pendukung[$index];
                        $totalHarga += $subtotal;
                    }
                }
            }

            // Buat pembelian
            $pembelianData = [
                'vendor_id' => $validated['vendor_id'],
                'tanggal' => $validated['tanggal'],
                'total_harga' => $totalHarga,
                'payment_method' => $paymentMethod,
                'bank_id' => $request->bank_id === 'credit' ? null : $request->bank_id,
                'terbayar' => ($paymentMethod === 'cash' || $paymentMethod === 'transfer') ? $totalHarga : 0,
                'sisa_pembayaran' => ($paymentMethod === 'cash' || $paymentMethod === 'transfer') ? 0 : $totalHarga,
                'status' => ($paymentMethod === 'cash' || $paymentMethod === 'transfer') ? 'lunas' : 'belum_lunas',
                'keterangan' => $validated['keterangan'] ?? null,
            ];
            
            // Debug: Log pembelian data
            \Log::info('Pembelian Data to Save', $pembelianData);
            
            $pembelian = Pembelian::create($pembelianData);

            // Update total_harga setelah details dibuat
            $totalHarga = 0;
            
            // Hitung total bahan baku
            if ($hasBahanBaku) {
                foreach ($request->bahan_baku_id as $index => $bahanBakuId) {
                    if ($bahanBakuId && isset($request->jumlah[$index]) && isset($request->harga_satuan[$index])) {
                        $jumlah = $request->jumlah[$index];
                        $hargaSatuan = $request->harga_satuan[$index];
                        $subtotal = $jumlah * $hargaSatuan;
                        $totalHarga += $subtotal;
                    }
                }
            }
            
            // Hitung total bahan pendukung
            if ($hasBahanPendukung) {
                foreach ($request->bahan_pendukung_id as $index => $bahanPendukungId) {
                    if ($bahanPendukungId && isset($request->jumlah_pendukung[$index]) && isset($request->harga_satuan_pendukung[$index])) {
                        $jumlah = $request->jumlah_pendukung[$index];
                        $hargaSatuan = $request->harga_satuan_pendukung[$index];
                        $subtotal = $jumlah * $hargaSatuan;
                        $totalHarga += $subtotal;
                    }
                }
            }
            
            // Update total_harga
            $pembelian->update(['total_harga' => $totalHarga]);

            // Buat detail pembelian dan update stok
            // Simpan detail bahan baku
            if ($hasBahanBaku) {
                foreach ($request->bahan_baku_id as $index => $bahanBakuId) {
                    if ($bahanBakuId && isset($request->jumlah[$index]) && isset($request->harga_satuan[$index])) {
                        $bahanBaku = BahanBaku::find($bahanBakuId);
                        $jumlah = $request->jumlah[$index];
                        $hargaSatuan = $request->harga_satuan[$index];
                        $subtotal = $jumlah * $hargaSatuan;

                        // Simpan detail
                        PembelianDetail::create([
                            'pembelian_id' => $pembelian->id,
                            'tipe_item' => 'bahan_baku',
                            'bahan_baku_id' => $bahanBakuId,
                            'jumlah' => $jumlah,
                            'harga_satuan' => $hargaSatuan,
                            'subtotal' => $subtotal,
                            'satuan' => $bahanBaku->satuan->nama_satuan ?? null,
                        ]);

                        // Update stok bahan baku
                        $bahanBaku->increment('stok', $jumlah);
                    }
                }
            }
            
            // Simpan detail bahan pendukung
            if ($hasBahanPendukung) {
                foreach ($request->bahan_pendukung_id as $index => $bahanPendukungId) {
                    if ($bahanPendukungId && isset($request->jumlah_pendukung[$index]) && isset($request->harga_satuan_pendukung[$index])) {
                        $bahanPendukung = BahanPendukung::find($bahanPendukungId);
                        $jumlah = $request->jumlah_pendukung[$index];
                        $hargaSatuan = $request->harga_satuan_pendukung[$index];
                        $subtotal = $jumlah * $hargaSatuan;

                        // Simpan detail bahan pendukung (mungkin perlu table terpisah)
                        // Untuk sekarang, simpan ke pembelian_detail dengan bahan_pendukung_id
                        PembelianDetail::create([
                            'pembelian_id' => $pembelian->id,
                            'tipe_item' => 'bahan_pendukung',
                            'bahan_pendukung_id' => $bahanPendukungId,
                            'jumlah' => $jumlah,
                            'harga_satuan' => $hargaSatuan,
                            'subtotal' => $subtotal,
                            'satuan' => $bahanPendukung->satuan->nama_satuan ?? null,
                        ]);

                        // Update stok bahan pendukung
                        $bahanPendukung->increment('stok', $jumlah);
                    }
                }
            }

            DB::commit();

            return redirect()->route('pegawai-pembelian.pembelian.index')
                ->with('success', 'Pembelian berhasil dibuat!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal membuat pembelian: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $pembelian = Pembelian::with(['vendor', 'details.bahanBaku', 'details.bahanPendukung'])->findOrFail($id);
        return view('pegawai-pembelian.pembelian.show', compact('pembelian'));
    }

    public function edit($id)
    {
        $pembelian = Pembelian::with(['vendor', 'details.bahanBaku.satuan', 'details.bahanPendukung.satuanRelation'])->findOrFail($id);
        $vendors = Vendor::all();
        $bahanBakus = BahanBaku::with('satuan')->get();
        $bahanPendukungs = BahanPendukung::with('satuanRelation')->get();
        
        // Ambil data COA untuk kas dan bank yang relevan saja (sesuai laporan kas/bank)
        $kasbank = Coa::whereIn('kode_akun', ['1101', '1102', '1103'])
            ->where('tipe_akun', 'Asset')
            ->where('is_akun_header', '!=', 1)
            ->orderBy('kode_akun')
            ->get();
        
        // Calculate current balances for payment display (sesuai laporan kas/bank)
        $currentBalances = [];
        foreach ($kasbank as $bank) {
            // Use the same method as LaporanKasBankController
            $startDate = '2026-01-01'; // Fixed periode sesuai admin display
            $endDate = '2026-01-31'; // Fixed periode sesuai admin display
            
            $saldoAwal = $this->getSaldoAwal($bank, $startDate);
            $transaksiMasuk = $this->getTransaksiMasuk($bank, $startDate, $endDate);
            $transaksiKeluar = $this->getTransaksiKeluar($bank, $startDate, $endDate);
            
            // Saldo Akhir = Saldo Awal + Debit (Masuk) - Kredit (Keluar)
            $saldoAkhir = $saldoAwal + $transaksiMasuk - $transaksiKeluar;
            $currentBalances[$bank->kode_akun] = $saldoAkhir;
        }
        
        return view('pegawai-pembelian.pembelian.edit', compact('pembelian', 'vendors', 'bahanBakus', 'bahanPendukungs', 'kasbank', 'currentBalances'));
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
        $validated = $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'tanggal' => 'required|date',
            'bank_id' => 'required',
            'keterangan' => 'nullable|string',
            'bahan_baku_id' => 'nullable|array',
            'bahan_baku_id.*' => 'nullable|exists:bahan_bakus,id',
            'jumlah' => 'nullable|array',
            'jumlah.*' => 'nullable|numeric|min:0.01',
            'harga_satuan' => 'nullable|array',
            'harga_satuan.*' => 'nullable|numeric|min:0',
            'bahan_pendukung_id' => 'nullable|array',
            'bahan_pendukung_id.*' => 'nullable|exists:bahan_pendukungs,id',
            'jumlah_pendukung' => 'nullable|array',
            'jumlah_pendukung.*' => 'nullable|numeric|min:0.01',
            'harga_satuan_pendukung' => 'nullable|array',
            'harga_satuan_pendukung.*' => 'nullable|numeric|min:0',
        ]);

        // Validasi: harus ada bahan baku ATAU bahan pendukung
        $hasBahanBaku = !empty($request->bahan_baku_id) && array_filter($request->bahan_baku_id);
        $hasBahanPendukung = !empty($request->bahan_pendukung_id) && array_filter($request->bahan_pendukung_id);
        
        if (!$hasBahanBaku && !$hasBahanPendukung) {
            return back()->withErrors(['bahan_required' => 'Harus memilih minimal satu bahan baku atau bahan pendukung']);
        }

        DB::beginTransaction();
        try {
            $pembelian = Pembelian::findOrFail($id);
            
            // Update pembelian
            $pembelian->update([
                'vendor_id' => $request->vendor_id,
                'tanggal' => $request->tanggal,
                'bank_id' => $request->bank_id,
                'keterangan' => $request->keterangan,
            ]);

            // Hapus detail lama
            $pembelian->details()->delete();

            // Tambah detail baru - Bahan Baku
            if ($hasBahanBaku) {
                foreach ($request->bahan_baku_id as $key => $bahanBakuId) {
                    if ($bahanBakuId) {
                        PembelianDetail::create([
                            'pembelian_id' => $pembelian->id,
                            'bahan_baku_id' => $bahanBakuId,
                            'jumlah' => $request->jumlah[$key],
                            'harga_satuan' => $request->harga_satuan[$key],
                        ]);
                    }
                }
            }

            // Tambah detail baru - Bahan Pendukung
            if ($hasBahanPendukung) {
                foreach ($request->bahan_pendukung_id as $key => $bahanPendukungId) {
                    if ($bahanPendukungId) {
                        PembelianDetail::create([
                            'pembelian_id' => $pembelian->id,
                            'bahan_pendukung_id' => $bahanPendukungId,
                            'jumlah' => $request->jumlah_pendukung[$key],
                            'harga_satuan' => $request->harga_satuan_pendukung[$key],
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()->route('pegawai-pembelian.pembelian.index')
                ->with('success', 'Pembelian berhasil diupdate!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal mengupdate pembelian: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $pembelian = Pembelian::with('pembelianDetails')->findOrFail($id);

            // Kembalikan stok bahan baku
            foreach ($pembelian->pembelianDetails as $detail) {
                $bahanBaku = BahanBaku::find($detail->bahan_baku_id);
                if ($bahanBaku) {
                    $bahanBaku->decrement('stok', $detail->jumlah);
                }
            }

            // Hapus detail dan pembelian
            $pembelian->pembelianDetails()->delete();
            $pembelian->delete();

            DB::commit();

            return redirect()->route('pegawai-pembelian.pembelian.index')
                ->with('success', 'Pembelian berhasil dihapus!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus pembelian: ' . $e->getMessage());
        }
    }
}
