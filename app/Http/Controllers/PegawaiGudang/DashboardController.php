<?php

namespace App\Http\Controllers\PegawaiGudang;

use App\Http\Controllers\Controller;
use App\Models\BahanBaku;
use App\Models\BahanPendukung;
use App\Models\Vendor;
use App\Models\Pembelian;
use Illuminate\Http\Request;

class DashboardController extends Controller
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
    public function index()
    {
        // CRITICAL: Filter by user_id untuk multi-tenant isolation
        // Use session('user_id') for gudang staff authentication
        $userId = session('user_id') ?? session('gudang_id');

        // Get stok minimum notifications (with fallback if column doesn't exist)
        try {
            $stokMinimumBahanBaku = BahanBaku::where('user_id', $userId)
                ->whereRaw('stok <= COALESCE(stok_minimum, 10)')->get();
        } catch (\Exception $e) {
            // Fallback jika kolom stok_minimum belum ada
            $stokMinimumBahanBaku = BahanBaku::where('user_id', $userId)
                ->where('stok', '<=', 10)->get();
        }

        try {
            $stokMinimumBahanPendukung = BahanPendukung::where('user_id', $userId)
                ->whereRaw('stok <= COALESCE(stok_minimum, 10)')->get();
        } catch (\Exception $e) {
            // Fallback jika kolom stok_minimum belum ada
            $stokMinimumBahanPendukung = BahanPendukung::where('user_id', $userId)
                ->where('stok', '<=', 10)->get();
        }

        // Get recent pembelians
        $recentPembelians = Pembelian::where('user_id', $userId)
            ->with('vendor')
            ->orderBy('tanggal', 'desc')
            ->take(5)
            ->get();
        
        $data = [
            'title' => 'Dashboard Pegawai Gudang',
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
                // Use session('user_id') for gudang staff authentication
                'total_bahan_baku' => BahanBaku::where('user_id', session('user_id') ?? session('gudang_id'))->count(),
                'total_bahan_pendukung' => BahanPendukung::where('user_id', session('user_id') ?? session('gudang_id'))->count(),
                'total_vendor' => Vendor::where('user_id', session('user_id') ?? session('gudang_id'))->count(),
                'total_pembelian_bulan_ini' => Pembelian::where('user_id', session('user_id') ?? session('gudang_id'))
                    ->whereMonth('tanggal', date('m'))
                    ->whereYear('tanggal', date('Y'))
                    ->count(),
            ],
            'stok_minimum' => [
                'bahan_baku' => $stokMinimumBahanBaku,
                'bahan_pendukung' => $stokMinimumBahanPendukung
            ],
            'recent_purchases' => $recentPembelians
        ];

        return view('pegawai-gudang.dashboard', $data);
    }
}