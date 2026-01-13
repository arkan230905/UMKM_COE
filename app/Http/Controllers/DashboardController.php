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
        // Get filter parameters
        $month = request()->get('month', now()->month);
        $year = request()->get('year', now()->year);
        
        // Create month names for display
        $monthNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        
        // Get available years (from transactions)
        $availableYears = range(2020, now()->year);
        $availableMonths = $monthNames;
        
        $selectedMonth = $monthNames[$month] ?? 'Bulan ' . $month;
        $selectedYear = $year;
        
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
            // Filter Data
            'month', 'year', 'selectedMonth', 'selectedYear', 'availableMonths', 'availableYears',
            
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
            if (!\Schema::hasTable('coas')) { return 0; }

            // Ambil COA Kas dan Bank menggunakan helper
            $coas = \App\Helpers\AccountHelper::getKasBankAccounts();
            if ($coas->isEmpty()) { return 0; }

            $total = 0;
            foreach ($coas as $coa) {
                $saldoAwal = (float)$coa->saldo_awal;
                
                // Cari account_id yang sesuai
                $account = \DB::table('accounts')->where('code', $coa->kode_akun)->first();
                if (!$account) {
                    $total += $saldoAwal;
                    continue;
                }
                
                // Hitung dari journal_lines
                if (\Schema::hasTable('journal_lines')) {
                    $debit = \DB::table('journal_lines')->where('account_id', $account->id)->sum('debit');
                    $kredit = \DB::table('journal_lines')->where('account_id', $account->id)->sum('credit');
                    $total += $saldoAwal + (float)$debit - (float)$kredit;
                } else {
                    $total += $saldoAwal;
                }
            }
            
            return max(0, $total);
        } catch (\Exception $e) {
            \Log::error('Error getTotalKasBank: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Calculate income for current month
     */
    private function getPendapatanBulanIni()
    {
        try {
            // Hitung dari penjualan tunai bulan ini
            $startOfMonth = now()->startOfMonth();
            $endOfMonth = now()->endOfMonth();

            $totalPenjualan = Penjualan::whereBetween('tanggal', [$startOfMonth, $endOfMonth])
                ->sum('total');
            
            return (float)$totalPenjualan;
        } catch (\Exception $e) {
            \Log::error('Error getPendapatanBulanIni: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Calculate total receivables
     */
    private function getTotalPiutang()
    {
        try {
            // Hitung dari penjualan kredit yang belum lunas
            $totalPiutang = Penjualan::where('payment_method', 'credit')
                ->where('status', '!=', 'lunas')
                ->sum('total');
            
            return (float)$totalPiutang;
        } catch (\Exception $e) {
            \Log::error('Error getTotalPiutang: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Calculate total payables
     */
    private function getTotalUtang()
    {
        try {
            // Hitung dari pembelian kredit yang belum lunas
            $totalUtang = Pembelian::where('payment_method', 'credit')
                ->where('status', '!=', 'lunas')
                ->sum('sisa_pembayaran');
            
            return (float)$totalUtang;
        } catch (\Exception $e) {
            \Log::error('Error getTotalUtang: ' . $e->getMessage());
            return 0;
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

    // === Helper: guess COA categories robustly across differing charts ===
    private function getKasBankCoaIds(): array
    {
        $query = Coa::query();
        $query->whereIn('kode_akun', \App\Helpers\AccountHelper::KAS_BANK_CODES);
        return $query->pluck('id')->all();
    }

    private function getRevenueCoaIds(): array
    {
        return Coa::where('tipe_akun', 'Revenue')
            ->orWhere('kode_akun', 'like', '4%')
            ->pluck('id')->all();
    }

    private function getReceivableCoaIds(): array
    {
        $q = Coa::query();
        $q->where('nama_akun', 'like', '%piutang%')
          ->orWhere('kode_akun', 'like', '12%')
          ->orWhere('kode_akun', 'like', '13%');
        return $q->pluck('id')->all();
    }

    private function getPayableCoaIds(): array
    {
        $q = Coa::query();
        $q->where('nama_akun', 'like', '%utang%')
          ->orWhere('kode_akun', 'like', '21%');
        return $q->pluck('id')->all();
    }
}
