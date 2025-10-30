<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pegawai;
use App\Models\Presensi;
use App\Models\Produk;
use App\Models\Vendor;
use App\Models\BahanBaku;
use App\Models\Bop;
use App\Models\Bom;
use App\Models\Coa;
use App\Models\Pembelian;
use App\Models\Penjualan;
use App\Models\Retur;
use App\Models\Penggajian;
use App\Models\ExpensePayment;
use App\Models\ApSettlement;
use App\Models\JurnalUmum;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Master Data
        $totalPegawai     = Pegawai::count();
        $totalPresensi    = Presensi::count();
        $totalProduk      = Produk::count();
        $totalVendor      = Vendor::count();
        $totalBahanBaku   = BahanBaku::count();
        $totalSatuan      = \App\Models\Satuan::count();
        
        // Handle case when bops table doesn't exist yet
        $totalBOP = 0;
        try {
            if (\Schema::hasTable('bops')) {
                $totalBOP = Bop::count();
            }
        } catch (\Exception $e) {
            // Table doesn't exist or other error, default to 0
        }
        
        $totalBOM         = Bom::count();
        $totalCOA         = Coa::count();
        
        // Handle case when produksis table doesn't exist
        $totalProduksi = 0;
        try {
            if (\Schema::hasTable('produksis')) {
                $totalProduksi = \App\Models\Produksi::count();
            }
        } catch (\Exception $e) {
            // Table doesn't exist or other error, default to 0
        }

        // Transaksi
        $totalPembelian   = Pembelian::count();
        $totalPenjualan   = Penjualan::count();
        $totalRetur       = Retur::sum('jumlah');
        $totalPenggajian  = Penggajian::count();

        // Financial Data
        $totalKasBank = $this->getTotalKasBank();
        $pendapatanBulanIni = $this->getPendapatanBulanIni();
        $totalPiutang = $this->getTotalPiutang();
        $totalUtang = $this->getTotalUtang();

        // Recent Transactions
        $recentExpensePayments = collect();
        if (\Schema::hasTable('expense_payments')) {
            $recentExpensePayments = \App\Models\ExpensePayment::with('coaBeban')
                ->latest()
                ->take(5)
                ->get();
        }

        $recentApSettlements = collect();
        if (\Schema::hasTable('ap_settlements')) {
            $recentApSettlements = \App\Models\ApSettlement::with(['pembelian.vendor'])
                ->latest()
                ->take(5)
                ->get();
        }

        // Calculate trends (simplified - you might want to implement more sophisticated logic)
        $trendPenjualan = $this->calculateTrend('penjualan');
        $trendPembelian = $this->calculateTrend('pembelian');
        $trendProduksi = $this->calculateTrend('produksi');
        $trendRetur = $this->calculateTrend('retur');

        return view('dashboard', compact(
            // Master Data
            'totalPegawai',
            'totalPresensi',
            'totalProduk',
            'totalVendor',
            'totalBahanBaku',
            'totalSatuan',
            'totalBOP',
            'totalBOM',
            'totalCOA',
            'totalProduksi',
            
            // Transaction Counts
            'totalPembelian',
            'totalPenjualan',
            'totalRetur',
            'totalPenggajian',
            
            // Financial Data
            'totalKasBank',
            'pendapatanBulanIni',
            'totalPiutang',
            'totalUtang',
            
            // Recent Transactions
            'recentExpensePayments',
            'recentApSettlements',
            
            // Trends
            'trendPenjualan',
            'trendPembelian',
            'trendProduksi',
            'trendRetur'
        ));
    }

    /**
     * Calculate total cash and bank balances
     */
    private function getTotalKasBank()
    {
        try {
            // Cek apakah tabel jurnal_umum ada
            if (!\Schema::hasTable('jurnal_umum')) {
                return 0;
            }
            
            // Sum balances from cash and bank accounts
            $kas = \App\Models\JurnalUmum::whereHas('coa', function($q) {
                    $q->whereIn('kode_akun', ['1101', '1102']); // Kas & Bank accounts
                })
                ->sum('debit') - \App\Models\JurnalUmum::whereHas('coa', function($q) {
                    $q->whereIn('kode_akun', ['1101', '1102']);
                })
                ->sum('kredit');

            return max(0, $kas); // Pastikan tidak negatif
        } catch (\Exception $e) {
            return 0; // Kembalikan 0 jika ada error
        }
    }

    /**
     * Calculate income for current month
     */
    private function getPendapatanBulanIni()
    {
        try {
            // Cek apakah tabel yang dibutuhkan ada
            if (!\Schema::hasTable('jurnal_umum') || !\Schema::hasTable('coas')) {
                return 0;
            }
            
            $startOfMonth = now()->startOfMonth();
            $endOfMonth = now()->endOfMonth();

            $pendapatan = \App\Models\JurnalUmum::whereHas('coa', function($q) {
                    $q->whereIn('kode_akun', ['4001', '4002']); // Pendapatan accounts
                })
                ->whereBetween('tanggal', [$startOfMonth, $endOfMonth])
                ->sum('kredit');

            return $pendapatan;
        } catch (\Exception $e) {
            return 0; // Kembalikan 0 jika ada error
        }
    }

    /**
     * Calculate total receivables
     */
    private function getTotalPiutang()
    {
        try {
            // Cek apakah tabel yang dibutuhkan ada
            if (!\Schema::hasTable('jurnal_umum') || !\Schema::hasTable('coas')) {
                return 0;
            }
            
            return \App\Models\JurnalUmum::whereHas('coa', function($q) {
                    $q->where('kode_akun', 'like', '13%'); // Akun piutang
                })
                ->sum('debit') - \App\Models\JurnalUmum::whereHas('coa', function($q) {
                    $q->where('kode_akun', 'like', '13%');
                })
                ->sum('kredit');
        } catch (\Exception $e) {
            return 0; // Kembalikan 0 jika ada error
        }
    }

    /**
     * Calculate total payables
     */
    private function getTotalUtang()
    {
        try {
            // Cek apakah tabel yang dibutuhkan ada
            if (!\Schema::hasTable('jurnal_umum') || !\Schema::hasTable('coas')) {
                return 0;
            }
            
            return \App\Models\JurnalUmum::whereHas('coa', function($q) {
                    $q->where('kode_akun', 'like', '21%'); // Akun utang
                })
                ->sum('kredit') - \App\Models\JurnalUmum::whereHas('coa', function($q) {
                    $q->where('kode_akun', 'like', '21%');
                })
                ->sum('debit');
        } catch (\Exception $e) {
            return 0; // Kembalikan 0 jika ada error
        }
    }

    /**
     * Calculate trend percentage (simplified)
     */
    private function calculateTrend($type)
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;
        $lastMonth = $currentMonth - 1;
        $lastMonthYear = $currentYear;
        
        if ($lastMonth === 0) {
            $lastMonth = 12;
            $lastMonthYear = $currentYear - 1;
        }

        $currentCount = 0;
        $lastCount = 0;

        switch ($type) {
            case 'penjualan':
                $currentCount = Penjualan::whereMonth('tanggal', $currentMonth)
                    ->whereYear('tanggal', $currentYear)
                    ->count();
                $lastCount = Penjualan::whereMonth('tanggal', $lastMonth)
                    ->whereYear('tanggal', $lastMonthYear)
                    ->count();
                break;
                
            case 'pembelian':
                $currentCount = Pembelian::whereMonth('tanggal', $currentMonth)
                    ->whereYear('tanggal', $currentYear)
                    ->count();
                $lastCount = Pembelian::whereMonth('tanggal', $lastMonth)
                    ->whereYear('tanggal', $lastMonthYear)
                    ->count();
                break;
                
            case 'produksi':
                if (\Schema::hasTable('produksis')) {
                    $currentCount = \App\Models\Produksi::whereMonth('tanggal', $currentMonth)
                        ->whereYear('tanggal', $currentYear)
                        ->count();
                    $lastCount = \App\Models\Produksi::whereMonth('tanggal', $lastMonth)
                        ->whereYear('tanggal', $lastMonthYear)
                        ->count();
                } else {
                    $currentCount = 0;
                    $lastCount = 0;
                }
                break;
                
            case 'retur':
                $currentCount = Retur::whereMonth('tanggal', $currentMonth)
                    ->whereYear('tanggal', $currentYear)
                    ->count();
                $lastCount = Retur::whereMonth('tanggal', $lastMonth)
                    ->whereYear('tanggal', $lastMonthYear)
                    ->count();
                break;
        }

        // Avoid division by zero
        if ($lastCount == 0) {
            return $currentCount > 0 ? 100 : 0;
        }

        return round((($currentCount - $lastCount) / $lastCount) * 100, 1);
    }
}
