<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\Account;

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

    public function bukuBesar(Request $request)
    {
        $from = $request->get('from');
        $to   = $request->get('to');
        $accountId = $request->get('account_id');

        $accounts = Account::orderBy('code')->get();
        $lines = collect();
        $saldoAwal = 0.0;

        if ($accountId) {
            $q = JournalLine::with(['entry'])
                ->where('account_id', $accountId)
                ->orderBy(JournalLine::query()->getModel()->getTable().'.id','asc');
            if ($from) {
                $saldoAwal = JournalLine::where('account_id',$accountId)
                    ->whereHas('entry', function($qq) use ($from) { $qq->whereDate('tanggal','<',$from); })
                    ->selectRaw('COALESCE(SUM(debit - credit),0) as sal')->value('sal') ?? 0;
                $q->whereHas('entry', function($qq) use ($from) { $qq->whereDate('tanggal','>=',$from); });
            }
            if ($to) {
                $q->whereHas('entry', function($qq) use ($to) { $qq->whereDate('tanggal','<=',$to); });
            }
            $lines = $q->get();
        }

        return view('akuntansi.buku-besar', compact('accounts','accountId','lines','from','to','saldoAwal'));
    }

    public function neracaSaldo(Request $request)
    {
        $from = $request->get('from');
        $to   = $request->get('to');

        $accounts = Account::orderBy('code')->get();
        $totals = [];
        foreach ($accounts as $acc) {
            $q = JournalLine::where('account_id', $acc->id)->with('entry');
            if ($from) { $q->whereHas('entry', fn($qq)=>$qq->whereDate('tanggal','>=',$from)); }
            if ($to)   { $q->whereHas('entry', fn($qq)=>$qq->whereDate('tanggal','<=',$to)); }
            $sum = $q->selectRaw('COALESCE(SUM(debit),0) as d, COALESCE(SUM(credit),0) as c')->first();
            $totals[$acc->id] = ['debit'=>(float)($sum->d ?? 0), 'credit'=>(float)($sum->c ?? 0)];
        }

        return view('akuntansi.neraca-saldo', compact('accounts','totals','from','to'));
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
}
