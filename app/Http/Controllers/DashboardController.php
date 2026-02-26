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
        // ✅ TAMBAHAN: Data Penjualan untuk Chart (12 bulan terakhir)
        $salesChartData = $this->getSalesChartData();
        
        // ✅ TAMBAHAN: Detail Kas & Bank untuk dashboard
        $kasBankDetails = $this->getKasBankDetails();

        return view('dashboard', compact(
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
            'totalPembelian',
            'totalPenjualan',
            'totalRetur',
            'totalPenggajian',
            'totalKasBank',
            'pendapatanBulanIni',
            'totalPiutang',
            'totalUtang',
            'recentExpensePayments',
            'recentApSettlements',
            'trendPenjualan',
            'trendPembelian',
            'trendProduksi',
            'trendRetur',
            'salesChartData',
            'kasBankDetails'
        ));
    }

    /**
     * Calculate total cash and bank balances - menggunakan logic yang sama dengan LaporanKasBankController
     */
    private function getTotalKasBank()
    {
        try {
            if (!\Schema::hasTable('coas')) { return 0; }

            // Ambil COA Kas dan Bank menggunakan helper - sama dengan LaporanKasBankController
            $akunKasBank = \App\Helpers\AccountHelper::getKasBankAccounts();
            \Log::info('Dashboard: Total akun Kas & Bank: ' . $akunKasBank->count());
            
            if ($akunKasBank->isEmpty()) { 
                \Log::info('Dashboard: akunKasBank kosong, return 0');
                return 0; 
            }

            $total = 0;
            $startDate = now()->startOfMonth()->format('Y-m-d');
            $endDate = now()->endOfMonth()->format('Y-m-d');
            
            foreach ($akunKasBank as $akun) {
                // Gunakan logic yang sama dengan LaporanKasBankController
                $saldoAwal = $this->getSaldoAwal($akun, $startDate);
                $transaksiMasuk = $this->getTransaksiMasuk($akun, $startDate, $endDate);
                $transaksiKeluar = $this->getTransaksiKeluar($akun, $startDate, $endDate);
                
                // Untuk akun Kas & Bank (Aset), saldo normal adalah Debit
                // Saldo Akhir = Saldo Awal + Debit (Masuk) - Kredit (Keluar)
                $saldoAkhir = $saldoAwal + $transaksiMasuk - $transaksiKeluar;
                $total += $saldoAkhir;
            }
            
            return $total;
        } catch (\Exception $e) {
            \Log::error('Error getTotalKasBank: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get saldo awal sebelum periode - sama dengan logic LaporanKasBankController
     * Saldo Awal = Saldo dari CoaPeriodBalance atau saldo awal COA
     */
    private function getSaldoAwal($akun, $startDate)
    {
        // 1. Cari periode yang sesuai dengan start date
        $periode = \App\Models\CoaPeriod::where('periode', date('Y-m', strtotime($startDate)))->first();
        
        if ($periode) {
            // 2. Cek apakah ada saldo di CoaPeriodBalance untuk periode tersebut
            $periodBalance = \DB::table('coa_period_balances')
                ->where('kode_akun', $akun->kode_akun)
                ->where('period_id', $periode->id)
                ->first();
                
            if ($periodBalance) {
                return is_numeric($periodBalance->saldo_awal) ? (float) $periodBalance->saldo_awal : 0;
            }
        }
        
        // 3. Jika tidak ada periode atau tidak ada balance, cek periode sebelumnya
        $previousPeriod = isset($periode) ? $periode->getPreviousPeriod() : null;
        if ($previousPeriod) {
            $previousBalance = \DB::table('coa_period_balances')
                ->where('kode_akun', $akun->kode_akun)
                ->where('period_id', $previousPeriod->id)
                ->first();
                
            if ($previousBalance) {
                return is_numeric($previousBalance->saldo_akhir) ? (float) $previousBalance->saldo_akhir : 0;
            }
        }
        
        // 4. Jika tidak ada sama sekali, gunakan saldo awal dari COA
        return (float)$akun->saldo_awal;
    }

    /**
     * Get total transaksi masuk dalam periode (Debit) - sama dengan logic LaporanKasBankController
     */
    private function getTransaksiMasuk($akun, $startDate, $endDate)
    {
        $totalMasuk = 0;
        
        // 1. Penjualan (cash/transfer masuk ke kas/bank) - sama dengan LaporanKasBankController
        $penjualanMasuk = \DB::table('penjualans')
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->where(function($query) use ($akun) {
                // Jika akun adalah Kas (mengandung kata 'kas')
                if (stripos($akun->nama_akun, 'kas') !== false) {
                    $query->where('payment_method', 'cash');
                }
                // Jika akun adalah Bank (mengandung kata 'bank')
                elseif (stripos($akun->nama_akun, 'bank') !== false) {
                    $query->where('payment_method', 'transfer');
                }
            })
            ->sum('total');
            
        $totalMasuk += (float) ($penjualanMasuk ?? 0);
        
        // 2. Pelunasan Utang (pembayaran utang masuk ke kas/bank) - sama dengan LaporanKasBankController
        try {
            $pelunasanUtangMasuk = \DB::table('pelunasan_utangs')
                ->whereBetween('tanggal', [$startDate, $endDate])
                ->where(function($query) use ($akun) {
                    // Jika akun adalah Kas (mengandung kata 'kas')
                    if (stripos($akun->nama_akun, 'kas') !== false) {
                        $query->where('payment_method', 'cash');
                    }
                    // Jika akun adalah Bank (mengandung kata 'bank')
                    elseif (stripos($akun->nama_akun, 'bank') !== false) {
                        $query->where('payment_method', 'transfer');
                    }
                })
                ->sum('dibayar_bersih');
                
            $totalMasuk += (float) ($pelunasanUtangMasuk ?? 0);
        } catch (\Exception $e) {
            // Table might not exist
        }
        
        // 3. Retur Pembelian (uang kembali masuk ke kas/bank) - sama dengan LaporanKasBankController
        try {
            $returPembelianMasuk = \DB::table('purchase_returns')
                ->whereBetween('tanggal', [$startDate, $endDate])
                ->where(function($query) use ($akun) {
                    $query->where(function($subQuery) use ($akun) {
                        // Jika akun adalah Kas (mengandung kata 'kas')
                        if (stripos($akun->nama_akun, 'kas') !== false) {
                            $subQuery->where('payment_method', 'cash');
                        }
                        // Jika akun adalah Bank (mengandung kata 'bank')
                        elseif (stripos($akun->nama_akun, 'bank') !== false) {
                            $subQuery->where('payment_method', 'transfer');
                        }
                    });
                })
                ->sum('total_refund');
                
            $totalMasuk += (float) ($returPembelianMasuk ?? 0);
        } catch (\Exception $e) {
            // Table might not exist
        }
        
        return $totalMasuk;
    }

    /**
     * Get total transaksi keluar dalam periode (Kredit) - sama dengan logic LaporanKasBankController
     */
    private function getTransaksiKeluar($akun, $startDate, $endDate)
    {
        $totalKeluar = 0;
        
        // 1. Pembelian (cash/transfer keluar dari kas/bank) - sama dengan LaporanKasBankController
        $pembelianKeluar = \DB::table('pembelians')
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->where(function($query) use ($akun) {
                // Jika akun adalah Kas (mengandung kata 'kas')
                if (stripos($akun->nama_akun, 'kas') !== false) {
                    $query->where('payment_method', 'cash');
                }
                // Jika akun adalah Bank (mengandung kata 'bank')
                elseif (stripos($akun->nama_akun, 'bank') !== false) {
                    $query->where('payment_method', 'transfer');
                }
            })
            ->sum('total');
            
        $totalKeluar += (float) ($pembelianKeluar ?? 0);
        
        // 2. Penggajian (pengeluaran kas/bank) - sama dengan LaporanKasBankController
        try {
            $penggajianKeluar = \DB::table('penggajians')
                ->whereBetween('tanggal', [$startDate, $endDate])
                ->where(function($query) use ($akun) {
                    // Jika akun adalah Kas (mengandung kata 'kas')
                    if (stripos($akun->nama_akun, 'kas') !== false) {
                        $query->where('payment_method', 'cash');
                    }
                    // Jika akun adalah Bank (mengandung kata 'bank')
                    elseif (stripos($akun->nama_akun, 'bank') !== false) {
                        $query->where('payment_method', 'transfer');
                    }
                })
                ->sum('total_gaji');
                
            $totalKeluar += (float) ($penggajianKeluar ?? 0);
        } catch (\Exception $e) {
            // Table might not exist
        }
        
        // 3. Retur Penjualan (uang kembali ke kas/bank) - sama dengan LaporanKasBankController
        try {
            $returPenjualanKeluar = \DB::table('returns')
                ->whereBetween('tanggal', [$startDate, $endDate])
                ->where(function($query) use ($akun) {
                    $query->where(function($subQuery) use ($akun) {
                        // Jika akun adalah Kas (mengandung kata 'kas')
                        if (stripos($akun->nama_akun, 'kas') !== false) {
                            $subQuery->where('payment_method', 'cash');
                        }
                        // Jika akun adalah Bank (mengandung kata 'bank')
                        elseif (stripos($akun->nama_akun, 'bank') !== false) {
                            $subQuery->where('payment_method', 'transfer');
                        }
                    });
                })
                ->sum('total_refund');
                
            $totalKeluar += (float) ($returPenjualanKeluar ?? 0);
        } catch (\Exception $e) {
            // Table might not exist
        }
        
        return $totalKeluar;
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
                ->whereIn('status', ['pending', 'partial'])
                ->sum('sisa_pembayaran');
            
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

    /**
     * ✅ TAMBAHAN: Get Kas & Bank details per account - menggunakan logic yang sama dengan LaporanKasBankController
     */
    private function getKasBankDetails()
    {
        try {
            if (!\Schema::hasTable('coas')) { return collect(); }

            // Ambil COA Kas dan Bank menggunakan helper - sama dengan LaporanKasBankController
            $akunKasBank = \App\Helpers\AccountHelper::getKasBankAccounts();
            if ($akunKasBank->isEmpty()) { return collect(); }

            $details = [];
            $startDate = now()->startOfMonth()->format('Y-m-d');
            $endDate = now()->endOfMonth()->format('Y-m-d');
            
            foreach ($akunKasBank as $akun) {
                // Gunakan logic yang sama dengan LaporanKasBankController
                $saldoAwal = $this->getSaldoAwal($akun, $startDate);
                $transaksiMasuk = $this->getTransaksiMasuk($akun, $startDate, $endDate);
                $transaksiKeluar = $this->getTransaksiKeluar($akun, $startDate, $endDate);
                
                // Untuk akun Kas & Bank (Aset), saldo normal adalah Debit
                // Saldo Akhir = Saldo Awal + Debit (Masuk) - Kredit (Keluar)
                $saldoAkhir = $saldoAwal + $transaksiMasuk - $transaksiKeluar;
                
                $details[] = [
                    'kode_akun' => $akun->kode_akun,
                    'nama_akun' => $akun->nama_akun,
                    'saldo_awal' => $saldoAwal,
                    'transaksi_masuk' => $transaksiMasuk,
                    'transaksi_keluar' => $transaksiKeluar,
                    'saldo_akhir' => $saldoAkhir  // ✅ Perbaikan: gunakan key yang sama dengan LaporanKasBankController
                ];
            }
            
            return collect($details);
        } catch (\Exception $e) {
            \Log::error('Error getKasBankDetails: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * ✅ TAMBAHAN: Get sales data for chart (last 12 months)
     */
    private function getSalesChartData()
    {
        try {
            $data = [];
            $labels = [];
            
            // Get last 12 months
            for ($i = 11; $i >= 0; $i--) {
                $date = now()->subMonths($i);
                $month = $date->month;
                $year = $date->year;
                
                // Get total sales for this month
                $total = Penjualan::whereMonth('tanggal', $month)
                    ->whereYear('tanggal', $year)
                    ->sum('total');
                
                $labels[] = $date->format('M Y');
                $data[] = (float)$total;
            }
            
            return [
                'labels' => $labels,
                'data' => $data
            ];
        } catch (\Exception $e) {
            \Log::error('Error getSalesChartData: ' . $e->getMessage());
            return [
                'labels' => [],
                'data' => []
            ];
        }
    }
}
