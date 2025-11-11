<?php

namespace App\Http\Controllers\Transaksi;

use App\Http\Controllers\Controller;
use App\Models\PelunasanUtang;
use App\Models\Coa;
use App\Models\Pembelian;
use App\Models\Jurnal;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PelunasanUtangController extends Controller
{
    public function index(Request $request)
    {
        $query = PelunasanUtang::with(['pembelian.vendor', 'akunKas'])
            ->latest();

        // Filter berdasarkan tanggal
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('tanggal', [
                $request->start_date,
                $request->end_date
            ]);
        }

        // Filter berdasarkan vendor
        if ($request->has('vendor_id')) {
            $query->whereHas('pembelian', function($q) use ($request) {
                $q->where('vendor_id', $request->vendor_id);
            });
        }

        $pelunasanUtang = $query->paginate(15);
        $vendors = \App\Models\Vendor::all();
        
        return view('transaksi.pelunasan-utang.index', compact('pelunasanUtang', 'vendors'));
    }

    public function create()
    {
        $pembayarans = Pembelian::where('payment_method', 'credit')
            ->where(function($q) {
                $q->where('status', 'belum_lunas')
                  ->orWhereRaw('sisa_pembayaran > 0')
                  ->orWhereRaw('terbayar < total_harga');
            })
            ->with('vendor')
            ->get();
            
        // Ambil akun kas/bank menggunakan helper untuk konsistensi
        $akunKas = \App\Helpers\AccountHelper::getKasBankAccounts();
        
        return view('transaksi.pelunasan-utang.create', compact('pembayarans', 'akunKas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'pembelian_id' => 'required|exists:pembelians,id',
            'akun_kas_id' => 'required|exists:coas,id',
            'jumlah' => 'required|numeric|min:1',
            'keterangan' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $pembelian = Pembelian::with('vendor')->findOrFail($request->pembelian_id);
            $akunKas = Coa::findOrFail($request->akun_kas_id);
            $akunUtangDagang = Coa::where('kode', '211')->firstOrFail(); // Akun Utang Dagang
            
            // Validasi jumlah tidak melebihi sisa pembayaran
            $sisaUtang = max(0, ($pembelian->total_harga ?? 0) - ($pembelian->terbayar ?? 0));
            if ($request->jumlah > $sisaUtang) {
                return back()->with('error', 'Jumlah pembayaran melebihi sisa utang (Rp ' . number_format($sisaUtang, 0, ',', '.') . ')')
                    ->withInput();
            }
            
            // Cek saldo kas
            if ($akunKas->saldo < $request->jumlah) {
                return back()->with('error', 'Saldo kas tidak mencukupi')
                    ->withInput();
            }

            // Generate kode transaksi
            $kodeTransaksi = 'PU-' . date('Ymd') . '-' . strtoupper(Str::random(5));
            
            // Simpan pelunasan
            $pelunasan = PelunasanUtang::create([
                'kode_transaksi' => $kodeTransaksi,
                'tanggal' => $request->tanggal,
                'pembelian_id' => $request->pembelian_id,
                'akun_kas_id' => $request->akun_kas_id,
                'jumlah' => $request->jumlah,
                'keterangan' => $request->keterangan,
                'user_id' => auth()->id(),
                'status' => 'lunas'
            ]);

            // Update terbayar dan sisa_pembayaran di pembelian
            $pembelian->terbayar += $request->jumlah;
            $pembelian->sisa_pembayaran -= $request->jumlah;
            
            if ($pembelian->sisa_pembayaran <= 0 || $pembelian->terbayar >= $pembelian->total_harga) {
                $pembelian->status = 'lunas';
                $pembelian->sisa_pembayaran = 0;
                $pembelian->terbayar = $pembelian->total_harga; // Pastikan tidak melebihi total
            }
            $pembelian->save();

            // Update saldo akun kas
            $akunKas->saldo -= $request->jumlah;
            $akunKas->save();

            // Update saldo akun utang dagang
            $akunUtangDagang->saldo -= $request->jumlah;
            $akunUtangDagang->save();

            // Catat jurnal
            // 1. Debit Utang Dagang (mengurangi utang)
            Jurnal::create([
                'kode_jurnal' => 'J-' . date('Ymd') . '-' . strtoupper(Str::random(4)),
                'tanggal' => $request->tanggal,
                'coa_id' => $akunUtangDagang->id,
                'debit' => $request->jumlah,
                'kredit' => 0,
                'keterangan' => 'Pelunasan utang ' . $pembelian->kode_pembelian . ' - ' . $pembelian->vendor->nama,
                'referensi' => $kodeTransaksi,
                'user_id' => auth()->id()
            ]);

            // 2. Kredit Kas (mengurangi kas)
            Jurnal::create([
                'kode_jurnal' => 'J-' . date('Ymd') . '-' . strtoupper(Str::random(4)),
                'tanggal' => $request->tanggal,
                'coa_id' => $akunKas->id,
                'debit' => 0,
                'kredit' => $request->jumlah,
                'keterangan' => 'Pembayaran utang ' . $pembelian->kode_pembelian . ' - ' . $pembelian->vendor->nama,
                'referensi' => $kodeTransaksi,
                'user_id' => auth()->id()
            ]);

            DB::commit();
            return redirect()->route('transaksi.pelunasan-utang.show', $pelunasan->id)
                ->with('success', 'Pelunasan utang berhasil disimpan');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error saat menyimpan pelunasan utang: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show($id)
    {
        $pelunasan = PelunasanUtang::with([
            'pembelian.vendor', 
            'pembelian.details.bahanBaku',
            'akunKas'
        ])->findOrFail($id);

        // Format items for display
        $pelunasan->pembelian->items = $pelunasan->pembelian->details->map(function($detail) {
            if ($detail->bahanBaku) {
                $subtotal = ($detail->jumlah ?? 0) * ($detail->harga_satuan ?? 0);
                return sprintf(
                    '• %s (%s %s) - Rp %s = Rp %s',
                    $detail->bahanBaku->nama_bahan,
                    number_format($detail->jumlah, 0, ',', '.'),
                    $detail->bahanBaku->satuan ?? 'unit',
                    number_format($detail->harga_satuan, 0, ',', '.'),
                    number_format($subtotal, 0, ',', '.')
                );
            }
            return '';
        })->filter()->toArray();

        return view('transaksi.pelunasan-utang.show', compact('pelunasan'));
    }

    public function print($id)
    {
        $pelunasanUtang = PelunasanUtang::with([
            'pembelian.vendor', 
            'pembelian.details.bahanBaku',
            'akunKas',
            'jurnals.coa',
            'user'
        ])->findOrFail($id);
        
        // Format items for display in print view
        $pelunasanUtang->pembelian->items = $pelunasanUtang->pembelian->details->map(function($detail) {
            if ($detail->bahanBaku) {
                $subtotal = ($detail->jumlah ?? 0) * ($detail->harga_satuan ?? 0);
                return sprintf(
                    '• %s (%s %s) - Rp %s = Rp %s',
                    $detail->bahanBaku->nama_bahan,
                    number_format($detail->jumlah, 0, ',', '.'),
                    $detail->bahanBaku->satuan ?? 'unit',
                    number_format($detail->harga_satuan, 0, ',', '.'),
                    number_format($subtotal, 0, ',', '.')
                );
            }
            return '';
        })->filter()->toArray();
        
        return view('transaksi.pelunasan-utang.print', compact('pelunasanUtang'));
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            
            $pelunasan = PelunasanUtang::with('pembelian')->findOrFail($id);
            
            // Kembalikan terbayar dan sisa_pembayaran di pembelian
            $pembelian = $pelunasan->pembelian;
            $pembelian->terbayar -= $pelunasan->jumlah;
            $pembelian->sisa_pembayaran += $pelunasan->jumlah;
            
            if ($pembelian->sisa_pembayaran > 0 || $pembelian->terbayar < $pembelian->total_harga) {
                $pembelian->status = 'belum_lunas';
            }
            $pembelian->save();
            
            // Kembalikan saldo akun kas
            $akunKas = $pelunasan->akunKas;
            $akunKas->saldo += $pelunasan->jumlah;
            $akunKas->save();
            
            // Hapus jurnal terkait
            Jurnal::where('referensi', $pelunasan->kode_transaksi)->delete();
            
            // Hapus pelunasan
            $pelunasan->delete();
            
            DB::commit();
            
            return redirect()->route('transaksi.pelunasan-utang.index')
                ->with('success', 'Data pelunasan utang berhasil dihapus');
                
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error saat menghapus pelunasan utang: ' . $e->getMessage());
            return back()->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }
    
    public function getPembelian($id)
    {
        $pembelian = Pembelian::with('vendor')
            ->where('id', $id)
            ->firstOrFail();
        
        $sisaUtang = max(0, ($pembelian->total_harga ?? 0) - ($pembelian->terbayar ?? 0));
        
        if ($sisaUtang <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Pembelian ini sudah lunas'
            ], 400);
        }
            
        return response()->json([
            'success' => true,
            'data' => [
                'sisa_utang' => $sisaUtang,
                'vendor' => $pembelian->vendor->nama ?? '-',
                'kode_pembelian' => $pembelian->kode_pembelian ?? 'PB-' . $pembelian->id
            ]
        ]);
    }
}
