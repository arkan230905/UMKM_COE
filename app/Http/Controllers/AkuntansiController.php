<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\Account;
use App\Models\Coa;
use App\Models\CoaPeriod;
use App\Models\CoaPeriodBalance;
use App\Models\JurnalUmum;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\JurnalUmumExport;
use App\Exports\BukuBesarExport;
use Maatwebsite\Excel\Facades\Excel;

class AkuntansiController extends Controller
{
    public function jurnalUmum(Request $request)
    {
        $from = $request->get('from');
        $to   = $request->get('to');
        $refType = $request->get('ref_type');
        $refId   = $request->get('ref_id');
        $accountCode = $request->get('account_code');

        $query = JournalEntry::with(['lines.account'])->orderBy('tanggal','asc')->orderBy('id','asc');
        if ($from) { $query->whereDate('tanggal','>=',$from); }
        if ($to)   { $query->whereDate('tanggal','<=',$to); }
        if ($refType) { $query->where('ref_type', $refType); }
        if ($refId)   { $query->where('ref_id', $refId); }
        if ($accountCode) { 
            $query->whereHas('lines', function($q) use ($accountCode) {
                $q->whereHas('account', function($subQ) use ($accountCode) {
                    $subQ->where('code', $accountCode);
                });
            });
        }
        $entries = $query->get();

        return view('akuntansi.jurnal-umum', compact('entries','from','to','refType','refId','accountCode'));
    }

    public function jurnalUmumExportPdf(Request $request)
    {
        $from = $request->get('from');
        $to   = $request->get('to');
        $refType = $request->get('ref_type');
        $refId   = $request->get('ref_id');

        $query = JournalEntry::with(['lines.account'])->orderBy('tanggal','asc')->orderBy('id','asc');
        if ($from) { $query->whereDate('tanggal','>=',$from); }
        if ($to)   { $query->whereDate('tanggal','<=',$to); }
        if ($refType) { $query->where('ref_type', $refType); }
        if ($refId)   { $query->where('ref_id', $refId); }
        $entries = $query->get();

        $pdf = Pdf::loadView('akuntansi.jurnal-umum-pdf', compact('entries','from','to','refType','refId'))
            ->setPaper('a4', 'landscape');
        
        return $pdf->download('jurnal-umum-'.date('Y-m-d').'.pdf');
    }

    public function jurnalUmumExportExcel(Request $request)
    {
        $from = $request->get('from');
        $to   = $request->get('to');
        $refType = $request->get('ref_type');
        $refId   = $request->get('ref_id');

        $export = new JurnalUmumExport($from, $to, $refType, $refId);
        return $export->download('jurnal-umum-'.date('Y-m-d').'.xlsx');
    }

    public function bukuBesar(Request $request)
    {
        $month = $request->get('month');
        $year = $request->get('year');
        $accountId = $request->get('account_id');

        // Ambil semua COA yang ada di sistem
        $coas = \App\Models\Coa::where('is_akun_header', 0)->orderBy('kode_akun')->get();
        $lines = collect();
        $saldoAwal = 0.0;
        $from = null;
        $to = null;

        if ($accountId) {
            // Debug: Coba cari COA dengan cara berbeda
            $coa = null;
            foreach($coas as $c) {
                if ($c->id == $accountId) {
                    $coa = $c;
                    break;
                }
            }
            
            if (!$coa) {
                return view('akuntansi.buku-besar', compact('coas','accountId','lines','from','to','saldoAwal','month','year'));
            }
            
            // Cari Account yang sesuai dengan COA
            $account = \App\Models\Account::where('code', $coa->kode_akun)->first();
            
            if (!$account) {
                return view('akuntansi.buku-besar', compact('coas','accountId','lines','from','to','saldoAwal','month','year'));
            }
            
            // Jika bulan dan tahun dipilih, buat rentang tanggal
            if ($month && $year) {
                $from = $year . '-' . $month . '-01';
                $to = $year . '-' . $month . '-' . date('t', mktime(0, 0, 0, $month, 1, $year));
                
                // Get saldo awal dari COA table (proper accounting method)
                $saldoAwal = $coa->saldo_awal ?? 0;
                
                // Ambil transaksi dalam periode
                $query = \App\Models\JournalLine::with(['entry'])
                    ->where('account_id', $account->id)
                    ->whereHas('entry', function($q) use ($from, $to) {
                        $q->whereDate('tanggal', '>=', $from)
                          ->whereDate('tanggal', '<=', $to);
                    });
                
                $lines = $query->orderBy('journal_entry_id', 'asc')->get();
            }
        }

        return view('akuntansi.buku-besar', compact('coas','accountId','lines','from','to','saldoAwal','month','year'));
    }

    public function bukuBesarExportExcel(Request $request)
    {
        $from = $request->get('from');
        $to   = $request->get('to');

        return Excel::download(
            new BukuBesarExport($from, $to),
            'buku-besar-'.date('Y-m-d').'.xlsx'
        );
    }

