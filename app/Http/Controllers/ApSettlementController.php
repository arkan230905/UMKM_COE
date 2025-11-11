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
        $openPurchases = Pembelian::with(['vendor', 'details.bahanBaku'])
            ->where('payment_method','credit')
            ->where(function($q) {
                $q->where('status', '!=', 'lunas')
                  ->orWhereNull('status');
            })
            ->orderBy('tanggal','desc')->get();
        return view('transaksi.ap-settlement.index', compact('rows','openPurchases'));
    }

    public function create(Request $request)
    {
        $pembelian = Pembelian::with('vendor')->findOrFail($request->get('pembelian_id'));
        $kasbank = \App\Helpers\AccountHelper::getKasBankAccounts();
        return view('transaksi.ap-settlement.create', compact('pembelian', 'kasbank'));
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

        // Jurnal Pelunasan Utang
        $entries = [];
        $entries[] = ['code'=>'2101','debit'=>(float)$request->total_tagihan, 'credit'=>0]; // Dr Hutang Usaha
        if (($request->diskon ?? 0) > 0) {
            $entries[] = ['code'=>'5105','debit'=>0,'credit'=>(float)$request->diskon]; // Cr Penyesuaian HPP (Diskon Pembelian)
        }
        if (($request->denda_bunga ?? 0) > 0) {
            $entries[] = ['code'=>'5104','debit'=>(float)$request->denda_bunga,'credit'=>0]; // Dr Beban Denda dan Bunga
        }
        $entries[] = ['code'=>$request->coa_kasbank,'debit'=>0,'credit'=>(float)$request->dibayar_bersih]; // Cr Kas/Bank

        $journal->post($request->tanggal, 'ap_settlement', (int)$row->id, 'Pelunasan Utang Pembelian #'.$pembelian->id, $entries);

        // Update terbayar dan sisa_pembayaran di pembelian
        $pembelian->terbayar = ($pembelian->terbayar ?? 0) + (float)$request->dibayar_bersih;
        $pembelian->sisa_pembayaran = max(0, ($pembelian->total_harga ?? 0) - $pembelian->terbayar);
        
        // Update status pembelian
        if ($pembelian->sisa_pembayaran <= 0 || $pembelian->terbayar >= ($pembelian->total_harga ?? 0)) {
            $pembelian->status = 'lunas';
            $pembelian->sisa_pembayaran = 0;
        } else {
            $pembelian->status = 'belum_lunas';
        }
        $pembelian->save();

        return redirect()->route('transaksi.ap-settlement.index')->with('success','Pelunasan utang berhasil.');
    }

    public function show($id)
    {
        $row = ApSettlement::with(['pembelian.vendor'])->findOrFail($id);
        return view('transaksi.ap-settlement.show', compact('row'));
    }

    public function edit($id)
    {
        $row = ApSettlement::with(['pembelian.vendor'])->findOrFail($id);
        $pembelian = $row->pembelian;
        return view('transaksi.ap-settlement.edit', compact('row', 'pembelian'));
    }

    public function update(Request $request, $id, JournalService $journal)
    {
        $request->validate([
            'tanggal'=>'required|date',
            'total_tagihan'=>'required|numeric|min:0',
            'diskon'=>'nullable|numeric|min:0',
            'denda_bunga'=>'nullable|numeric|min:0',
            'dibayar_bersih'=>'required|numeric|min:0',
            'coa_kasbank'=>'required',
        ]);

        $row = ApSettlement::with('pembelian')->findOrFail($id);
        $oldDibayar = $row->dibayar_bersih;
        $pembelian = $row->pembelian;

        // Cek saldo kas/bank cukup (hitung selisih jika nominal berubah)
        $cashCode = (string)($request->coa_kasbank ?? '101');
        $selisih = (float)$request->dibayar_bersih - (float)$oldDibayar;
        
        if ($selisih > 0) {
            $saldoAwal = (float) (\App\Models\Coa::where('kode_akun', $cashCode)->value('saldo_awal') ?? 0);
            $acc = \App\Models\Account::where('code', $cashCode)->first();
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
            'total_tagihan'=>$request->total_tagihan,
            'diskon'=>$request->diskon ?? 0,
            'denda_bunga'=>$request->denda_bunga ?? 0,
            'dibayar_bersih'=>$request->dibayar_bersih,
            'metode_bayar'=>$request->metode_bayar ?? 'cash',
            'coa_kasbank'=>$request->coa_kasbank,
            'keterangan'=>$request->keterangan,
        ]);

        // Hapus jurnal lama
        \App\Models\JournalEntry::where('ref_type', 'ap_settlement')
            ->where('ref_id', $row->id)
            ->delete();

        // Jurnal baru
        $entries = [];
        $entries[] = ['code'=>'2101','debit'=>(float)$request->total_tagihan, 'credit'=>0];  // Dr Hutang Usaha
        if (($request->diskon ?? 0) > 0) {
            $entries[] = ['code'=>'5105','debit'=>0,'credit'=>(float)$request->diskon];  // Cr Penyesuaian HPP (Diskon Pembelian)
        }
        if (($request->denda_bunga ?? 0) > 0) {
            $entries[] = ['code'=>'5104','debit'=>(float)$request->denda_bunga,'credit'=>0];  // Dr Beban Denda dan Bunga
        }
        $entries[] = ['code'=>$request->coa_kasbank,'debit'=>0,'credit'=>(float)$request->dibayar_bersih];  // Cr Kas/Bank

        $journal->post($request->tanggal, 'ap_settlement', (int)$row->id, 'Pelunasan Utang Pembelian #'.$pembelian->id, $entries);

        // Update terbayar di pembelian (kurangi pembayaran lama, tambah pembayaran baru)
        $pembelian->terbayar = ($pembelian->terbayar ?? 0) - (float)$oldDibayar + (float)$request->dibayar_bersih;
        $pembelian->sisa_pembayaran = max(0, ($pembelian->total_harga ?? 0) - $pembelian->terbayar);
        
        if ($pembelian->sisa_pembayaran <= 0 || $pembelian->terbayar >= ($pembelian->total_harga ?? 0)) {
            $pembelian->status = 'lunas';
            $pembelian->sisa_pembayaran = 0;
        } else {
            $pembelian->status = 'belum_lunas';
        }
        $pembelian->save();

        return redirect()->route('transaksi.ap-settlement.index')->with('success','Pelunasan utang berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $row = ApSettlement::with('pembelian')->findOrFail($id);
        $pembelian = $row->pembelian;
        
        // Kembalikan terbayar di pembelian
        if ($pembelian) {
            $pembelian->terbayar = max(0, ($pembelian->terbayar ?? 0) - (float)$row->dibayar_bersih);
            $pembelian->sisa_pembayaran = max(0, ($pembelian->total_harga ?? 0) - $pembelian->terbayar);
            
            if ($pembelian->sisa_pembayaran > 0) {
                $pembelian->status = 'belum_lunas';
            }
            $pembelian->save();
        }
        
        // Hapus jurnal terkait
        \App\Models\JournalEntry::where('ref_type', 'ap_settlement')
            ->where('ref_id', $row->id)
            ->delete();
        
        $row->delete();

        return redirect()->route('transaksi.ap-settlement.index')->with('success','Pelunasan utang berhasil dihapus.');
    }
}
