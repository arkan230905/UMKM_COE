<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\ExpensePayment;
use App\Models\Coa;
use App\Models\Bop;
use App\Models\BebanOperasional;
use App\Services\JournalService;
use App\Helpers\AccountHelper;

class ExpensePaymentController extends Controller
{
    /**
     * Get saldo akhir akun kas/bank menggunakan data yang sama dengan halaman laporan kas-bank
     */
    public function getSaldoAkhir($akun, $tanggal = null)
    {
        // Langsung gunakan LaporanKasBankController untuk mendapatkan data yang sama
        $laporanController = new \App\Http\Controllers\LaporanKasBankController();
        
        // Gunakan range tanggal yang sama dengan laporan kas-bank (bulan ini)
        $startDate = now()->startOfMonth()->format('Y-m-d');
        $endDate = now()->endOfMonth()->format('Y-m-d');
        
        // Gunakan AccountHelper untuk mendapatkan akun yang sama dengan laporan kas-bank
        $akunKasBank = AccountHelper::getKasBankAccounts();
        
        // Cari akun yang sesuai dan hitung saldo menggunakan logika LaporanKasBankController
        foreach ($akunKasBank as $kasBankAkun) {
            if ($kasBankAkun->kode_akun === $akun->kode_akun) {
                // Gunakan reflection untuk mengakses private methods dari LaporanKasBankController
                $reflection = new \ReflectionClass($laporanController);
                
                $getSaldoAwalMethod = $reflection->getMethod('getSaldoAwal');
                $getSaldoAwalMethod->setAccessible(true);
                $saldoAwal = $getSaldoAwalMethod->invoke($laporanController, $kasBankAkun, $startDate);
                
                $getTransaksiMasukMethod = $reflection->getMethod('getTransaksiMasuk');
                $getTransaksiMasukMethod->setAccessible(true);
                $transaksiMasuk = $getTransaksiMasukMethod->invoke($laporanController, $kasBankAkun, $startDate, $endDate);
                
                $getTransaksiKeluarMethod = $reflection->getMethod('getTransaksiKeluar');
                $getTransaksiKeluarMethod->setAccessible(true);
                $transaksiKeluar = $getTransaksiKeluarMethod->invoke($laporanController, $kasBankAkun, $startDate, $endDate);
                
                // Saldo Akhir = Saldo Awal + Debit (Masuk) - Kredit (Keluar)
                $saldoAkhir = $saldoAwal + $transaksiMasuk - $transaksiKeluar;
                
                return $saldoAkhir;
            }
        }
        
        return 0;
    }



    public function index(Request $request)
    {
        $query = ExpensePayment::with([
            'bebanOperasional.coa',
            'coaBeban' => function($q) {
                $q->select('kode_akun', 'nama_akun');
            },
            'coaKasBank' => function($q) {
                $q->select('kode_akun', 'nama_akun');
            }
        ]);
        
        // Filter by tanggal
        if ($request->filled('tanggal_mulai')) {
            $query->whereDate('tanggal', '>=', $request->tanggal_mulai);
        }
        if ($request->filled('tanggal_selesai')) {
            $query->whereDate('tanggal', '<=', $request->tanggal_selesai);
        }
        
        // Filter by beban operasional
        if ($request->filled('beban_operasional_id')) {
            $query->where('beban_operasional_id', $request->beban_operasional_id);
        }
        
        // Filter by kategori
        if ($request->filled('kategori')) {
            $query->whereHas('bebanOperasional', function($q) use ($request) {
                $q->where('kategori', $request->kategori);
            });
        }
        
        // Filter by akun beban
        if ($request->filled('akun_beban_id')) {
            $query->where('coa_beban_id', $request->akun_beban_id);
        }
        
        // Filter by akun kas
        if ($request->filled('akun_kas_id')) {
            $query->where('coa_kasbank', $request->akun_kas_id);
        }
        
        $rows = $query->orderBy('tanggal', 'desc')->paginate(20);
        
        // Get data for dropdowns
        $bebanOperasional = BebanOperasional::with('coa')
            ->where('status', 'aktif')
            ->orderBy('nama_beban')
            ->get();
            
        $coaBebans = Coa::where('tipe_akun', 'Beban')
            ->orWhere('tipe_akun', 'BEBAN')
            ->get();
            
        $coaKas = Coa::where('tipe_akun', 'Aset')
            ->orWhere('tipe_akun', 'ASET')
            ->where('saldo_normal', 'debit')
            ->where(function($query) {
                $query->where('nama_akun', 'like', '%kas%')
                      ->orWhere('nama_akun', 'like', '%bank%');
            })
            ->orderBy('kode_akun')
            ->get();
        
        return view('transaksi.pembayaran-beban.index', compact(
            'rows', 
            'bebanOperasional', 
            'coaBebans', 
            'coaKas'
        ));
    }

