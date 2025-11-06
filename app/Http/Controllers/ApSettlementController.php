<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ApSettlement;
use App\Models\Pembelian;
use App\Models\Vendor;
use App\Services\JournalService;

class ApSettlementController extends Controller
{
    public function index()
    {
        $rows = ApSettlement::with(['pembelian.vendor'])->orderBy('tanggal','desc')->paginate(20);
        // daftar pembelian kredit yang belum lunas
        $openPurchases = Pembelian::with('vendor')
            ->where('payment_method','credit')
            ->orderBy('tanggal','desc')->get();
        return view('transaksi.ap-settlement.index', compact('rows','openPurchases'));
    }

    public function create(Request $request)
    {
        $pembelian = Pembelian::with('vendor')->findOrFail($request->get('pembelian_id'));
        return view('transaksi.ap-settlement.create', compact('pembelian'));
    }

    public function store(Request $request, JournalService $journal)
    {
        $request->validate([
            'tanggal'=>'required|date',
            'pembelian_id'=>'required|exists:pembelians,id',
            'total_tagihan'=>'required|numeric|min:0',
            'diskon'=>'nullable|numeric|min:0',
            'denda_bunga'=>'nullable|numeric|min:0',
            'dibayar_bersih'=>'required|numeric|min:0',
            'coa_kasbank'=>'required',
        ]);

        // Cek saldo kas/bank cukup untuk pelunasan
        $cashCode = (string)($request->coa_kasbank ?? '101');
        $saldoAwal = (float) (\App\Models\Coa::where('kode_akun', $cashCode)->value('saldo_awal') ?? 0);
        $acc = \App\Models\Account::where('code', $cashCode)->first();
        $journalBalance = 0.0;
        if ($acc) {
            $journalBalance = (float) (\App\Models\JournalLine::where('account_id', $acc->id)
                ->selectRaw('COALESCE(SUM(debit - credit),0) as bal')->value('bal') ?? 0);
        }
        $cashBalance = $saldoAwal + $journalBalance;
        if ($cashBalance + 1e-6 < (float)$request->dibayar_bersih) {
            return back()->withErrors([
                'kas' => 'Nominal kas tidak cukup untuk melakukan transaksi. Saldo kas saat ini: Rp '.number_format($cashBalance,0,',','.').' ; Nominal transaksi: Rp '.number_format((float)$request->dibayar_bersih,0,',','.'),
            ])->withInput();
        }

        $pembelian = Pembelian::with('vendor')->findOrFail($request->pembelian_id);

        $row = ApSettlement::create([
            'tanggal'=>$request->tanggal,
            'vendor_id'=>$pembelian->vendor_id,
            'pembelian_id'=>$pembelian->id,
            'total_tagihan'=>$request->total_tagihan,
            'diskon'=>$request->diskon ?? 0,
            'denda_bunga'=>$request->denda_bunga ?? 0,
            'dibayar_bersih'=>$request->dibayar_bersih,
            'metode_bayar'=>$request->metode_bayar ?? 'cash',
            'coa_kasbank'=>$request->coa_kasbank,
            'keterangan'=>$request->keterangan,
            'status'=>'lunas',
            'user_id'=>auth()->id(),
        ]);

        // Jurnal Pelunasan Utang (Opsi A: diskon ke 506 Penyesuaian HPP)
        $entries = [];
        $entries[] = ['code'=>'201','debit'=>(float)$request->total_tagihan, 'credit'=>0]; // Dr AP
        if (($request->diskon ?? 0) > 0) {
            $entries[] = ['code'=>'506','debit'=>0,'credit'=>(float)$request->diskon]; // Cr Diskon (Penyesuaian HPP)
        }
        if (($request->denda_bunga ?? 0) > 0) {
            $entries[] = ['code'=>'701','debit'=>(float)$request->denda_bunga,'credit'=>0]; // Dr Beban Bunga
        }
        $entries[] = ['code'=>$request->coa_kasbank,'debit'=>0,'credit'=>(float)$request->dibayar_bersih]; // Cr Kas/Bank

        $journal->post($request->tanggal, 'ap_settlement', (int)$row->id, 'Pelunasan Utang Pembelian #'.$pembelian->id, $entries);

        // update status pembelian (sederhana: langsung lunas)
        $pembelian->status = 'lunas';
        $pembelian->save();

        return redirect()->route('transaksi.ap-settlement.index')->with('success','Pelunasan utang berhasil.');
    }

    public function show($id)
    {
        $row = ApSettlement::with(['pembelian.vendor'])->findOrFail($id);
        return view('transaksi.ap-settlement.show', compact('row'));
    }
}
