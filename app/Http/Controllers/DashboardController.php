<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pegawai;
use App\Models\Presensi;
use App\Models\Produk;
use App\Models\Vendor;
use App\Models\BahanBaku;
use App\Models\BahanPendukung;
use App\Models\Pelanggan;
use App\Models\Aset;
use App\Models\Jabatan;
use App\Models\Btkl;
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
        $user = auth()->user();
        
        // Handle unauthenticated users
        if (!$user) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }
        
        $userRole = $user->role;
        

        // Master Data - Filter berdasarkan user untuk multi-tenant dengan safety check
        $totalPegawai     = $this->getCountByUser('pegawais', $user->id);
        // CRITICAL: Filter by user_id untuk multi-tenant isolation
        $totalPresensi    = $this->getCountByUser('presensis', $user->id);
        $totalProduk      = $this->getCountByUser('produks', $user->id);
        $totalVendor      = $this->getCountByUser('vendors', $user->id);
        $totalBahanBaku   = $this->getCountByUser('bahan_bakus', $user->id);
        
        // Bahan Pendukung - check if table exists
        $totalBahanPendukung = 0;
        try {
            if (\Schema::hasTable('bahan_pendukungs')) {
                $totalBahanPendukung = $this->getCountByUser('bahan_pendukungs', $user->id);
            }
        } catch (\Exception $e) {
            // Table doesn't exist or other error, default to 0
        }
        
        $totalSatuan      = $this->getCountByUser('satuans', $user->id);
        $totalAset        = $this->getCountByUser('asets', $user->id);
        $totalPelanggan   = $this->getCountByUser('pelanggans', $user->id);
        
        // Jabatan - check if table exists
        $totalJabatan = 0;
        try {
            if (\Schema::hasTable('jabatans')) {
                $totalJabatan = $this->getCountByUser('jabatans', $user->id);
            }
        } catch (\Exception $e) {
            // Table doesn't exist or other error, default to 0
        }
        
        // BOP - check if table exists
        $totalBOP = 0;
        try {
            if (\Schema::hasTable('bops')) {
                $totalBOP = $this->getCountByUser('bops', $user->id);
            }
        } catch (\Exception $e) {
            // Table doesn't exist or other error, default to 0
        }
        
        // BTKL - check if table exists
        $totalBTKL = 0;
        try {
            if (\Schema::hasTable('proses_produksis')) {
                $totalBTKL = $this->getCountByUser('proses_produksis', $user->id);
            }
        } catch (\Exception $e) {
            // Table doesn't exist or other error, default to 0
        }
        
        $totalBOM         = $this->getCountByUser('boms', $user->id);
        $totalCOA         = $this->getCountByUser('coas', $user->id);
        
        // Handle case when produksis table doesn't exist
        $totalProduksi = 0;
        try {
            if (\Schema::hasTable('produksis')) {
                $totalProduksi = $this->getCountByUser('produksis', $user->id);
            }
        } catch (\Exception $e) {
            // Table doesn't exist or other error, default to 0
        }

        // Transaksi
        $totalPembelian   = $this->getCountByUser('pembelians', $user->id);
        $totalPenjualan   = $this->getCountByUser('penjualans', $user->id);
        $totalRetur       = $this->getCountByUser('returs', $user->id, 'jumlah');
        $totalPenggajian  = $this->getCountByUser('penggajians', $user->id);

        // Financial Data
        $totalKasBank = $this->getTotalKasBank();
        $pendapatanBulanIni = $this->getPendapatanBulanIni();
        $totalPiutang = $this->getTotalPiutang();
        $totalUtang = $this->getTotalUtang();

        // Recent Transactions
        $recentExpensePayments = collect();
        // Get pembayaran beban from PembayaranBeban (same as transaksi/pembayaran-beban page)
        if (\Schema::hasTable('pembayaran_beban')) {
            $recentExpensePayments = \App\Models\PembayaranBeban::with(['coaBeban', 'coaKas', 'bebanOperasional'])
                ->latest('tanggal')
                ->take(5)
                ->get();
        }

        $recentApSettlements = collect();
        // Get pelunasan utang from pelunasan_utangs table
        if (\Schema::hasTable('pelunasan_utangs')) {
            $recentApSettlements = \DB::table('pelunasan_utangs')
                ->join('pembelians', 'pelunasan_utangs.pembelian_id', '=', 'pembelians.id')
                ->join('vendors', 'pembelians.vendor_id', '=', 'vendors.id')
                ->select('pelunasan_utangs.*', 'pembelians.nomor_faktur', 'vendors.nama_vendor')
                ->latest('pelunasan_utangs.tanggal')
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

        // Return different dashboard based on user role
        if ($userRole === 'pegawai') {
            // Prepare stats for pegawai dashboard
            // CRITICAL: Filter by user_id untuk multi-tenant isolation
            $stats = [
                'total_hadir' => Presensi::where('user_id', $user->id)->where('status', 'hadir')->count(),
                'persentasi_kehadiran' => 95, // Default value, can be calculated later
                'total_hari_kerja' => 22, // Default working days in month
                'today_status' => [
                    'sudah_lengkap' => false, // Default value, can be calculated based on today's presensi
                    'jam_masuk' => '08:00', // Default value, can be calculated from today's presensi
                    'jam_keluar' => null // Default value, can be calculated from today's presensi
                ]
            ];
            
            // Prepare recent attendance for pegawai dashboard
            // CRITICAL: Filter by user_id untuk multi-tenant isolation
            $recentAttendance = Presensi::where('user_id', $user->id)
                ->orderBy('tgl_presensi', 'desc')
                ->limit(7)
                ->get();
            
            // Pegawai Dashboard - Limited access
            return view('pegawai.dashboard', [
                'user' => $user,
                'pegawai' => $user, // Pass user as pegawai for view compatibility
                'totalPresensi' => $totalPresensi,
                'totalPenggajian' => $totalPenggajian,
                'stats' => $stats,
                'recentAttendance' => $recentAttendance
            ]);
        } else {
            // Admin/Owner Dashboard - Full access
            return view('dashboard', compact(
                'totalPegawai',
                'totalPresensi', 
                'totalProduk',
                'totalVendor',
                'totalBahanBaku',
                'totalSatuan',
                'totalAset',
                'totalPelanggan',
                'totalBOP',
                'totalBOM',
                'totalCOA',
                'totalProduksi',
                'totalPelanggan',
                'totalBahanPendukung',
                'totalAset',
                'totalJabatan',
                'totalBTKL',
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
    }

    /**
     * Hitung saldo akhir akun kas/bank langsung dari journal_lines + jurnal_umum
     * Sama persis dengan logika buku besar & neraca saldo
     * DENGAN FILTER USER_ID untuk multi-tenant
     */
    private function getSaldoAkhirAkun($akun, $userId)
    {
        $saldoAwal = (float)($akun->saldo_awal ?? 0);

        // Dari jurnal_umum (sistem jurnal baru) - DENGAN FILTER USER_ID
        $ju = \DB::table('jurnal_umum')
            ->where('coa_id', $akun->id)
            ->where('user_id', $userId) // 🔒 SECURITY: Filter by user_id
            ->selectRaw('COALESCE(SUM(debit),0) as total_debit, COALESCE(SUM(kredit),0) as total_kredit')
            ->first();

        $totalDebit  = (float)$ju->total_debit;
        $totalKredit = (float)$ju->total_kredit;

        // Akun Aset: saldo normal Debit
        return $saldoAwal + $totalDebit - $totalKredit;
    }

    private function getTotalKasBank()
    {
        try {
            if (!\Schema::hasTable('coas')) { return 0; }

            $akunKasBank = \App\Helpers\AccountHelper::getKasBankAccounts();
            if ($akunKasBank->isEmpty()) { return 0; }

            $total = 0;
            $user = auth()->user();
            
            foreach ($akunKasBank as $akun) {
                $total += $this->getSaldoAkhirAkun($akun, $user->id);
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
                \Log::info("Dashboard: Saldo awal dari period balance untuk {$akun->kode_akun}: " . $periodBalance->saldo_awal);
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
                \Log::info("Dashboard: Saldo awal dari previous period balance untuk {$akun->kode_akun}: " . $previousBalance->saldo_akhir);
                return is_numeric($previousBalance->saldo_akhir) ? (float) $previousBalance->saldo_akhir : 0;
            }
        }
        
        // 4. Jika tidak ada sama sekali, gunakan saldo awal dari COA
        $saldoAwalCOA = (float)$akun->saldo_awal;
        \Log::info("Dashboard: Saldo awal dari COA untuk {$akun->kode_akun}: " . $saldoAwalCOA);
        return $saldoAwalCOA;
    }

    /**
     * Get total transaksi masuk dalam periode (Debit) - sama dengan logic LaporanKasBankController
     */
    private function getTransaksiMasuk($akun, $startDate, $endDate)
    {
        $totalMasuk = 0;
        $userId = auth()->id(); // CRITICAL: Get user_id for multi-tenant filtering
        
        // 1. Penjualan (cash/transfer masuk ke kas/bank) - sama dengan LaporanKasBankController
        $penjualanMasuk = \DB::table('penjualans')
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->where('user_id', $userId) // CRITICAL: Filter by user_id
            ->where(function($query) use ($akun) {
                // Jika akun adalah Bank (mengandung kata 'bank') - check this first
                if (stripos($akun->nama_akun, 'bank') !== false) {
                    $query->where('payment_method', 'transfer');
                }
                // Jika akun adalah Kas (mengandung kata 'kas' tapi bukan 'bank')
                elseif (stripos($akun->nama_akun, 'kas') !== false) {
                    $query->where('payment_method', 'cash');
                }
            })
            ->sum('total');
            
        $totalMasuk += (float) ($penjualanMasuk ?? 0);
        
        // 2. Pelunasan Utang (pembayaran utang masuk ke kas/bank) - sama dengan LaporanKasBankController
        try {
            $pelunasanUtangMasuk = \DB::table('pelunasan_utangs')
                ->whereBetween('tanggal', [$startDate, $endDate])
                ->where('user_id', $userId) // CRITICAL: Filter by user_id
                ->where(function($query) use ($akun) {
                    // Jika akun adalah Bank (mengandung kata 'bank') - check this first
                    if (stripos($akun->nama_akun, 'bank') !== false) {
                        $query->where('payment_method', 'transfer');
                    }
                    // Jika akun adalah Kas (mengandung kata 'kas' tapi bukan 'bank')
                    elseif (stripos($akun->nama_akun, 'kas') !== false) {
                        $query->where('payment_method', 'cash');
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
                ->where('user_id', $userId) // CRITICAL: Filter by user_id
                ->where(function($query) use ($akun) {
                    $query->where(function($subQuery) use ($akun) {
                        // Jika akun adalah Bank (mengandung kata 'bank') - check this first
                        if (stripos($akun->nama_akun, 'bank') !== false) {
                            $subQuery->where('payment_method', 'transfer');
                        }
                        // Jika akun adalah Kas (mengandung kata 'kas' tapi bukan 'bank')
                        elseif (stripos($akun->nama_akun, 'kas') !== false) {
                            $subQuery->where('payment_method', 'cash');
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
        $userId = auth()->id(); // CRITICAL: Get user_id for multi-tenant filtering
        
        // 1. Pembelian (cash/transfer keluar dari kas/bank) - sama dengan LaporanKasBankController
        $pembelianKeluar = \DB::table('pembelians')
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->where('user_id', $userId) // CRITICAL: Filter by user_id
            ->where(function($query) use ($akun) {
                // Jika akun adalah Bank (mengandung kata 'bank') - check this first
                if (stripos($akun->nama_akun, 'bank') !== false) {
                    $query->where('payment_method', 'transfer');
                }
                // Jika akun adalah Kas (mengandung kata 'kas' tapi bukan 'bank')
                elseif (stripos($akun->nama_akun, 'kas') !== false) {
                    $query->where('payment_method', 'cash');
                }
            })
            ->sum('total_harga');
            
        $totalKeluar += (float) ($pembelianKeluar ?? 0);
        
        // 2. Penggajian (pengeluaran kas/bank) - sama dengan LaporanKasBankController
        try {
            $penggajianKeluar = \DB::table('penggajians')
                ->whereBetween('tanggal', [$startDate, $endDate])
                ->where('user_id', $userId) // CRITICAL: Filter by user_id
                ->where(function($query) use ($akun) {
                    // Jika akun adalah Bank (mengandung kata 'bank') - check this first
                    if (stripos($akun->nama_akun, 'bank') !== false) {
                        $query->where('payment_method', 'transfer');
                    }
                    // Jika akun adalah Kas (mengandung kata 'kas' tapi bukan 'bank')
                    elseif (stripos($akun->nama_akun, 'kas') !== false) {
                        $query->where('payment_method', 'cash');
                    }
                })
                ->sum('total_gaji');
                
            $totalKeluar += (float) ($penggajianKeluar ?? 0);
        } catch (\Exception $e) {
            // Table might not exist
        }
        
        // 3. Pembayaran Beban Operasional (pengeluaran kas/bank) - tambahan untuk dashboard
        try {
            $pembayaranBebanKeluar = \DB::table('expense_payments')
                ->whereBetween('tanggal', [$startDate, $endDate])
                ->where('user_id', $userId) // CRITICAL: Filter by user_id
                ->where(function($query) use ($akun) {
                    // Cek apakah akun kas/bank yang digunakan sesuai dengan akun yang sedang dihitung
                    $query->where('coa_kasbank', $akun->kode_akun);
                })
                ->sum('nominal_pembayaran');
                
            $totalKeluar += (float) ($pembayaranBebanKeluar ?? 0);
        } catch (\Exception $e) {
            // Table might not exist
        }
        
        // 4. Retur Penjualan (uang kembali ke kas/bank) - sama dengan LaporanKasBankController
        try {
            $returPenjualanKeluar = \DB::table('returns')
                ->whereBetween('tanggal', [$startDate, $endDate])
                ->where('user_id', $userId) // CRITICAL: Filter by user_id
                ->where(function($query) use ($akun) {
                    $query->where(function($subQuery) use ($akun) {
                        // Jika akun adalah Bank (mengandung kata 'bank') - check this first
                        if (stripos($akun->nama_akun, 'bank') !== false) {
                            $subQuery->where('payment_method', 'transfer');
                        }
                        // Jika akun adalah Kas (mengandung kata 'kas' tapi bukan 'bank')
                        elseif (stripos($akun->nama_akun, 'kas') !== false) {
                            $subQuery->where('payment_method', 'cash');
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
            $user = auth()->user();

            $totalPenjualan = Penjualan::whereBetween('tanggal', [$startOfMonth, $endOfMonth])
                ->where('user_id', $user->id) // 🔒 SECURITY: Add user_id filter
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
            $user = auth()->user();
            $totalPiutang = Penjualan::where('payment_method', 'credit')

                ->where('user_id', $user->id) // 🔒 SECURITY: Add user_id filter
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
            $user = auth()->user();
            $totalUtang = Pembelian::where('payment_method', 'credit')
                ->where('user_id', $user->id) // 🔒 SECURITY: Add user_id filter
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
        $user = auth()->user();
        
        if ($lastMonth === 0) {
            $lastMonth = 12;
            $lastMonthYear = $currentYear - 1;
        }

        $currentCount = 0;
        $lastCount = 0;

        switch ($type) {
            case 'penjualan':
                $currentCount = $this->getCountByUserAndDate('penjualans', $user->id, $currentMonth, $currentYear);
                $lastCount = $this->getCountByUserAndDate('penjualans', $user->id, $lastMonth, $lastMonthYear);
                break;
                
            case 'pembelian':
                $currentCount = $this->getCountByUserAndDate('pembelians', $user->id, $currentMonth, $currentYear);
                $lastCount = $this->getCountByUserAndDate('pembelians', $user->id, $lastMonth, $lastMonthYear);
                break;
                
            case 'produksi':
                if (\Schema::hasTable('produksis')) {
                    $currentCount = $this->getCountByUserAndDate('produksis', $user->id, $currentMonth, $currentYear);
                    $lastCount = $this->getCountByUserAndDate('produksis', $user->id, $lastMonth, $lastMonthYear);
                } else {
                    $currentCount = 0;
                    $lastCount = 0;
                }
                break;
                
            case 'retur':
                $currentCount = $this->getCountByUserAndDate('returs', $user->id, $currentMonth, $currentYear);
                $lastCount = $this->getCountByUserAndDate('returs', $user->id, $lastMonth, $lastMonthYear);
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
        return Coa::where('tipe_akun', 'PENDAPATAN')
            ->orWhere('tipe_akun', 'Pendapatan')
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

    private function getKasBankDetails()
    {
        try {
            if (!\Schema::hasTable('coas')) { return collect(); }

            $akunKasBank = \App\Helpers\AccountHelper::getKasBankAccounts();
            if ($akunKasBank->isEmpty()) { return collect(); }

            $user = auth()->user();
            $details = [];
            
            foreach ($akunKasBank as $akun) {
                $saldoAkhir = $this->getSaldoAkhirAkun($akun, $user->id);

                $details[] = [
                    'kode_akun'  => $akun->kode_akun,
                    'nama_akun'  => $akun->nama_akun,
                    'saldo_akhir'=> $saldoAkhir,
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
            $user = auth()->user();
            
            // Get last 12 months
            for ($i = 11; $i >= 0; $i--) {
                $date = now()->subMonths($i);
                $month = $date->month;
                $year = $date->year;
                
                // Get total sales for this month with safety check
                $total = $this->getSumByUserAndDate('penjualans', $user->id, $month, $year, 'total');
                
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

    /**
     * Local Development Dashboard - Mock data without database
     */
    private function localDashboard()
    {
        // Get user data from session (mock authentication)
        $userId = session('user_id', 1);
        $userRole = session('user_role', 'owner');
        $userName = session('user_name', 'Local Developer');
        $userEmail = session('user_email', 'local@example.com');
        
        // Mock data for local development - FILTERED BY USER_ID (Multi-Tenant)
        $mockData = $this->getMultiTenantMockData($userId);
        
        // Master data counts
        $totalPegawai = $mockData['pegawai'];
        $totalPresensi = $mockData['presensi'];
        $totalProduk = $mockData['produk'];
        $totalVendor = $mockData['vendor'];
        $totalBahanBaku = $mockData['bahan_baku'];
        $totalSatuan = $mockData['satuan'];
        $totalAset = $mockData['aset'];
        $totalPelanggan = $mockData['pelanggan'];
        
        // Financial data - Calculate from bank accounts
        $totalKasBank = array_sum(array_column($mockData['bank_accounts'], 'saldo_akhir'));
        $pendapatanBulanIni = $mockData['sales_chart'][11]; // Latest month
        $totalPiutang = $pendapatanBulanIni * 0.3; // 30% of monthly revenue as receivables
        $totalUtang = $pendapatanBulanIni * 0.2; // 20% of monthly revenue as payables
        
        // Mock BOP data
        $totalBop = 0;
        $bopDetails = collect();
        
        // Mock BOM data
        $totalBom = 0;
        $bomDetails = collect();
        
        // Mock COA data
        $totalCoa = 0;
        $coaDetails = collect();
        
        // Mock chart data - FILTERED BY USER_ID
        $salesChartData = [
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            'data' => $mockData['sales_chart']
        ];
        
        // Mock bank accounts - FILTERED BY USER_ID
        $kasBankDetails = collect($mockData['bank_accounts']);
        
        return view('dashboard', compact(
            'userRole',
            'totalPegawai',
            'totalPresensi', 
            'totalProduk',
            'totalVendor',
            'totalBahanBaku',
            'totalSatuan',
            'totalAset',
            'totalPelanggan',
            'totalKasBank',
            'pendapatanBulanIni',
            'totalPiutang',
            'totalUtang',
            'totalBop',
            'bopDetails',
            'totalBom',
            'bomDetails',
            'totalCoa',
            'coaDetails',
            'salesChartData',
            'kasBankDetails'
        ));
    }

    /**
     * Helper method untuk mendapatkan count dengan safety check untuk user_id column
     */
    private function getCountByUser($table, $userId, $column = 'id')
    {
        try {
            if (!\Schema::hasTable($table)) {
                return 0;
            }
            
            // Check if user_id column exists
            if (!\Schema::hasColumn($table, 'user_id')) {
                // If no user_id column, return total count (for backward compatibility)
                return \DB::table($table)->count();
            }
            
            // If user_id column exists, filter by user_id
            return \DB::table($table)->where('user_id', $userId)->count();
            
        } catch (\Exception $e) {
            // Return 0 if any error occurs
            return 0;
        }
    }

    /**
     * Helper method untuk mendapatkan count dengan safety check untuk user_id column dan tanggal
     */
    private function getCountByUserAndDate($table, $userId, $month, $year)
    {
        try {
            if (!\Schema::hasTable($table)) {
                return 0;
            }
            
            $query = \DB::table($table);
            
            // Check if user_id column exists
            if (\Schema::hasColumn($table, 'user_id')) {
                $query->where('user_id', $userId);
            }
            
            // Check if tanggal column exists (for returs table)
            if (\Schema::hasColumn($table, 'tanggal')) {
                $query->whereMonth('tanggal', $month)
                      ->whereYear('tanggal', $year);
            }
            
            return $query->count();
            
        } catch (\Exception $e) {
            // Return 0 if any error occurs
            return 0;
        }
    }

    /**
     * Helper method untuk mendapatkan sum dengan safety check untuk user_id column dan tanggal
     */
    private function getSumByUserAndDate($table, $userId, $month, $year, $column = 'total')
    {
        try {
            if (!\Schema::hasTable($table)) {
                return 0;
            }
            
            $query = \DB::table($table);
            
            // Check if user_id column exists
            if (\Schema::hasColumn($table, 'user_id')) {
                $query->where('user_id', $userId);
            }
            
            // Check if tanggal column exists
            if (\Schema::hasColumn($table, 'tanggal')) {
                $query->whereMonth('tanggal', $month)
                      ->whereYear('tanggal', $year);
            }
            
            // Check if the sum column exists
            if (\Schema::hasColumn($table, $column)) {
                return $query->sum($column);
            }
            
            return 0;
            
        } catch (\Exception $e) {
            // Return 0 if any error occurs
            return 0;
        }
    }

    /**
     * Get mock data filtered by user_id for multi-tenant testing
     */
    private function getMultiTenantMockData($userId)
    {
        // Different data for each user to simulate multi-tenant behavior
        $mockData = [
            // User ID 1 (Owner - Arkan)
            1 => [
                'pegawai' => 15,
                'presensi' => 120,
                'produk' => 25,
                'vendor' => 8,
                'bahan_baku' => 30,
                'satuan' => 10,
                'aset' => 12,
                'pelanggan' => 45,
                'sales_chart' => [12000000, 15000000, 18000000, 14000000, 20000000, 22000000, 19000000, 25000000, 21000000, 23000000, 26000000, 28000000],
                'bank_accounts' => [
                    ['kode_akun' => '11100', 'nama_akun' => 'Kas', 'saldo_akhir' => 15000000],
                    ['kode_akun' => '11200', 'nama_akun' => 'Bank BCA', 'saldo_akhir' => 45000000],
                    ['kode_akun' => '11300', 'nama_akun' => 'Bank Mandiri', 'saldo_akhir' => 30000000]
                ]
            ],
            
            // User ID 2 (Kasir)
            2 => [
                'pegawai' => 8,
                'presensi' => 65,
                'produk' => 18,
                'vendor' => 5,
                'bahan_baku' => 22,
                'satuan' => 8,
                'aset' => 7,
                'pelanggan' => 32,
                'sales_chart' => [8000000, 9500000, 11000000, 9000000, 13000000, 14000000, 12000000, 16000000, 13500000, 14500000, 17000000, 18000000],
                'bank_accounts' => [
                    ['kode_akun' => '11100', 'nama_akun' => 'Kas', 'saldo_akhir' => 8000000],
                    ['kode_akun' => '11200', 'nama_akun' => 'Bank BCA', 'saldo_akhir' => 25000000]
                ]
            ],
            
            // User ID 3 (Pegawai Pembelian)
            3 => [
                'pegawai' => 12,
                'presensi' => 95,
                'produk' => 20,
                'vendor' => 12,
                'bahan_baku' => 35,
                'satuan' => 12,
                'aset' => 9,
                'pelanggan' => 28,
                'sales_chart' => [10000000, 12000000, 14000000, 11000000, 16000000, 17000000, 15000000, 19000000, 16500000, 17500000, 20000000, 21000000],
                'bank_accounts' => [
                    ['kode_akun' => '11100', 'nama_akun' => 'Kas', 'saldo_akhir' => 12000000],
                    ['kode_akun' => '11200', 'nama_akun' => 'Bank BCA', 'saldo_akhir' => 35000000],
                    ['kode_akun' => '11300', 'nama_akun' => 'Bank Mandiri', 'saldo_akhir' => 20000000]
                ]
            ],
            
            // User ID 4 (Pegawai)
            4 => [
                'pegawai' => 6,
                'presensi' => 48,
                'produk' => 12,
                'vendor' => 4,
                'bahan_baku' => 18,
                'satuan' => 6,
                'aset' => 5,
                'pelanggan' => 20,
                'sales_chart' => [6000000, 7000000, 8000000, 6500000, 9000000, 10000000, 8500000, 11000000, 9500000, 10500000, 12000000, 13000000],
                'bank_accounts' => [
                    ['kode_akun' => '11100', 'nama_akun' => 'Kas', 'saldo_akhir' => 6000000],
                    ['kode_akun' => '11200', 'nama_akun' => 'Bank BCA', 'saldo_akhir' => 18000000]
                ]
            ]
        ];
        
        // Return data for the specific user, or default data if user not found
        return $mockData[$userId] ?? $mockData[1];
    }
}