    public function create()
    {
        // Get all Beban Operasional with COA relation
        $bebanOperasional = BebanOperasional::with('coa')
            ->orderBy('nama_beban')
            ->get();
        
        // Get COA Beban for dropdown
        $coaBebans = Coa::where('tipe_akun', 'Beban')
            ->orWhere('tipe_akun', 'BEBAN')
            ->orderByRaw('CAST(kode_akun AS UNSIGNED) ASC')
            ->orderBy('kode_akun', 'ASC')
            ->get();
        
        // Get COA Kas/Bank for dropdown
        $akunKas = Coa::where('tipe_akun', 'Asset')
            ->where('saldo_normal', 'debit')
            ->where(function($query) {
                $query->where('nama_akun', 'like', '%kas%')
                      ->orWhere('nama_akun', 'like', '%bank%');
            })
            ->orderBy('kode_akun')
            ->get();
        
        return view('transaksi.pembayaran-beban.create', compact(
            'bebanOperasional', 
            'coaBebans',
            'akunKas'
        ));
    }

    public function store(Request $request, JournalService $journal)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'beban_operasional_id' => 'required|exists:beban_operasional,id',
            'kode_akun_beban' => 'required|exists:coas,kode_akun',
            'kode_akun_kas' => 'required|exists:coas,kode_akun',
            'jumlah' => 'required|numeric|min:1',
            'keterangan' => 'nullable|string|max:255',
            'catatan' => 'nullable|string',
        ], [
            'beban_operasional_id.required' => 'Beban Operasional wajib dipilih',
            'beban_operasional_id.exists' => 'Beban Operasional tidak valid',
            'kode_akun_beban.required' => 'Akun Beban wajib dipilih',
            'kode_akun_beban.exists' => 'Akun Beban tidak valid',
            'kode_akun_kas.required' => 'Akun Kas/Bank wajib dipilih',
            'kode_akun_kas.exists' => 'Akun Kas/Bank tidak valid',
        ]);

        DB::beginTransaction();
        
        try {
            // Ambil Beban Operasional yang dipilih
            $bebanOperasional = BebanOperasional::find($request->beban_operasional_id);
            
            if (!$bebanOperasional) {
                throw new \Exception('Beban operasional tidak ditemukan. ID: ' . $request->beban_operasional_id);
            }
            
            // Ambil akun beban berdasarkan kode_akun dari dropdown
            $akunBeban = Coa::where('kode_akun', $request->kode_akun_beban)->first();
            
            if (!$akunBeban) {
                throw new \Exception('Akun beban tidak ditemukan. Kode akun: ' . $request->kode_akun_beban);
            }
            
            // Ambil akun kas berdasarkan kode_akun dari dropdown
            $akunKas = Coa::where('kode_akun', $request->kode_akun_kas)->first();
            
            if (!$akunKas) {
                throw new \Exception('Akun kas tidak ditemukan. Kode akun: ' . $request->kode_akun_kas);
            }
            
            // Validasi saldo kas
            $saldoAkhir = $this->getSaldoAkhir($akunKas, $request->tanggal);
            
            if ($saldoAkhir < $request->jumlah) {
                return back()
                    ->with('error', 'Saldo kas tidak mencukupi. Saldo tersedia: ' . format_rupiah($saldoAkhir))
                    ->withInput();
            }
            
            // Generate kode transaksi
            $lastPembayaran = \App\Models\ExpensePayment::latest('id')->first();
            $count = $lastPembayaran ? ($lastPembayaran->id + 1) : 1;
            $kodeTransaksi = 'PB-' . date('Ymd') . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
            
            // Simpan pembayaran beban
            $pembayaran = new \App\Models\ExpensePayment([
                'tanggal' => $request->tanggal,
                'beban_operasional_id' => $bebanOperasional->id,
                'coa_beban_id' => $akunBeban->kode_akun,
                'metode_bayar' => 'cash', // Default
                'coa_kasbank' => $akunKas->kode_akun,
                'nominal_pembayaran' => $request->jumlah,
                'keterangan' => $request->keterangan,
                'user_id' => auth()->id(),
            ]);
            
            if (!$pembayaran->save()) {
                throw new \Exception('Gagal menyimpan data pembayaran beban');
            }

            // Jurnal: Dr Expense ; Cr Cash/Bank
            $journal->post(
                $request->tanggal, 
                'expense_payment', 
                (int)$pembayaran->id, 
                'Pembayaran Beban: ' . $bebanOperasional->nama_beban, 
                [
                    ['code' => $akunBeban->kode_akun, 'debit' => $request->jumlah, 'credit' => 0],
                    ['code' => $akunKas->kode_akun, 'debit' => 0, 'credit' => $request->jumlah],
                ]
            );

            DB::commit();
            
            return redirect()
                ->route('transaksi.pembayaran-beban.index')
                ->with('success', 'Pembayaran beban berhasil disimpan');
                
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error in ExpensePaymentController@store: ' . $e->getMessage());
            
            return back()
                ->with('error', 'Gagal menyimpan pembayaran beban: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show($id)
    {
        $pembayaran = ExpensePayment::with(['bebanOperasional', 'coaBeban', 'coaKasBank'])->findOrFail($id);
        return view('transaksi.pembayaran-beban.show', compact('pembayaran'));
    }

    public function edit($id)
    {
        $row = ExpensePayment::with(['bebanOperasional', 'coaBeban', 'coaKasBank'])->findOrFail($id);
        
        // Get active Beban Operasional for dropdown
        $bebanOperasional = BebanOperasional::where('status', 'aktif')
            ->orderBy('nama_beban')
            ->get();
        
        // Get COA Beban for dropdown
        $coaBebans = Coa::where('tipe_akun', 'Beban')
            ->orWhere('tipe_akun', 'BEBAN')
            ->get();
        
        // Get COA Kas/Bank for dropdown - dynamic filter based on account type and name
        $coaKas = Coa::where('tipe_akun', 'Aset')
            ->orWhere('tipe_akun', 'ASET')
            ->where('saldo_normal', 'debit')
            ->where(function($query) {
                $query->where('nama_akun', 'like', '%kas%')
                      ->orWhere('nama_akun', 'like', '%bank%');
            })
            ->orderBy('kode_akun')
            ->get();
        
        return view('transaksi.pembayaran-beban.edit', compact(
            'row', 
            'bebanOperasional', 
            'coaBebans', 
            'coaKas'
        ));
    }

    public function update(Request $request, $id, JournalService $journal)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'beban_operasional_id' => 'required|exists:beban_operasional,id',
            'metode_bayar' => 'required|in:cash,bank',
            'coa_kasbank' => 'required|exists:coas,kode_akun',
            'nominal_pembayaran' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string|max:500'
        ], [
            'beban_operasional_id.required' => 'Beban Operasional wajib dipilih',
            'beban_operasional_id.exists' => 'Beban Operasional tidak valid',
            'coa_kasbank.required' => 'Akun Kas/Bank wajib dipilih',
            'coa_kasbank.exists' => 'Akun Kas/Bank tidak valid',
            'nominal_pembayaran.required' => 'Nominal Pembayaran wajib diisi',
            'nominal_pembayaran.min' => 'Nominal Pembayaran harus lebih dari 0',
        ]);

        $row = ExpensePayment::findOrFail($id);
        $oldNominal = $row->nominal_pembayaran;
        $oldCashCode = $row->coa_kasbank;

        // Ambil Beban Operasional yang dipilih user
        $bebanOperasional = BebanOperasional::with('coa')->findOrFail($request->beban_operasional_id);
        
        // Validasi COA beban
        if (!$bebanOperasional->coa) {
            throw new \Exception('Beban Operasional ini belum memiliki akun COA. Silakan atur terlebih dahulu.');
        }
        
        // Dapatkan data COA kas/bank
        $coaKas = Coa::where('kode_akun', $request->coa_kasbank)->firstOrFail();

        // Cek saldo kas/bank cukup (hitung selisih jika nominal berubah)
        $selisih = (float)$request->nominal_pembayaran - (float)$oldNominal;
        
        if ($selisih > 0) {
            $saldoAkhir = $this->getSaldoAkhir($coaKas, $request->tanggal);
            
            if ($saldoAkhir + 1e-6 < $selisih) {
                return back()->withErrors([
                    'kas' => 'Nominal kas tidak cukup untuk melakukan transaksi. Saldo kas saat ini: Rp '.number_format($saldoAkhir,0,',','.').' ; Selisih nominal: Rp '.number_format($selisih,0,',','.'),
                ])->withInput();
            }
        }

        $row->update([
            'tanggal' => $request->tanggal,
            'beban_operasional_id' => $request->beban_operasional_id,
            'coa_beban_id' => $bebanOperasional->coa->kode_akun,
            'metode_bayar' => $request->metode_bayar,
            'coa_kasbank' => $request->coa_kasbank,
            'nominal_pembayaran' => $request->nominal_pembayaran,
            'keterangan' => $request->keterangan,
        ]);

        // Hapus jurnal lama dan buat baru
        $acc = \App\Models\Account::where('code', $oldCashCode)->first();
        if ($acc) {
            \App\Models\JournalEntry::where('ref_type', 'expense_payment')
                ->where('ref_id', $row->id)
                ->delete();
        }

        // Jurnal baru: Dr Expense ; Cr Cash/Bank
        $journal->post($request->tanggal, 'expense_payment', (int)$row->id, 'Pembayaran Beban - '.$bebanOperasional->coa->nama_akun, [
            ['code'=>$bebanOperasional->coa->kode_akun, 'debit'=>(float)$request->nominal_pembayaran, 'credit'=>0],
            ['code'=>$request->coa_kasbank, 'debit'=>0, 'credit'=>(float)$request->nominal_pembayaran],
        ]);

        // Update aktual di BOP
        $this->updateBopAktual($bebanOperasional->coa->kode_akun);

        return redirect()->route('transaksi.pembayaran-beban.index')->with('success','Pembayaran beban berhasil diupdate.');
    }

    public function destroy($id)
    {
        $row = ExpensePayment::findOrFail($id);
        
        // Hapus jurnal terkait
        \App\Models\JournalEntry::where('ref_type', 'expense_payment')
            ->where('ref_id', $row->id)
            ->delete();
        
        // Simpan kode akun sebelum delete
        $kodeAkun = $row->coa->kode_akun ?? null;
        
        $row->delete();

        // Update aktual di BOP setelah delete
        if ($kodeAkun) {
            $this->updateBopAktual($kodeAkun);
        }

        return redirect()->route('transaksi.pembayaran-beban.index')->with('success','Pembayaran beban berhasil dihapus.');
    }

    /**
     * Print/export pembayaran beban
     */
    public function print($id)
    {
        $pembayaran = ExpensePayment::with([
            'bebanOperasional', 
            'coaBeban', 
            'coaKasBank'
        ])->findOrFail($id);
        
        return view('transaksi.pembayaran-beban.print', compact('pembayaran'));
    }

    /**
     * Update saldo COA berdasarkan jurnal
     */
    protected function updateCoaSaldo($kodeAkun)
    {
        $coa = Coa::where('kode_akun', $kodeAkun)->first();
        if (!$coa) return;

        // Hitung total debit dan kredit dari jurnal
        $saldo = \DB::table('journal_lines as jl')
            ->join('journal_entries as je', 'je.id', '=', 'jl.journal_entry_id')
            ->where('jl.coa_id', $coa->id)
            ->selectRaw('COALESCE(SUM(jl.debit), 0) as total_debit, COALESCE(SUM(jl.credit), 0) as total_credit')
            ->first();

        // Update saldo di COA
        $coa->update([
            'saldo_debit' => $saldo->total_debit,
            'saldo_kredit' => $saldo->total_credit,
        ]);
    }

    /**
     * Update kolom aktual di BOP berdasarkan total pembayaran beban
     */
    protected function updateBopAktual($kodeAkun)
    {
        try {
            // Cari BOP Lainnya dengan kode akun ini
            $bopLainnya = \App\Models\BopLainnya::where('kode_akun', $kodeAkun)
                ->where('is_active', true)
                ->first();
            
            if (!$bopLainnya) {
                \Log::warning('BOP Lainnya not found for kode_akun: ' . $kodeAkun);
                return;
            }

            // Hitung total pembayaran beban untuk akun ini
            $totalAktual = ExpensePayment::where('coa_beban_id', $kodeAkun)->sum('nominal_pembayaran');

            // Update kolom aktual
            $bopLainnya->aktual = $totalAktual;
            $bopLainnya->save();

            \Log::info('BOP Lainnya Aktual Updated', [
                'kode_akun' => $kodeAkun,
                'aktual' => $totalAktual,
                'budget' => $bopLainnya->budget,
                'selisih' => $bopLainnya->budget - $totalAktual,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error updating BOP Lainnya aktual: ' . $e->getMessage(), [
                'kode_akun' => $kodeAkun,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
