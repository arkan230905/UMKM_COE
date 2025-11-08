<?php

namespace App\Http\Controllers\Transaksi;

use App\Http\Controllers\Controller;
use App\Models\PembayaranBeban;
use App\Models\Coa;
use App\Models\Jurnal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PembayaranBebanController extends Controller
{
    public function index()
    {
        $pembayaranBeban = PembayaranBeban::with(['coaBeban', 'coaKas'])
            ->latest()
            ->paginate(15);
            
        return view('transaksi.pembayaran-beban.index', compact('pembayaranBeban'));
    }

    public function create()
    {
        $akunBeban = Coa::where('kategori', 'beban')
            ->where('is_active', 1)
            ->orderBy('kode')
            ->get();
            
        $akunKas = Coa::where('kategori', 'kas')
            ->where('is_active', 1)
            ->orderBy('kode')
            ->get();
        
        return view('transaksi.pembayaran-beban.create', compact('akunBeban', 'akunKas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'keterangan' => 'required|string|max:255',
            'akun_beban_id' => 'required|exists:coas,id',
            'akun_kas_id' => 'required|exists:coas,id|different:akun_beban_id',
            'jumlah' => 'required|numeric|min:1',
            'catatan' => 'nullable|string',
        ], [
            'akun_kas_id.different' => 'Akun Kas dan Akun Beban tidak boleh sama',
        ]);

        DB::beginTransaction();
        
        try {
            // Cek saldo kas
            $kas = Coa::findOrFail($request->akun_kas_id);
            if ($kas->saldo < $request->jumlah) {
                return back()
                    ->with('error', 'Saldo kas tidak mencukupi. Saldo tersedia: ' . format_rupiah($kas->saldo))
                    ->withInput();
            }
            
            // Generate kode transaksi
            $count = PembayaranBeban::withTrashed()->count() + 1;
            $kodeTransaksi = 'PB-' . date('Ymd') . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
            
            // Simpan pembayaran beban
            $pembayaran = PembayaranBeban::create([
                'kode_transaksi' => $kodeTransaksi,
                'tanggal' => $request->tanggal,
                'keterangan' => $request->keterangan,
                'akun_beban_id' => $request->akun_beban_id,
                'akun_kas_id' => $request->akun_kas_id,
                'jumlah' => $request->jumlah,
                'catatan' => $request->catatan,
                'user_id' => auth()->id(),
                'status' => 'lunas',
            ]);

            // Update saldo akun
            $beban = Coa::findOrFail($request->akun_beban_id);
            $beban->saldo += $request->jumlah;
            $beban->save();

            $kas->saldo -= $request->jumlah;
            $kas->save();

            // Catat jurnal
            $noJurnal = 'J-' . date('Ymd') . '-' . str_pad(Jurnal::withTrashed()->count() + 1, 5, '0', STR_PAD_LEFT);
            
            // Jurnal untuk beban (debit)
            Jurnal::create([
                'no_jurnal' => $noJurnal,
                'tanggal' => $request->tanggal,
                'coa_id' => $request->akun_beban_id,
                'keterangan' => 'Pembayaran Beban: ' . $request->keterangan,
                'debit' => $request->jumlah,
                'kredit' => 0,
                'referensi' => $pembayaran->kode_transaksi,
                'user_id' => auth()->id(),
            ]);
            
            // Jurnal untuk kas (kredit)
            Jurnal::create([
                'no_jurnal' => $noJurnal,
                'tanggal' => $request->tanggal,
                'coa_id' => $request->akun_kas_id,
                'keterangan' => 'Pembayaran Beban: ' . $request->keterangan,
                'debit' => 0,
                'kredit' => $request->jumlah,
                'referensi' => $pembayaran->kode_transaksi,
                'user_id' => auth()->id(),
            ]);

            DB::commit();
            
            return redirect()
                ->route('transaksi.pembayaran-beban.show', $pembayaran->id)
                ->with('success', 'Pembayaran beban berhasil disimpan');
                
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error in PembayaranBebanController@store: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            
            return back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show($id)
    {
        $pembayaran = PembayaranBeban::with(['coaBeban', 'coaKas', 'user'])
            ->findOrFail($id);
            
        $jurnals = Jurnal::where('referensi', $pembayaran->kode_transaksi)
            ->with('coa')
            ->get();
            
        return view('transaksi.pembayaran-beban.show', compact('pembayaran', 'jurnals'));
    }
    
    public function print($id)
    {
        $pembayaran = PembayaranBeban::with(['coaBeban', 'coaKas', 'user'])
            ->findOrFail($id);
            
        return view('transaksi.pembayaran-beban.print', compact('pembayaran'));
    }
}
