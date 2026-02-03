<?php

namespace App\Http\Controllers\PegawaiGudang;

use App\Http\Controllers\Controller;
use App\Models\BahanBaku;
use App\Models\BahanPendukung;
use App\Models\Vendor;
use App\Models\Pembelian;
use App\Services\StockService;
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
    public function index(StockService $stock)
    {
        // Get real-time stock using StockService
        $bahanBakus = BahanBaku::all();
        $bahanPendukungs = BahanPendukung::all();
        
        // Calculate real-time stock for each item
        $stokMinimumBahanBaku = collect();
        $stokMinimumBahanPendukung = collect();
        
        foreach ($bahanBakus as $bahan) {
            $availableStock = $stock->getAvailableStock('material', $bahan->id);
            $stokMinimum = $bahan->stok_minimum ?? 10;
            
            // Update stok master dengan data realtime
            $bahan->stok = $availableStock;
            $bahan->save();
            
            if ($availableStock <= $stokMinimum) {
                $stokMinimumBahanBaku->push($bahan);
            }
        }
        
        foreach ($bahanPendukungs as $bahan) {
            $availableStock = $stock->getAvailableStock('support', $bahan->id);
            $stokMinimum = $bahan->stok_minimum ?? 10;
            
            // Update stok master dengan data realtime
            $bahan->stok = $availableStock;
            $bahan->save();
            
            if ($availableStock <= $stokMinimum) {
                $stokMinimumBahanPendukung->push($bahan);
            }
        }
        
        // Get recent pembelians
        $recentPembelians = Pembelian::with('vendor')
            ->orderBy('tanggal', 'desc')
            ->take(5)
            ->get();
        
        // Calculate total stock values
        $totalStokBahanBaku = 0;
        $totalStokBahanPendukung = 0;
        
        foreach ($bahanBakus as $bahan) {
            $totalStokBahanBaku += $bahan->stok;
        }
        
        foreach ($bahanPendukungs as $bahan) {
            $totalStokBahanPendukung += $bahan->stok;
        }
        
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
                'total_bahan_baku' => BahanBaku::count(),
                'total_bahan_pendukung' => BahanPendukung::count(),
                'total_vendor' => Vendor::count(),
                'total_pembelian_bulan_ini' => Pembelian::whereMonth('tanggal', date('m'))
                    ->whereYear('tanggal', date('Y'))
                    ->count(),
                'total_stok_bahan_baku' => $totalStokBahanBaku,
                'total_stok_bahan_pendukung' => $totalStokBahanPendukung,
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