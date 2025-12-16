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
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AkuntansiController extends Controller
{
    public function jurnalUmum(Request $request)
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

        return view('akuntansi.jurnal-umum', compact('entries','from','to','refType','refId'));
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
        $from = $request->get('from');
        $to   = $request->get('to');
        $accountId = $request->get('account_id');

        $accountBaseQuery = Account::query()
            ->join('coas', 'coas.kode_akun', '=', 'accounts.code')
            ->select('accounts.*');

        if (Schema::hasColumn('coas', 'is_active')) {
            $accountBaseQuery->where('coas.is_active', true);
        }

        if (Schema::hasColumn('coas', 'is_akun_header')) {
            $accountBaseQuery->where(function ($query) {
                $query->where('coas.is_akun_header', false)
                    ->orWhereNull('coas.is_akun_header');
            });
        }

        $accounts = (clone $accountBaseQuery)
            ->orderBy('accounts.code')
            ->get();
        $lines = collect();
        $saldoAwal = 0.0;

        if ($accountId) {
            $account = (clone $accountBaseQuery)
                ->where('accounts.id', $accountId)
                ->first();

            if (!$account) {
                abort(404);
            }

            // Ambil saldo awal dari COA
            $coa = \App\Models\Coa::where('kode_akun', $account->code)->first();
            $saldoAwalCoa = $coa ? (float)($coa->saldo_awal ?? 0) : 0;
            
            // Hitung mutasi sebelum periode
            $mutasiSebelumPeriode = 0.0;
            if ($from) {
                $mutasiSebelumPeriode = JournalLine::where('account_id',$accountId)
                    ->whereHas('entry', function($qq) use ($from) { $qq->whereDate('tanggal','<',$from); })
                    ->selectRaw('COALESCE(SUM(debit - credit),0) as sal')->value('sal') ?? 0;
            }
            
            // Saldo awal = saldo awal COA + mutasi sebelum periode
            $saldoAwal = $saldoAwalCoa + $mutasiSebelumPeriode;
            
            // Ambil transaksi dalam periode
            $q = JournalLine::with(['entry'])
                ->where('account_id', $accountId)
                ->orderBy(JournalLine::query()->getModel()->getTable().'.id','asc');
            
            if ($from) {
                $q->whereHas('entry', function($qq) use ($from) { $qq->whereDate('tanggal','>=',$from); });
            }
            if ($to) {
                $q->whereHas('entry', function($qq) use ($to) { $qq->whereDate('tanggal','<=',$to); });
            }
            $lines = $q->get()->map(function ($line) {
                $entry = $line->entry;

                if ($entry) {
                    $type = $entry->ref_type ?? '';
                    $refId = $entry->ref_id ?? '';

                    $prefixMap = [
                        'expense_payment' => 'PB',       // Pembayaran Beban
                        'expense' => 'BEBAN',
                        'asset' => 'AST',
                        'journal' => 'JUR',
                        'purchase' => 'PO',
                        'sale' => 'SO',
                    ];

                    $prefix = $prefixMap[$type] ?? 'TRX';
                    $suffix = $refId !== ''
                        ? (is_numeric($refId) ? str_pad((string) $refId, 3, '0', STR_PAD_LEFT) : strtoupper($refId))
                        : null;

                    $line->display_ref = $suffix ? ($prefix . '-' . $suffix) : $prefix;
                } else {
                    $line->display_ref = '-';
                }

                return $line;
            });
        }

        return view('akuntansi.buku-besar', compact('accounts','accountId','lines','from','to','saldoAwal'));
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
            // Get saldo awal dari periode
            $saldoAwal = $this->getSaldoAwalPeriode($coa, $periode);
            
            // Hitung mutasi dalam periode menggunakan coa_id
            $debit = JurnalUmum::where('coa_id', $coa->id)
                ->whereBetween('tanggal', [$from, $to])
                ->sum('debit');
            
            $kredit = JurnalUmum::where('coa_id', $coa->id)
                ->whereBetween('tanggal', [$from, $to])
                ->sum('kredit');
            
            // Hitung saldo akhir
            if ($coa->saldo_normal === 'debit') {
                $saldoAkhir = $saldoAwal + $debit - $kredit;
            } else {
                $saldoAkhir = $saldoAwal + $kredit - $debit;
            }
            
            $totals[$coa->kode_akun] = [
                'saldo_awal' => $saldoAwal,
                'debit' => (float)$debit,
                'kredit' => (float)$kredit,
                'saldo_akhir' => $saldoAkhir,
            ];
        }

        return view('akuntansi.neraca-saldo', compact('coas','totals','from','to','periode','periods'));
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
        $selectedPeriod = $request->input('period', now()->format('Y-m'));

        try {
            $periodDate = Carbon::createFromFormat('Y-m', $selectedPeriod)->startOfMonth();
        } catch (\Exception $exception) {
            $periodDate = now()->startOfMonth();
            $selectedPeriod = $periodDate->format('Y-m');
        }

        $startDate = $periodDate->copy()->startOfMonth();
        $endDate = $periodDate->copy()->endOfMonth();

        $summaryLines = JournalLine::selectRaw('account_id, SUM(debit) as total_debit, SUM(credit) as total_credit')
            ->whereHas('entry', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('tanggal', [$startDate->toDateString(), $endDate->toDateString()]);
            })
            ->whereHas('account', function ($query) {
                $query->whereIn('type', ['revenue', 'expense']);
            })
            ->with('account')
            ->groupBy('account_id')
            ->get();

        $revenueAccounts = $summaryLines->filter(fn ($line) => $line->account && $line->account->type === 'revenue')
            ->map(function ($line) {
                $amount = (float) ($line->total_credit - $line->total_debit);
                if (abs($amount) < 0.0001) {
                    return null;
                }

                return [
                    'code' => $line->account->code,
                    'name' => $line->account->name,
                    'amount' => $amount,
                ];
            })
            ->filter()
            ->values();

        $expenseAccounts = $summaryLines->filter(fn ($line) => $line->account && $line->account->type === 'expense')
            ->map(function ($line) {
                $amount = (float) ($line->total_debit - $line->total_credit);
                if (abs($amount) < 0.0001) {
                    return null;
                }

                return [
                    'code' => $line->account->code,
                    'name' => $line->account->name,
                    'amount' => $amount,
                ];
            })
            ->filter()
            ->values();

        $totalRevenue = $revenueAccounts->sum('amount');
        $totalExpense = $expenseAccounts->sum('amount');
        $netProfit = $totalRevenue - $totalExpense;

        $periodLabel = $periodDate->translatedFormat('F Y');

        return view('akuntansi.laba-rugi', [
            'period' => $selectedPeriod,
            'periodLabel' => $periodLabel,
            'revenueAccounts' => $revenueAccounts,
            'expenseAccounts' => $expenseAccounts,
            'totalRevenue' => $totalRevenue,
            'totalExpense' => $totalExpense,
            'netProfit' => $netProfit,
        ]);
    }
}
