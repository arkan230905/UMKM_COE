<?php

namespace App\Http\Controllers\PegawaiPembelian;

use App\Http\Controllers\Controller;
use App\Models\BahanBaku;
use App\Models\Vendor;
use App\Models\Pembelian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (auth()->check() && auth()->user()->role !== 'pegawai_pembelian') {
                abort(403, 'Unauthorized. Halaman ini hanya untuk pegawai pembelian.');
            }
            return $next($request);
        });
    }

    public function index()
    {
        // Statistik
        $totalBahanBaku = BahanBaku::count();
        $totalVendor = Vendor::count();
        $totalPembelianBulanIni = Pembelian::whereMonth('tanggal', date('m'))
            ->whereYear('tanggal', date('Y'))
            ->count();
        $totalNilaiPembelianBulanIni = Pembelian::whereMonth('tanggal', date('m'))
            ->whereYear('tanggal', date('Y'))
            ->sum('total_harga');

        // Pembelian Terbaru
        $pembelianTerbaru = Pembelian::with('vendor')
            ->latest('tanggal')
            ->take(5)
            ->get();

        // Bahan Baku Stok Rendah (< 10)
        $bahanBakuStokRendah = BahanBaku::where('stok', '<', 10)
            ->orderBy('stok', 'asc')
            ->take(5)
            ->get();

        // Vendor Aktif
        $vendorAktif = Vendor::withCount(['pembelians' => function($query) {
                $query->whereMonth('tanggal', date('m'))
                      ->whereYear('tanggal', date('Y'));
            }])
            ->having('pembelians_count', '>', 0)
            ->orderBy('pembelians_count', 'desc')
            ->take(5)
            ->get();

        return view('pegawai-pembelian.dashboard', compact(
            'totalBahanBaku',
            'totalVendor',
            'totalPembelianBulanIni',
            'totalNilaiPembelianBulanIni',
            'pembelianTerbaru',
            'bahanBakuStokRendah',
            'vendorAktif'
        ));
    }
}
