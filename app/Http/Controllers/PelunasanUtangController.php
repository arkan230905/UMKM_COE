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
        $query = PelunasanUtang::with(['pembelian.vendor']);
        
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
     * Store a newly created payment.
     */
    public function store(Request $request)
    {
        $request->validate([
            'pembelian_id' => 'required|exists:pembelians,id',
            'tanggal' => 'required|date',
            'jumlah' => 'required|numeric|min:1',
            'keterangan' => 'nullable|string|max:255'
        ]);

        return DB::transaction(function () use ($request) {
            // Get the purchase
            $pembelian = Pembelian::findOrFail($request->pembelian_id);
            
            // Calculate remaining debt
            $sisaUtang = $pembelian->total - $pembelian->terbayar;
            
            if ($request->jumlah > $sisaUtang) {
                return back()->with('error', 'Jumlah pembayaran melebihi sisa utang.');
            }
            
            // Create payment record
            $pelunasan = new PelunasanUtang([
                'pembelian_id' => $pembelian->id,
                'tanggal' => $request->tanggal,
                'jumlah' => $request->jumlah,
                'keterangan' => $request->keterangan
            ]);
            
            $pelunasan->save();
            
            // Update purchase payment status
            $pembelian->terbayar += $request->jumlah;
            
            // Check if fully paid
            if (abs($pembelian->terbayar - $pembelian->total) < 0.01) {
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
        $pembelian = Pembelian::with(['vendor', 'pelunasan'])
            ->findOrFail($id);
            
        return view('transaksi.pelunasan-utang.show', compact('pembelian'));
    }
}