    public function neracaSaldo(Request $request)
    {
        // Get periode yang dipilih atau periode saat ini
        $periodId = $request->get('period_id');
        $periode = null;
        
        if ($periodId) {
            $periode = CoaPeriod::find($periodId);
        }
        
        // Jika tidak ada periode dipilih, gunakan periode saat ini
        if (!$periode) {
            $periode = CoaPeriod::getCurrentPeriod();
        }
        
        $from = $periode->tanggal_mulai->format('Y-m-d');
        $to = $periode->tanggal_selesai->format('Y-m-d');

        // Get semua periode untuk dropdown
        $periods = CoaPeriod::orderBy('periode', 'desc')->get();

        // Get semua COA (bukan header)
        $coas = Coa::where('is_akun_header', false)->orderBy('kode_akun')->get();
        
        $totals = [];
        foreach ($coas as $coa) {
            // Get saldo awal dari COA table (proper accounting method)
            $saldoAwal = $coa->saldo_awal ?? 0;
            
            // Hitung mutasi dalam periode menggunakan JournalEntry dan JournalLine (data baru)
            $journalEntryIds = JournalEntry::whereBetween('tanggal', [$from, $to])->pluck('id');
            
            $debit = JournalLine::whereIn('journal_entry_id', $journalEntryIds)
                ->whereHas('account', function($query) use ($coa) {
                    $query->where('code', $coa->kode_akun);
                })
                ->sum('debit');
                
            $credit = JournalLine::whereIn('journal_entry_id', $journalEntryIds)
                ->whereHas('account', function($query) use ($coa) {
                    $query->where('code', $coa->kode_akun);
                })
                ->sum('credit');
            
            // Hitung saldo akhir dengan metode yang sama seperti buku besar
            // (selalu: saldo_akhir = saldo_awal + debit - credit)
            $saldoAkhir = $saldoAwal + $debit - $credit;
            
            $totals[$coa->kode_akun] = [
                'saldo_awal' => $saldoAwal,
                'debit' => $debit,
                'kredit' => $credit,
                'saldo_akhir' => $saldoAkhir
            ];
        }

        return view('akuntansi.neraca-saldo', compact('periode','periods','coas','totals'));
    }

    /**
     * Get saldo awal untuk periode tertentu
     */
    private function getSaldoAwalPeriode($coa, $periode)
    {
        // Cek apakah ada saldo periode
        $periodBalance = CoaPeriodBalance::where('kode_akun', $coa->kode_akun)
            ->where('period_id', $periode->id)
            ->first();
        
        if ($periodBalance) {
            return $periodBalance->saldo_awal;
        }
        
        // Jika tidak ada, cek periode sebelumnya
        $previousPeriod = $periode->getPreviousPeriod();
        if ($previousPeriod) {
            $previousBalance = CoaPeriodBalance::where('kode_akun', $coa->kode_akun)
                ->where('period_id', $previousPeriod->id)
                ->first();
            
            if ($previousBalance) {
                return $previousBalance->saldo_akhir;
            }
        }
        
        // Jika tidak ada periode sebelumnya, gunakan saldo awal dari COA
        return $coa->saldo_awal ?? 0;
    }

    public function labaRugi(Request $request)
    {
        $from = $request->get('from');
        $to   = $request->get('to');

        $revenue = Account::where('type','revenue')->get();
        $expense = Account::where('type','expense')->get();
        $sum = function($accs) use ($from,$to) {
            $total = 0.0;
            foreach ($accs as $acc) {
                $q = JournalLine::where('account_id',$acc->id)->with('entry');
                if ($from) { $q->whereHas('entry', fn($qq)=>$qq->whereDate('tanggal','>=',$from)); }
                if ($to)   { $q->whereHas('entry', fn($qq)=>$qq->whereDate('tanggal','<=',$to)); }
                $row = $q->selectRaw('COALESCE(SUM(debit),0) as d, COALESCE(SUM(credit),0) as c')->first();
                $balance = ($acc->type==='revenue') ? (float)($row->c - $row->d) : (float)($row->d - $row->c);
                $total += $balance;
            }
            return $total;
        };
        $totalRevenue = $sum($revenue);
        $totalExpense = $sum($expense);
        $laba = $totalRevenue - $totalExpense;

        return view('akuntansi.laba-rugi', compact('from','to','totalRevenue','totalExpense','laba','revenue','expense'));
    }

    public function neraca(Request $request)
    {
        $periode = $request->get('periode', now()->format('Y-m'));
        
        // Get COA data for neraca
        $aset = \App\Models\Coa::where('kategori_akun', 'ASET')->orderBy('kode_akun')->get();
        $kewajiban = \App\Models\Coa::where('kategori_akun', 'KEWAJIBAN')->orderBy('kode_akun')->get();
        $modal = \App\Models\Coa::where('kategori_akun', 'MODAL')->orderBy('kode_akun')->get();
        
        // Calculate balances for each account
        $calculateBalance = function($coa) use ($periode) {
            $saldo = 0;
            
            // Get journal entries for this account up to the selected period
            $entries = \App\Models\JurnalEntry::whereHas('details', function($q) use ($coa) {
                $q->where('kode_akun', $coa->kode_akun);
            })->whereDate('tanggal', '<=', $periode . '-31')->get();
            
            foreach ($entries as $entry) {
                $detail = $entry->details->where('kode_akun', $coa->kode_akun)->first();
                if ($detail) {
                    if ($coa->saldo_normal === 'debit') {
                        $saldo += $detail->debit - $detail->kredit;
                    } else {
                        $saldo += $detail->kredit - $detail->debit;
                    }
                }
            }
            
            // Add initial balance
            $saldo += $coa->saldo_awal ?? 0;
            
            return $saldo;
        };
        
        // Calculate totals
        $totalAset = $aset->sum(function($coa) use ($calculateBalance) {
            return $calculateBalance($coa);
        });
        
        $totalKewajiban = $kewajiban->sum(function($coa) use ($calculateBalance) {
            return $calculateBalance($coa);
        });
        
        $totalModal = $modal->sum(function($coa) use ($calculateBalance) {
            return $calculateBalance($coa);
        });
        
        return view('akuntansi.neraca', compact('periode', 'aset', 'kewajiban', 'modal', 'totalAset', 'totalKewajiban', 'totalModal', 'calculateBalance'));
    }
}
