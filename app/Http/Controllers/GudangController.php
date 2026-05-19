<?php

namespace App\Http\Controllers;

use App\Models\BahanBaku;
use App\Models\BahanPendukung;
use App\Models\Vendor;
use App\Models\Pembelian;
use App\Models\Satuan;
use App\Models\Coa;
use Illuminate\Http\Request;

class GudangController extends Controller
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
     * Dashboard untuk pegawai gudang
     */
    public function dashboard()
    {
        // CRITICAL: Filter by user_id untuk multi-tenant isolation
        // Use session('user_id') for gudang staff authentication
        $userId = session('user_id') ?? session('gudang_id');

        $data = [
            'title' => 'Dashboard Gudang',
            'pegawai' => [
                'id' => session('gudang_id'),
                'nama' => session('gudang_nama'),
                'kode' => session('gudang_kode'),
                'email' => session('gudang_email'),
                'jabatan' => session('gudang_jabatan'),
            ],
            'perusahaan' => [
                'nama' => session('perusahaan_nama'),
                'kode' => session('perusahaan_kode'),
            ],
            'stats' => [
                // CRITICAL: Filter by user_id untuk multi-tenant isolation
                'total_bahan_baku' => BahanBaku::where('user_id', $userId)->count(),
                'total_bahan_pendukung' => BahanPendukung::where('user_id', $userId)->count(),
                'total_vendor' => Vendor::where('user_id', $userId)->count(),
                'total_pembelian_bulan_ini' => Pembelian::where('user_id', $userId)
                    ->whereMonth('tanggal', date('m'))
                    ->whereYear('tanggal', date('Y'))
                    ->count(),
            ]
        ];

        return view('gudang.dashboard', $data);
    }

    /**
     * Halaman bahan baku
     */
    public function bahanBaku()
    {
        $bahanBakus = BahanBaku::with('satuanRelation')->orderBy('nama_bahan')->paginate(20);
        
        return view('gudang.bahan-baku', [
            'title' => 'Bahan Baku',
            'bahanBakus' => $bahanBakus
        ]);
    }

    /**
     * Halaman bahan pendukung
     */
    public function bahanPendukung()
    {
        $bahanPendukungs = BahanPendukung::with('kategori')->orderBy('nama_bahan')->paginate(20);
        
        return view('gudang.bahan-pendukung', [
            'title' => 'Bahan Pendukung',
            'bahanPendukungs' => $bahanPendukungs
        ]);
    }

    /**
     * Halaman vendor
     */
    public function vendor()
    {
        // CRITICAL: Filter by user_id untuk multi-tenant isolation
        // Use session('user_id') for gudang staff authentication
        $userId = session('user_id') ?? session('gudang_id');
        $vendors = Vendor::where('user_id', $userId)
            ->orderBy('nama_vendor')
            ->paginate(20);
        
        return view('gudang.vendor', [
            'title' => 'Vendor',
            'vendors' => $vendors
        ]);
    }

    /**
     * Halaman pembelian
     */
    public function pembelian()
    {
        $pembelians = Pembelian::with(['vendor', 'details'])
            ->orderBy('tanggal', 'desc')
            ->paginate(20);
        
        return view('gudang.pembelian', [
            'title' => 'Pembelian',
            'pembelians' => $pembelians
        ]);
    }

    /**
     * Form create pembelian
     */
    public function createPembelian()
    {
        // CRITICAL: Filter by user_id untuk multi-tenant isolation
        // Use session('user_id') for gudang staff authentication
        $userId = session('user_id') ?? session('gudang_id');
        $vendors = Vendor::where('user_id', $userId)
            ->orderBy('nama_vendor')
            ->get();
        $bahanBakus = BahanBaku::with('satuanRelation')->orderBy('nama_bahan')->get();
        $bahanPendukungs = BahanPendukung::orderBy('nama_bahan')->get();
        $satuans = Satuan::orderBy('kode')->get();
        $kasbank = Coa::whereIn('kode_akun', ['111', '112', '113'])->orderBy('nama_akun')->get();

        return view('gudang.pembelian-create', [
            'title' => 'Tambah Pembelian',
            'vendors' => $vendors,
            'bahanBakus' => $bahanBakus,
            'bahanPendukungs' => $bahanPendukungs,
            'satuans' => $satuans,
            'kasbank' => $kasbank
        ]);
    }

    /**
     * Store pembelian
     */
    public function storePembelian(Request $request)
    {
        // Redirect to main pembelian controller for processing
        return app(\App\Http\Controllers\PembelianController::class)->store($request);
    }

    /**
     * Show pembelian detail
     */
    public function showPembelian($id)
    {
        $pembelian = Pembelian::with(['vendor', 'details.bahanBaku', 'details.bahanPendukung'])
            ->findOrFail($id);
        
        return view('gudang.pembelian-show', [
            'title' => 'Detail Pembelian',
            'pembelian' => $pembelian
        ]);
    }
}