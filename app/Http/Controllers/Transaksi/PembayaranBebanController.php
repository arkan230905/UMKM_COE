<?php

namespace App\Http\Controllers\Transaksi;

use App\Http\Controllers\Controller;
use App\Models\PembayaranBeban;
use App\Models\Coa;
use App\Models\Jurnal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\BopLainnya;

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
        try {
            // Cari akun beban yang sudah ada budget di BOP Lainnya
            $akunBeban = BopLainnya::where('budget', '>', 0)
                ->where('is_active', 1)
                ->with(['coa'])
                ->get()
                ->map(function($bop) {
                    return $bop->coa;
                })
                ->filter(function($coa) {
                    return $coa; // Hanya filter yang COA-nya ada
                })
                ->unique('kode_akun'); // Hapus duplikat berdasarkan kode_akun
                
            // Cari akun kas (kode 101-102 untuk kas dan bank)
            $akunKas = Coa::where(function ($q) {
                $q->where('kode_akun', 'like', '101%') // Kas
                  ->orWhere('kode_akun', 'like', '102%') // Bank
                  ->orWhere('kode_akun', 'like', '103%'); 
            })
            ->orderBy('kode_akun')
            ->get();
            
            if ($akunBeban->isEmpty()) {
                $error = 'Akun beban dengan budget belum diatur. ';
                $error .= 'Tidak ada akun beban dengan budget yang aktif. ';
                return back()->with('error', $error);
            }
            
            if ($akunKas->isEmpty()) {
                // Warning only, not blocking
                \Log::warning('Tidak ada akun kas/bank yang aktif untuk pembayaran beban');
            }
            
            return view('transaksi.pembayaran-beban.create', compact('akunBeban', 'akunKas'));
            
        } catch (\Exception $e) {
            \Log::error('Error in PembayaranBebanController@create: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat memuat form: ' . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        // Validasi input
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'keterangan' => 'required|string|max:255',
            'akun_beban_id' => 'required|exists:coas,id',
            'akun_kas_id' => 'required|exists:coas,id|different:akun_beban_id',
            'jumlah' => 'required|numeric|min:1',
            'catatan' => 'nullable|string',
        ], [
            'akun_kas_id.different' => 'Akun Kas dan Akun Beban tidak boleh sama',
            'akun_beban_id.exists' => 'Akun beban tidak valid',
            'akun_kas_id.exists' => 'Akun kas tidak valid',
        ]);

        DB::beginTransaction();
        
        try {
            // Dapatkan data COA dengan pengecekan yang lebih ketat
            $beban = Coa::find($request->akun_beban_id);
            $kas = Coa::find($request->akun_kas_id);
            
            // Validasi COA
            if (!$beban) {
                throw new \Exception('Akun beban tidak ditemukan');
            }
            
            if (!$kas) {
                throw new \Exception('Akun kas tidak ditemukan');
            }
            
            // Validasi saldo kas
            if ($kas->saldo < $request->jumlah) {
                return back()
                    ->with('error', 'Saldo kas tidak mencukupi. Saldo tersedia: ' . format_rupiah($kas->saldo))
                    ->withInput();
            }
            
            // Generate kode transaksi
            $lastPembayaran = PembayaranBeban::withTrashed()->latest('id')->first();
            $count = $lastPembayaran ? ($lastPembayaran->id + 1) : 1;
            $kodeTransaksi = 'PB-' . date('Ymd') . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
            
            // Simpan pembayaran beban
            $pembayaran = new PembayaranBeban([
                'kode_transaksi' => $kodeTransaksi,
                'tanggal' => $request->tanggal,
                'keterangan' => $request->keterangan,
                'akun_beban_id' => $beban->id,
                'akun_kas_id' => $kas->id,
                'jumlah' => $request->jumlah,
                'catatan' => $request->catatan,
                'user_id' => auth()->id(),
                'status' => 'lunas',
            ]);
            
            if (!$pembayaran->save()) {
                throw new \Exception('Gagal menyimpan data pembayaran beban');
            }

            // Update saldo akun
            $beban->saldo = ($beban->saldo ?? 0) + $request->jumlah;
            $kas->saldo = ($kas->saldo ?? 0) - $request->jumlah;
            
            if (!$beban->save() || !$kas->save()) {
                throw new \Exception('Gagal memperbarui saldo akun');
            }

            // Generate nomor jurnal
            $lastJurnal = Jurnal::withTrashed()->latest('id')->first();
            $jurnalCount = $lastJurnal ? ($lastJurnal->id + 1) : 1;
            $noJurnal = 'J-' . date('Ymd') . '-' . str_pad($jurnalCount, 5, '0', STR_PAD_LEFT);
            
            // Data jurnal untuk beban (debit)
            $jurnalBeban = [
                'no_jurnal' => $noJurnal,
                'tanggal' => $request->tanggal,
                'coa_id' => $beban->id,
                'keterangan' => 'Pembayaran Beban: ' . $request->keterangan,
                'debit' => $request->jumlah,
                'kredit' => 0,
                'referensi' => $kodeTransaksi,
                'user_id' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ];
            
            // Data jurnal untuk kas (kredit)
            $jurnalKas = [
                'no_jurnal' => $noJurnal,
                'tanggal' => $request->tanggal,
                'coa_id' => $kas->id,
                'keterangan' => 'Pembayaran Beban: ' . $request->keterangan,
                'debit' => 0,
                'kredit' => $request->jumlah,
                'referensi' => $kodeTransaksi,
                'user_id' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ];
            
            // Simpan jurnal dalam batch
            if (!Jurnal::insert([$jurnalBeban, $jurnalKas])) {
                throw new \Exception('Gagal menyimpan jurnal transaksi');
            }

            DB::commit();
            
            return redirect()
                ->route('transaksi.pembayaran-beban.show', $pembayaran->id)
                ->with('success', 'Pembayaran beban berhasil disimpan');
                
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error in PembayaranBebanController@store: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            
            $errorMessage = 'Gagal menyimpan pembayaran beban';
            if (strpos($e->getMessage(), 'No query results for model') !== false) {
                $errorMessage = 'Data COA tidak valid. Pastikan akun beban dan kas sudah diatur dengan benar.';
            }
            
            return back()
                ->with('error', $errorMessage)
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
