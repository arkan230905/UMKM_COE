<?php

namespace App\Http\Controllers\PegawaiGudang;

use App\Http\Controllers\Controller;
use App\Models\Pembelian;
use App\Models\Vendor;
use App\Models\BahanBaku;
use App\Models\BahanPendukung;
use App\Models\Satuan;
use App\Models\Coa;
use Illuminate\Http\Request;

class PembelianController extends Controller
{
    public function __construct()
    {
        // Check if user is logged in as gudang staff
        $this->middleware(function ($request, $next) {
            if (!session('gudang_id')) {
                return redirect()->route('gudang.login')->withErrors(['error' => 'Silakan login terlebih dahulu']);
            }
            return $next($request);
        });
    }

    /**
     * Display a listing of pembelian
     */
    public function index(Request $request)
    {
        $query = Pembelian::with([
            'vendor', 
            'details.bahanBaku.satuanRelation', 
            'details.bahanPendukung.satuanRelation'
        ]);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nomor_pembelian', 'like', "%{$search}%")
                  ->orWhereHas('vendor', function($vendorQuery) use ($search) {
                      $vendorQuery->where('nama_vendor', 'like', "%{$search}%");
                  })
                  ->orWhereHas('details.bahanBaku', function($bahanQuery) use ($search) {
                      $bahanQuery->where('nama_bahan', 'like', "%{$search}%");
                  })
                  ->orWhereHas('details.bahanPendukung', function($bahanQuery) use ($search) {
                      $bahanQuery->where('nama_bahan', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by payment method
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        $pembelians = $query->orderBy('tanggal', 'desc')
                           ->paginate(15);
        
        return view('pegawai-gudang.pembelian.index', [
            'title' => 'Daftar Pembelian',
            'pembelians' => $pembelians
        ]);
    }

    /**
     * Show the form for creating a new pembelian
     */
    public function create()
    {
        $vendors = Vendor::orderBy('nama_vendor')->get();
        $bahanBakus = BahanBaku::with('satuanRelation')->orderBy('nama_bahan')->get();
        $bahanPendukungs = BahanPendukung::orderBy('nama_bahan')->get();
        $satuans = Satuan::orderBy('kode')->get();
        $kasbank = Coa::whereIn('kode_akun', ['101', '102'])->orderBy('nama_akun')->get();

        return view('pegawai-gudang.pembelian.create', [
            'title' => 'Tambah Pembelian',
            'vendors' => $vendors,
            'bahanBakus' => $bahanBakus,
            'bahanPendukungs' => $bahanPendukungs,
            'satuans' => $satuans,
            'kasbank' => $kasbank
        ]);
    }

    /**
     * Store a newly created pembelian
     */
    public function store(Request $request)
    {
        // Redirect to main pembelian controller for processing
        return app(\App\Http\Controllers\PembelianController::class)->store($request);
    }

    /**
     * Display the specified pembelian
     */
    public function show($id)
    {
        $pembelian = Pembelian::with(['vendor', 'details.bahanBaku', 'details.bahanPendukung'])
            ->findOrFail($id);
        
        return view('pegawai-gudang.pembelian.show', [
            'title' => 'Detail Pembelian',
            'pembelian' => $pembelian
        ]);
    }
}