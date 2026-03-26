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
    public function index(Request $request)
    {
        $query = ExpensePayment::with([
            'bebanOperasional',
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
        $bebanOperasional = BebanOperasional::where('status', 'aktif')
            ->orderBy('nama_beban')
            ->get();
            
        $coaBebans = Coa::where('tipe_akun', 'Expense')
            ->orderBy('kode_akun')
            ->get();
            
        $coaKas = Coa::where('tipe_akun', 'Asset')
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
        // Get active Beban Operasional for dropdown
        $bebanOperasional = BebanOperasional::where('status', 'aktif')
            ->orderBy('nama_beban')
            ->get();
        
        // Get COA Beban for dropdown
        $coaBebans = Coa::where('tipe_akun', 'Expense')
            ->orderBy('kode_akun')
            ->get();
        
        // Get COA Kas/Bank for dropdown - dynamic filter based on account type and name
        $coaKas = Coa::where('tipe_akun', 'Asset')
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
            'coaKas'
        ));
    }

    public function store(Request $request, JournalService $journal)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'beban_operasional_id' => 'required|exists:beban_operasional,id',
            'coa_beban_id' => 'required|exists:coas,kode_akun',
            'metode_bayar' => 'required|in:cash,bank',
            'coa_kasbank' => 'required|exists:coas,kode_akun',
            'nominal_pembayaran' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string|max:500'
        ], [
            'beban_operasional_id.required' => 'Beban Operasional wajib dipilih',
            'beban_operasional_id.exists' => 'Beban Operasional tidak valid',
            'coa_beban_id.required' => 'Akun Beban wajib dipilih',
            'coa_beban_id.exists' => 'Akun Beban tidak valid',
            'coa_kasbank.required' => 'Akun Kas/Bank wajib dipilih',
            'coa_kasbank.exists' => 'Akun Kas/Bank tidak valid',
            'nominal_pembayaran.required' => 'Nominal Pembayaran wajib diisi',
            'nominal_pembayaran.min' => 'Nominal Pembayaran harus lebih dari 0',
        ]);

        DB::beginTransaction();

        try {
            
            // Dapatkan data COA menggunakan kode_akun
            $coaBeban = Coa::where('kode_akun', $request->coa_beban_id)->firstOrFail();
            $coaKas = Coa::where('kode_akun', $request->coa_kasbank)->firstOrFail();

            // Cek saldo kas/bank cukup
            $saldoKas = (float) ($coaKas->saldo_awal ?? 0) + 
                       (float) ($coaKas->saldo_debit ?? 0) - 
                       (float) ($coaKas->saldo_kredit ?? 0);
            
            if ($saldoKas < (float)$request->nominal_pembayaran) {
                throw new \Exception('Saldo kas/bank tidak mencukupi. Saldo tersedia: ' . number_format($saldoKas, 0, ',', '.'));
            }

            // Simpan data pembayaran
            $row = new ExpensePayment([
                'tanggal' => $request->tanggal,
                'beban_operasional_id' => $request->beban_operasional_id,
                'coa_beban_id' => $coaBeban->kode_akun,
                'metode_bayar' => $request->metode_bayar,
                'coa_kasbank' => $coaKas->kode_akun,
                'nominal_pembayaran' => (float)$request->nominal_pembayaran,
                'keterangan' => $request->keterangan,
                'user_id' => auth()->id(),
            ]);

            if (!$row->save()) {
                throw new \Exception('Gagal menyimpan data pembayaran beban');
            }

            // Jurnal: Dr Expense ; Cr Cash/Bank
            $journal->post(
                $request->tanggal, 
                'expense_payment', 
                (int)$row->id, 
                'Pembayaran Beban - ' . $coaBeban->nama_akun, 
                [
                    ['code' => $coaBeban->kode_akun, 'debit' => (float)$request->nominal_pembayaran, 'credit' => 0],
                    ['code' => $coaKas->kode_akun, 'debit' => 0, 'credit' => (float)$request->nominal_pembayaran],
                ]
            );

            // Update saldo COA
            $this->updateCoaSaldo($coaBeban->kode_akun);
            $this->updateCoaSaldo($coaKas->kode_akun);
            
            // Update BOP aktual
            $this->updateBopAktual($coaBeban->kode_akun);

            DB::commit();
            
            return redirect()
                ->route('transaksi.pembayaran-beban.index')
                ->with('success', 'Pembayaran beban berhasil disimpan');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
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
        $coaBebans = Coa::where('tipe_akun', 'Expense')
            ->orderBy('kode_akun')
            ->get();
        
        // Get COA Kas/Bank for dropdown - dynamic filter based on account type and name
        $coaKas = Coa::where('tipe_akun', 'Asset')
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
            'coa_beban_id' => 'required|exists:coas,kode_akun',
            'metode_bayar' => 'required|in:cash,bank',
            'coa_kasbank' => 'required|exists:coas,kode_akun',
            'nominal_pembayaran' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string|max:500'
        ], [
            'beban_operasional_id.required' => 'Beban Operasional wajib dipilih',
            'beban_operasional_id.exists' => 'Beban Operasional tidak valid',
            'coa_beban_id.required' => 'Akun Beban wajib dipilih',
            'coa_beban_id.exists' => 'Akun Beban tidak valid',
            'coa_kasbank.required' => 'Akun Kas/Bank wajib dipilih',
            'coa_kasbank.exists' => 'Akun Kas/Bank tidak valid',
            'nominal_pembayaran.required' => 'Nominal Pembayaran wajib diisi',
            'nominal_pembayaran.min' => 'Nominal Pembayaran harus lebih dari 0',
        ]);

        $row = ExpensePayment::findOrFail($id);
        $oldNominal = $row->nominal_pembayaran;
        $oldCashCode = $row->coa_kasbank;

        // Dapatkan data COA
        $coaBeban = Coa::where('kode_akun', $request->coa_beban_id)->firstOrFail();
        $coaKas = Coa::where('kode_akun', $request->coa_kasbank)->firstOrFail();

        // Cek saldo kas/bank cukup (hitung selisih jika nominal berubah)
        $selisih = (float)$request->nominal_pembayaran - (float)$oldNominal;
        
        if ($selisih > 0) {
            $saldoAwal = (float) ($coaKas->saldo_awal ?? 0);
            $acc = \App\Models\Account::where('code', $coaKas->kode_akun)->first();
            
            $journalBalance = 0.0;
            if ($acc) {
                $journalBalance = (float) (\App\Models\JournalLine::where('account_id', $acc->id)
                    ->selectRaw('COALESCE(SUM(debit - credit),0) as bal')->value('bal') ?? 0);
            }
            
            $cashBalance = $saldoAwal + $journalBalance;
            if ($cashBalance + 1e-6 < $selisih) {
                return back()->withErrors([
                    'kas' => 'Nominal kas tidak cukup untuk melakukan transaksi. Saldo kas saat ini: Rp '.number_format($cashBalance,0,',','.').' ; Selisih nominal: Rp '.number_format($selisih,0,',','.'),
                ])->withInput();
            }
        }

        $row->update([
            'tanggal' => $request->tanggal,
            'beban_operasional_id' => $request->beban_operasional_id,
            'coa_beban_id' => $request->coa_beban_id,
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
        $coa = Coa::findOrFail($request->coa_beban_id);
        $journal->post($request->tanggal, 'expense_payment', (int)$row->id, 'Pembayaran Beban - '.$coa->nama_akun, [
            ['code'=>$coa->kode_akun, 'debit'=>(float)$request->nominal_pembayaran, 'credit'=>0],
            ['code'=>$request->coa_kasbank, 'debit'=>0, 'credit'=>(float)$request->nominal_pembayaran],
        ]);

        // Update aktual di BOP
        $this->updateBopAktual($coa->kode_akun);

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
     * Update saldo COA berdasarkan jurnal
     */
    protected function updateCoaSaldo($kodeAkun)
    {
        $coa = Coa::where('kode_akun', $kodeAkun)->first();
        if (!$coa) return;

        // Hitung total debit dan kredit dari jurnal
        $saldo = \DB::table('journal_lines as jl')
            ->join('journal_entries as je', 'je.id', '=', 'jl.journal_entry_id')
            ->where('jl.account_id', $coa->id)
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
