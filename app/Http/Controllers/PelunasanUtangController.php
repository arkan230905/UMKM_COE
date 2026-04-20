<?php

namespace App\Http\Controllers;

use App\Models\Pembelian;
use App\Models\PelunasanUtang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PelunasanUtangController extends Controller
{
    /**
     * Display a listing of unpaid purchases.
     */
    public function index(Request $request)
    {
        $query = PelunasanUtang::with(['pembelian.vendor', 'pembelian.details.bahanBaku', 'pembelian.details.bahanPendukung', 'akunKas', 'coaPelunasan']);
        
        // Filter by kode transaksi
        if ($request->filled('kode_transaksi')) {
            $query->where('kode_transaksi', 'like', '%' . $request->kode_transaksi . '%');
        }
        
        // Filter by tanggal
        if ($request->filled('tanggal_mulai')) {
            $query->whereDate('tanggal', '>=', $request->tanggal_mulai);
        }
        if ($request->filled('tanggal_selesai')) {
            $query->whereDate('tanggal', '<=', $request->tanggal_selesai);
        }
        
        // Filter by vendor
        if ($request->filled('vendor_id')) {
            $query->whereHas('pembelian', function($q) use ($request) {
                $q->where('vendor_id', $request->vendor_id);
            });
        }
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        $pelunasanUtang = $query->orderBy('tanggal', 'desc')->paginate(15);
        
        // Get vendors for dropdown
        $vendors = \App\Models\Vendor::orderBy('nama_vendor')->get();

        return view('transaksi.pelunasan-utang.index', compact('pelunasanUtang', 'vendors'));
    }

    /**
     * Show the form for creating a new payment.
     */
    public function create()
    {
        // Get unpaid purchases - include both credit and partially paid purchases
        $pembayarans = Pembelian::where(function($q) {
                // Credit purchases that are not fully paid
                $q->where('payment_method', 'credit')
                  ->where('status', 'belum_lunas');
            })
            ->orWhere(function($q) {
                // Any purchase where terbayar < total_harga (partially paid)
                $q->whereRaw('terbayar < total_harga')
                  ->where('status', '!=', 'lunas');
            })
            ->orWhere(function($q) {
                // Any purchase with sisa_pembayaran > 0
                $q->where('sisa_pembayaran', '>', 0);
            })
            ->with('vendor')
            ->orderBy('tanggal', 'desc')
            ->get()
            ->filter(function($pembelian) {
                // Additional filter to ensure there's actually debt remaining
                $sisaUtang = ($pembelian->total_harga ?? 0) - ($pembelian->terbayar ?? 0);
                return $sisaUtang > 0;
            });
            
        // Get kas/bank accounts using helper for consistency
        $akunKas = \App\Helpers\AccountHelper::getKasBankAccounts();
        
        // Get COA hutang/kewajiban yang relevan untuk pelunasan
        // Only get accounts that actually exist in the database
        $coaPelunasan = \App\Models\Coa::where('tipe_akun', 'Liability')
            ->whereIn('kode_akun', ['2101', '211', '212']) // Hutang Usaha, Hutang Gaji, PPN Keluaran
            ->orderBy('kode_akun')
            ->get();
        
        // If no liability accounts found, show warning
        if ($coaPelunasan->isEmpty()) {
            \Log::warning('No liability COA accounts found for debt payment');
        }
        
        return view('transaksi.pelunasan-utang.create', compact('pembayarans', 'akunKas', 'coaPelunasan'));
    }

    /**
     * Store a newly created payment.
     */
    public function store(Request $request)
    {
        $request->validate([
            'pembelian_id' => 'required|exists:pembelians,id',
            'tanggal' => 'required|date',
            'jumlah' => 'required|numeric|min:1',
            'akun_kas_id' => 'required|exists:coas,id',
            'coa_pelunasan_id' => 'required|exists:coas,id',
            'keterangan' => 'nullable|string|max:255'
        ]);

        return DB::transaction(function () use ($request) {
            // Get the purchase
            $pembelian = Pembelian::findOrFail($request->pembelian_id);
            
            // Calculate remaining debt using the correct field names
            $sisaUtang = ($pembelian->total_harga ?? 0) - ($pembelian->terbayar ?? 0);
            
            if ($request->jumlah > $sisaUtang) {
                return back()->with('error', 'Jumlah pembayaran melebihi sisa utang.');
            }
            
            // Generate transaction code
            $kodeTransaksi = 'PU-' . date('Ymd') . '-' . str_pad(PelunasanUtang::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);
            
            // Create payment record
            $pelunasan = new PelunasanUtang([
                'kode_transaksi' => $kodeTransaksi,
                'pembelian_id' => $pembelian->id,
                'tanggal' => $request->tanggal,
                'akun_kas_id' => $request->akun_kas_id,
                'coa_pelunasan_id' => $request->coa_pelunasan_id,
                'jumlah' => $request->jumlah,
                'keterangan' => $request->keterangan,
                'status' => 'lunas',
                'user_id' => auth()->id()
            ]);
            
            $pelunasan->save();
            
            // Load relasi yang diperlukan untuk jurnal
            $pelunasan->load(['coaPelunasan', 'akunKas', 'pembelian.vendor']);
            
            // Create journal entry for kas/bank integration
            \App\Services\JournalService::createJournalFromPelunasanUtang($pelunasan);
            
            // Update purchase payment status
            $pembelian->terbayar += $request->jumlah;
            $pembelian->sisa_pembayaran = ($pembelian->total_harga ?? 0) - $pembelian->terbayar;
            
            // Check if fully paid
            if (abs($pembelian->terbayar - ($pembelian->total_harga ?? 0)) < 0.01) {
                $pembelian->status = 'lunas';
            } else {
                $pembelian->status = 'belum_lunas';
            }
            
            $pembelian->save();
            
            return redirect()->route('transaksi.pelunasan-utang.index')
                ->with('success', 'Pembayaran berhasil disimpan.');
        });
    }

    /**
     * Display payment history for a purchase.
     */
    public function show($id)
    {
        $pelunasanUtang = PelunasanUtang::with([
            'pembelian.vendor', 
            'pembelian.pembelianDetails.bahanBaku',
            'akunKas',
            'coaPelunasan'
        ])->findOrFail($id);
        
        return view('transaksi.pelunasan-utang.show', compact('pelunasanUtang'));
    }

    /**
     * Print payment receipt.
     */
    public function print($id)
    {
        $pelunasanUtang = PelunasanUtang::with([
            'pembelian.vendor', 
            'pembelian.pembelianDetails.bahanBaku',
            'akunKas',
            'coaPelunasan'
        ])->findOrFail($id);
        
        return view('transaksi.pelunasan-utang.print', compact('pelunasanUtang'));
    }

    /**
     * Remove the specified payment from storage.
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            
            $pelunasan = PelunasanUtang::with('pembelian')->findOrFail($id);
            
            // Delete journal entries first
            $journalService = new \App\Services\JournalService();
            $journalService->deleteByRef('debt_payment', $pelunasan->id);
            
            // Restore payment status in purchase
            $pembelian = $pelunasan->pembelian;
            $pembelian->terbayar -= $pelunasan->jumlah;
            
            // Update payment status
            if ($pembelian->terbayar < $pembelian->total_harga) {
                $pembelian->status = 'belum_lunas';
            }
            $pembelian->save();
            
            // Delete the payment record
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
    
    /**
     * Get purchase details for AJAX.
     */
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
                'total_pembelian' => $pembelian->total_harga ?? 0,
                'terbayar' => $pembelian->terbayar ?? 0,
                'vendor' => $pembelian->vendor->nama_vendor ?? '-',
                'nomor_pembelian' => $pembelian->nomor_pembelian ?? 'PB-' . $pembelian->id
            ]
        ]);
    }
}
