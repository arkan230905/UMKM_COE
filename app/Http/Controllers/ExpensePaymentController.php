<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ExpensePayment;
use App\Models\Coa;
use App\Services\JournalService;

class ExpensePaymentController extends Controller
{
    public function index()
    {
        $rows = ExpensePayment::with('coa')->orderBy('tanggal','desc')->paginate(20);
        return view('transaksi.expense-payment.index', compact('rows'));
    }

    public function create()
    {
        $coas = Coa::whereIn('tipe_akun', ['Expense'])->orderBy('kode_akun')->get();
        $kasbank = Coa::whereIn('kode_akun', ['101'])->get();
        return view('transaksi.expense-payment.create', compact('coas','kasbank'));
    }

    public function store(Request $request, JournalService $journal)
    {
        $request->validate([
            'tanggal'=>'required|date',
            'coa_beban_id'=>'required|exists:coas,id',
            'coa_kasbank'=>'required',
            'nominal'=>'required|numeric|min:0',
        ]);

        $row = ExpensePayment::create([
            'tanggal'=>$request->tanggal,
            'coa_beban_id'=>$request->coa_beban_id,
            'metode_bayar'=>$request->metode_bayar ?? 'cash',
            'coa_kasbank'=>$request->coa_kasbank ?? '101',
            'nominal'=>$request->nominal,
            'deskripsi'=>$request->deskripsi,
            'user_id'=>auth()->id(),
        ]);

        // Jurnal: Dr Expense ; Cr Cash/Bank
        $coa = Coa::findOrFail($request->coa_beban_id);
        $journal->post($request->tanggal, 'expense_payment', (int)$row->id, 'Pembayaran Beban - '.$coa->nama_akun, [
            ['code'=>$coa->kode_akun, 'debit'=>(float)$request->nominal, 'credit'=>0],
            ['code'=>$request->coa_kasbank, 'debit'=>0, 'credit'=>(float)$request->nominal],
        ]);

        return redirect()->route('transaksi.expense-payment.index')->with('success','Pembayaran beban berhasil.');
    }

    public function show($id)
    {
        $row = ExpensePayment::with('coa')->findOrFail($id);
        return view('transaksi.expense-payment.show', compact('row'));
    }
}
