<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\ExpensePayment;
use App\Models\Coa;
use App\Models\Bop;
use App\Services\JournalService;
use App\Helpers\AccountHelper;

class ExpensePaymentController extends Controller
{
    public function index()
    {
        // Ambil data dengan relasi yang benar
        $rows = ExpensePayment::with([
            'coaBeban' => function($q) {
                $q->select('kode_akun', 'nama_akun');
            },
            'coaKasBank' => function($q) {
                $q->select('kode_akun', 'nama_akun');
            }
        ])
        ->orderBy('tanggal', 'desc')
        ->paginate(20);
        
        // Debug: Cek data yang diambil
        \Log::info('Expense Payment Data:', $rows->toArray());
        
        return view('transaksi.expense-payment.index', compact('rows'));
    }

    public function create()
    {
        // Hanya tampilkan COA yang ada di BOP (beban operasional)
        $bopKodes = Bop::pluck('kode_akun');
        $coas = Coa::whereIn('kode_akun', $bopKodes)->orderBy('kode_akun')->get();
        
        // Gunakan helper untuk konsistensi
        $kasbank = AccountHelper::getKasBankAccounts();
        return view('transaksi.expense-payment.create', compact('coas','kasbank'));
    }

    public function store(Request $request, JournalService $journal)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'coa_beban_id' => 'required|exists:coas,kode_akun',
            'coa_kasbank' => 'required|exists:coas,kode_akun',
            'nominal' => 'required|numeric|min:0',
        ], [
            'coa_beban_id.exists' => 'Akun beban tidak valid',
            'coa_kasbank.exists' => 'Akun kas/bank tidak valid',
        ]);

        DB::beginTransaction();

        try {
            // Dapatkan data COA menggunakan kode_akun
            $coaBeban = Coa::where('kode_akun', $request->coa_beban_id)->firstOrFail();
            $coaKas = Coa::where('kode_akun', $request->coa_kasbank)->firstOrFail();

            // Cek saldo kas/bank cukup
            $saldoKas = (float) $coaKas->saldo_awal + (float) $coaKas->saldo_debit - (float) $coaKas->saldo_kredit;
            if ($saldoKas < (float)$request->nominal) {
                throw new \Exception('Saldo kas/bank tidak mencukupi. Saldo tersedia: ' . number_format($saldoKas, 0, ',', '.'));
            }

            // Simpan data pembayaran, menggunakan kode_akun
            $row = new ExpensePayment([
                'tanggal' => $request->tanggal,
                'coa_beban_id' => $coaBeban->kode_akun,
                'metode_bayar' => $request->metode_bayar ?? 'cash',
                'coa_kasbank' => $coaKas->kode_akun,
                'nominal' => (float)$request->nominal,
                'deskripsi' => $request->deskripsi,
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
                    ['code' => $coaBeban->kode_akun, 'debit' => (float)$request->nominal, 'credit' => 0],
                    ['code' => $coaKas->kode_akun, 'debit' => 0, 'credit' => (float)$request->nominal],
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
            \Log::error('Error in ExpensePaymentController@store: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            
            return back()
                ->with('error', 'Gagal menyimpan pembayaran beban: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show($id)
    {
        $row = ExpensePayment::with('coa')->findOrFail($id);
        return view('transaksi.expense-payment.show', compact('row'));
    }

    public function edit($id)
    {
        $row = ExpensePayment::with('coa')->findOrFail($id);
        
        // Hanya tampilkan COA yang ada di BOP (beban operasional)
        $bopKodes = Bop::pluck('kode_akun');
        $coas = Coa::whereIn('kode_akun', $bopKodes)->orderBy('kode_akun')->get();
        
        // Gunakan helper untuk konsistensi
        $kasbank = AccountHelper::getKasBankAccounts();
        return view('transaksi.expense-payment.edit', compact('row', 'coas', 'kasbank'));
    }

    public function update(Request $request, $id, JournalService $journal)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'coa_beban_id' => 'required|exists:coas,kode_akun',
            'coa_kasbank' => 'required|exists:coas,kode_akun',
            'nominal' => 'required|numeric|min:0',
        ], [
            'coa_beban_id.exists' => 'Akun beban tidak valid',
            'coa_kasbank.exists' => 'Akun kas/bank tidak valid',
        ]);

        $row = ExpensePayment::findOrFail($id);
        $oldNominal = $row->nominal;
        $oldCashCode = $row->coa_kasbank;

        // Dapatkan data COA
        $coaBeban = Coa::where('kode_akun', $request->coa_beban_id)->firstOrFail();
        $coaKas = Coa::where('kode_akun', $request->coa_kasbank)->firstOrFail();

        // Cek saldo kas/bank cukup (hitung selisih jika nominal berubah)
        $selisih = (float)$request->nominal - (float)$oldNominal;
        
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
            'tanggal'=>$request->tanggal,
            'coa_beban_id'=>$request->coa_beban_id,
            'metode_bayar'=>$request->metode_bayar ?? 'cash',
            'coa_kasbank'=>$request->coa_kasbank ?? '101',
            'nominal'=>$request->nominal,
            'deskripsi'=>$request->deskripsi,
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
            ['code'=>$coa->kode_akun, 'debit'=>(float)$request->nominal, 'credit'=>0],
            ['code'=>$request->coa_kasbank, 'debit'=>0, 'credit'=>(float)$request->nominal],
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
            // Cari BOP dengan kode akun ini
            $bop = Bop::where('kode_akun', $kodeAkun)->first();
            
            if (!$bop) {
                \Log::warning('BOP not found for kode_akun: ' . $kodeAkun);
                return;
            }

            // Cari COA ID berdasarkan kode akun
            $coa = Coa::where('kode_akun', $kodeAkun)->first();
            if (!$coa) {
                \Log::warning('COA not found for kode_akun: ' . $kodeAkun);
                return;
            }

            // Hitung total pembayaran beban untuk akun ini
            $totalAktual = ExpensePayment::where('coa_beban_id', $coa->id)->sum('nominal');

            // Update kolom aktual
            $bop->aktual = $totalAktual;
            $bop->save();

            \Log::info('BOP Aktual Updated', [
                'kode_akun' => $kodeAkun,
                'coa_id' => $coa->id,
                'aktual' => $totalAktual,
                'budget' => $bop->budget,
                'selisih' => $bop->budget - $totalAktual,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error updating BOP aktual: ' . $e->getMessage(), [
                'kode_akun' => $kodeAkun,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
