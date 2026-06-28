<?php

namespace App\Http\Controllers;

use App\Models\Pembelian;
use App\Models\PelunasanUtang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PelunasanUtangController extends Controller
{
    /**
     * Display a listing of unpaid purchases and payment history.
     * 
     * POLA SEPERTI PENJUALAN: Fetch semua data sekaligus, tidak ada conditional logic
     * Tab switching dilakukan di view dengan JavaScript
     * 
     * TAB DAFTAR UTANG:
     * - Menampilkan SEMUA pembelian yang sisa utang > 0
     * - Calculation: Total - Terbayar - Refund > 0
     * 
     * TAB RIWAYAT PELUNASAN:
     * - Menampilkan SEMUA histori pembayaran
     * - Setiap pelunasan = 1 record baru
     */
    public function index(Request $request)
    {
        $userId = auth()->id();
        
        // Get vendors for dropdown
        $vendors = \App\Models\Vendor::where('user_id', $userId)
            ->orderBy('nama_vendor')
            ->get();
        
        // ============================================================
        // FETCH DAFTAR UTANG - SELALU diambil tanpa kondisi tab
        // HANYA tampilkan pembelian KREDIT yang masih punya sisa utang
        // ============================================================
        $queryUtang = DB::table('pembelians')
            ->select(
                'pembelians.*',
                'vendors.nama_vendor',
                DB::raw('COALESCE((SELECT SUM(jumlah) FROM pelunasan_utangs 
                         WHERE pembelian_id = pembelians.id AND user_id = ' . $userId . '), 0) as total_dibayar'),
                DB::raw('COALESCE((SELECT SUM(total_return_amount) FROM purchase_returns 
                         WHERE pembelian_id = pembelians.id 
                         AND user_id = ' . $userId . '
                         AND jenis_retur = "refund" 
                         AND status IN ("disetujui", "dikirim", "selesai")), 0) as total_refund'),
                DB::raw('(pembelians.total_harga - COALESCE(pembelians.dp, 0) - 
                         COALESCE((SELECT SUM(jumlah) FROM pelunasan_utangs 
                                  WHERE pembelian_id = pembelians.id AND user_id = ' . $userId . '), 0) -
                         COALESCE((SELECT SUM(total_return_amount) FROM purchase_returns 
                                  WHERE pembelian_id = pembelians.id 
                                  AND user_id = ' . $userId . '
                                  AND jenis_retur = "refund" 
                                  AND status IN ("disetujui", "dikirim", "selesai")), 0)
                        ) as sisa_utang_real')
            )
            ->leftJoin('vendors', 'pembelians.vendor_id', '=', 'vendors.id')
            ->where('pembelians.user_id', $userId)
            ->where('pembelians.payment_method', 'credit') // HANYA pembelian kredit
            ->whereNull('pembelians.deleted_at');
        
        // Apply filters untuk daftar utang
        if ($request->filled('nomor_pembelian')) {
            $queryUtang->where('pembelians.nomor_pembelian', 'like', '%' . $request->nomor_pembelian . '%');
        }
        
        if ($request->filled('tanggal_mulai')) {
            $queryUtang->whereDate('pembelians.tanggal', '>=', $request->tanggal_mulai);
        }
        if ($request->filled('tanggal_selesai')) {
            $queryUtang->whereDate('pembelians.tanggal', '<=', $request->tanggal_selesai);
        }
        
        if ($request->filled('vendor_id')) {
            $queryUtang->where('pembelians.vendor_id', $request->vendor_id);
        }
        
        // Filter hanya yang masih punya sisa utang
        $queryUtang->havingRaw('sisa_utang_real > 0');
        
        // Order by tanggal desc
        $queryUtang->orderBy('pembelians.tanggal', 'desc');
        
        // Get all data - bukan paginate dulu
        $daftarUtang = $queryUtang->get();
        
        // ============================================================
        // FETCH RIWAYAT PELUNASAN - SELALU diambil tanpa kondisi tab
        // ============================================================
        $queryPelunasan = DB::table('pelunasan_utangs')
            ->select(
                'pelunasan_utangs.*',
                'pembelians.nomor_pembelian',
                'pembelians.total_harga as pembelian_total',
                'vendors.nama_vendor',
                'coa_pelunasan.kode_akun as coa_pelunasan_kode',
                'coa_pelunasan.nama_akun as coa_pelunasan_nama',
                'coa_kas.kode_akun as coa_kas_kode',
                'coa_kas.nama_akun as coa_kas_nama',
                DB::raw('(pembelians.total_harga - COALESCE(pembelians.dp, 0) - 
                         COALESCE((SELECT SUM(jumlah) FROM pelunasan_utangs AS pu
                                  WHERE pu.pembelian_id = pelunasan_utangs.pembelian_id 
                                  AND pu.user_id = ' . $userId . '), 0) -
                         COALESCE((SELECT SUM(total_return_amount) FROM purchase_returns 
                                  WHERE pembelian_id = pelunasan_utangs.pembelian_id 
                                  AND user_id = ' . $userId . '
                                  AND jenis_retur = "refund" 
                                  AND status IN ("disetujui", "dikirim", "selesai")), 0)
                        ) as sisa_utang_setelah'),
                DB::raw('(SELECT GROUP_CONCAT(
                            CASE 
                                WHEN pd.bahan_baku_id IS NOT NULL THEN bb.nama_bahan
                                WHEN pd.bahan_pendukung_id IS NOT NULL THEN bp.nama_bahan
                                ELSE "Item"
                            END
                            SEPARATOR ", "
                         )
                         FROM pembelian_details pd
                         LEFT JOIN bahan_bakus bb ON pd.bahan_baku_id = bb.id
                         LEFT JOIN bahan_pendukungs bp ON pd.bahan_pendukung_id = bp.id
                         WHERE pd.pembelian_id = pembelians.id
                         LIMIT 3
                        ) as items_list')
            )
            ->leftJoin('pembelians', 'pelunasan_utangs.pembelian_id', '=', 'pembelians.id')
            ->leftJoin('vendors', 'pembelians.vendor_id', '=', 'vendors.id')
            ->leftJoin('coas as coa_pelunasan', 'pelunasan_utangs.coa_pelunasan_id', '=', 'coa_pelunasan.id')
            ->leftJoin('coas as coa_kas', 'pelunasan_utangs.akun_kas_id', '=', 'coa_kas.id')
            ->where('pelunasan_utangs.user_id', $userId);
        
        // Filter by kode transaksi
        if ($request->filled('kode_transaksi')) {
            $queryPelunasan->where('pelunasan_utangs.kode_transaksi', 'like', '%' . $request->kode_transaksi . '%');
        }
        
        // Filter by tanggal untuk pelunasan
        if ($request->filled('tanggal_mulai_pelunasan')) {
            $queryPelunasan->whereDate('pelunasan_utangs.tanggal', '>=', $request->tanggal_mulai_pelunasan);
        }
        if ($request->filled('tanggal_selesai_pelunasan')) {
            $queryPelunasan->whereDate('pelunasan_utangs.tanggal', '<=', $request->tanggal_selesai_pelunasan);
        }
        
        // Filter by vendor untuk pelunasan
        if ($request->filled('vendor_id_pelunasan')) {
            $queryPelunasan->where('pembelians.vendor_id', $request->vendor_id_pelunasan);
        }
        
        $queryPelunasan->orderBy('pelunasan_utangs.tanggal', 'desc');
        
        // Get all data - bukan paginate dulu
        $pelunasanUtang = $queryPelunasan->get();

        return view('transaksi.pelunasan-utang.index', compact('pelunasanUtang', 'daftarUtang', 'vendors'));
    }

    /**
     * Show the form for creating a new payment.
     */
    public function create(Request $request)
    {
        // Validate pembelian_id from URL parameter
        if (!$request->has('pembelian_id')) {
            return redirect()->route('transaksi.pelunasan-utang.index')
                ->with('error', 'ID Pembelian tidak ditemukan. Silakan pilih pembelian dari daftar utang.');
        }
        
        // Get the specific pembelian
        $pembelian = Pembelian::where('user_id', auth()->id())
            ->where('id', $request->pembelian_id)
            ->with('vendor')
            ->first();
            
        if (!$pembelian) {
            return redirect()->route('transaksi.pelunasan-utang.index')
                ->with('error', 'Pembelian tidak ditemukan.');
        }
        
        // Check if there's remaining debt
        if ($pembelian->sisa_utang <= 0) {
            return redirect()->route('transaksi.pelunasan-utang.index')
                ->with('error', 'Pembelian ini sudah lunas.');
        }
            
        // Get kas/bank accounts using helper for consistency
        $akunKas = \App\Helpers\AccountHelper::getKasBankAccounts();
        
        return view('transaksi.pelunasan-utang.create', compact('pembelian', 'akunKas'));
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
            
            // Find COA Hutang Usaha (kode_akun 211)
            $coaPelunasan = \App\Models\Coa::where('user_id', auth()->id())
                ->where('kode_akun', '211') // Hutang Usaha
                ->first();
                
            if (!$coaPelunasan) {
                return back()->with('error', 'COA Hutang Usaha (211) tidak ditemukan. Silakan setup Chart of Account terlebih dahulu.');
            }
            
            // Generate transaction code
            $kodeTransaksi = 'PU-' . date('Ymd') . '-' . str_pad(PelunasanUtang::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);
            
            // Calculate new payment status considering refunds
            $newTerbayar = ($pembelian->terbayar ?? 0) + $request->jumlah;
            $totalRefund = $pembelian->total_refund; // Get refund from accessor
            
            // Fully paid if: terbayar + refund >= total_harga
            $isFullyPaid = ($newTerbayar + $totalRefund) >= ($pembelian->total_harga ?? 0);
            
            // Create payment record
            $pelunasan = new PelunasanUtang([
                'kode_transaksi' => $kodeTransaksi,
                'pembelian_id' => $pembelian->id,
                'tanggal' => $request->tanggal,
                'akun_kas_id' => $request->akun_kas_id,
                'coa_pelunasan_id' => $coaPelunasan->id, // Auto-set to Hutang Usaha
                'jumlah' => $request->jumlah,
                'keterangan' => $request->keterangan,
                'status' => $isFullyPaid ? 'lunas' : 'belum_lunas', // Status based on payment
                'user_id' => auth()->id()
            ]);
            
            $pelunasan->save();
            
            // Load relasi yang diperlukan untuk jurnal
            $pelunasan->load(['coaPelunasan', 'akunKas', 'pembelian.vendor']);
            
            // Create journal entry for kas/bank integration
            \App\Services\JournalService::createJournalFromPelunasanUtang($pelunasan);
            
            // Update purchase payment status
            $pembelian->terbayar = $newTerbayar;
            $pembelian->sisa_pembayaran = ($pembelian->total_harga ?? 0) - $newTerbayar - $totalRefund;
            
            // Check if fully paid (considering refunds)
            if ($isFullyPaid) {
                $pembelian->status = 'lunas';
                
                // UPDATE: Set status semua pelunasan untuk pembelian ini menjadi 'lunas'
                PelunasanUtang::where('pembelian_id', $pembelian->id)
                    ->where('user_id', auth()->id())
                    ->update(['status' => 'lunas']);
            } else {
                $pembelian->status = 'belum_lunas';
            }
            
            $pembelian->save();
            
            return redirect()->route('transaksi.pelunasan-utang.index', ['tab' => 'pelunasan'])
                ->with('success', 'Pembayaran berhasil disimpan.');
        });
    }

    /**
     * Display journal entries for a payment.
     */
    public function showJurnal($id)
    {
        $pelunasan = PelunasanUtang::with(['pembelian.vendor'])->findOrFail($id);
        
        // Query jurnal_umum (flat structure) untuk pelunasan ini
        $query = \DB::table('jurnal_umum as ju')
            ->leftJoin('coas', 'coas.id', '=', 'ju.coa_id')
            ->select([
                'ju.*',
                'coas.kode_akun',
                'coas.nama_akun',
                'coas.tipe_akun'
            ])
            ->where('ju.user_id', auth()->id())
            ->where('ju.tipe_referensi', 'debt_payment')
            ->where('ju.referensi', (string)$id)
            ->where(function($q) {
                $q->where('ju.debit', '!=', 0)
                  ->orWhere('ju.kredit', '!=', 0);
            })
            ->orderBy('ju.tanggal','asc')
            ->orderBy('ju.created_at','asc')
            ->orderByDesc('ju.debit')
            ->orderBy('ju.id','asc');
        
        $results = $query->get();
        
        // Group results by date and reference for display
        $entries = collect();
        $groupedResults = $results->groupBy(function($item) {
            return $item->tanggal . '_' . ($item->tipe_referensi ?? 'manual') . '_' . ($item->referensi ?? $item->id);
        });
        
        foreach ($groupedResults as $groupKey => $lines) {
            $firstLine = $lines->first();
            
            if ($lines->isEmpty()) continue;
            
            $entry = (object) [
                'id' => $firstLine->id,
                'tanggal' => $firstLine->tanggal,
                'created_at' => $firstLine->created_at,
                'ref_type' => $firstLine->tipe_referensi,
                'ref_id' => $firstLine->referensi,
                'memo' => $firstLine->keterangan,
                'lines' => $lines->map(function($line) {
                    return (object) [
                        'id' => $line->id,
                        'debit' => $line->debit,
                        'credit' => $line->kredit,
                        'memo' => $line->keterangan,
                        'account_code' => $line->kode_akun,
                        'account_name' => $line->nama_akun,
                        'account_type' => $line->tipe_akun,
                        'coa' => (object) [
                            'kode_akun' => $line->kode_akun,
                            'nama_akun' => $line->nama_akun,
                            'tipe_akun' => $line->tipe_akun
                        ]
                    ];
                })
            ];
            $entries->push($entry);
        }
        
        return view('transaksi.pelunasan-utang.jurnal', compact('pelunasan', 'entries'));
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
     * Returns sisa utang that already considers refunds
     */
    public function getPembelian($id)
    {
        $pembelian = Pembelian::with('vendor')
            ->where('id', $id)
            ->firstOrFail();
        
        // Use accessor that already considers refunds
        $sisaUtang = $pembelian->sisa_utang;
        
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
                'total_refund' => $pembelian->total_refund ?? 0,
                'dp_amount' => $pembelian->dp ?? 0, // DP amount
                'tanggal_jatuh_tempo' => $pembelian->tanggal_jatuh_tempo ? $pembelian->tanggal_jatuh_tempo->format('Y-m-d') : null, // Due date
                'vendor' => $pembelian->vendor->nama_vendor ?? '-',
                'nomor_pembelian' => $pembelian->nomor_pembelian ?? 'PB-' . $pembelian->id
            ]
        ]);
    }
}
