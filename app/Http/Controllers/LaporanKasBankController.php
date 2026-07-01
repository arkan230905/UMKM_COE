<?php

namespace App\Http\Controllers;

use App\Models\Coa;
use App\Models\JournalEntry;
use App\Models\Penjualan;
use App\Models\Pembelian;
use App\Models\ExpensePayment;
use App\Models\PelunasanUtang;
use App\Models\Penggajian;
use App\Models\Retur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Helpers\AccountHelper;
use App\Services\KasBankConsistencyService;

class LaporanKasBankController extends Controller
{
    /**
     * Display laporan kas & bank
     */
    public function index(Request $request)
    {
        // Default periode: bulan ini
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));
        
        // Validasi konsistensi data (log untuk monitoring)
        KasBankConsistencyService::logConsistencyCheck($startDate, $endDate);
        
        // Ambil HANYA akun Kas dan Bank menggunakan helper untuk konsistensi
        $akunKasBank = AccountHelper::getKasBankAccounts();
        
        // Hitung saldo untuk setiap akun
        $dataKasBank = [];
        $totalKeseluruhan = 0;
        $totalSaldoAwal = 0;
        $totalTransaksiMasuk = 0;
        $totalTransaksiKeluar = 0;
        $totalKas = 0;
        $totalBank = 0;
        
        foreach ($akunKasBank as $akun) {
            $saldoAwal = $this->getSaldoAwal($akun, $startDate);
            $transaksiMasuk = $this->getTransaksiMasuk($akun, $startDate, $endDate);
            $transaksiKeluar = $this->getTransaksiKeluar($akun, $startDate, $endDate);
            
            // Untuk akun Kas & Bank (Aset), saldo normal adalah Debit
            // Saldo Akhir = Saldo Awal + Debit (Masuk) - Kredit (Keluar)
            $saldoAkhir = $saldoAwal + $transaksiMasuk - $transaksiKeluar;
            
            // Cek kategori akun (Kas atau Bank)
            $kategori = AccountHelper::getAccountCategory($akun->kode_akun);
            
            $dataKasBank[] = [
                'id' => $akun->kode_akun, // Use kode_akun as ID for API calls
                'kode_akun' => $akun->kode_akun,
                'nama_akun' => $akun->nama_akun,
                'saldo_awal' => $saldoAwal,
                'transaksi_masuk' => $transaksiMasuk,
                'transaksi_keluar' => $transaksiKeluar,
                'saldo_akhir' => $saldoAkhir,
                'kategori' => $kategori
            ];
            
            $totalKeseluruhan += $saldoAkhir;
            $totalSaldoAwal += $saldoAwal;
            $totalTransaksiMasuk += $transaksiMasuk;
            $totalTransaksiKeluar += $transaksiKeluar;
            
            // Pisahkan total kas dan bank
            if ($kategori === 'Kas') {
                $totalKas += $saldoAkhir;
            } elseif ($kategori === 'Bank') {
                $totalBank += $saldoAkhir;
            }
        }
        
        return view('laporan.kas-bank.index', compact(
            'dataKasBank',
            'totalKeseluruhan',
            'totalSaldoAwal',
            'totalTransaksiMasuk',
            'totalTransaksiKeluar',
            'totalKas',
            'totalBank',
            'startDate',
            'endDate'
        ));
    }
    
    /**
     * Get saldo awal sebelum periode
     * Saldo Awal = Saldo dari saldo_awal COA (Master Data COA)
     * Untuk Kas & Bank, saldo_awal harus diisi manual di Master Data COA
     */
    private function getSaldoAwal($akun, $startDate)
    {
        // PRIORITAS 1: Gunakan saldo_awal dari Master Data COA
        // Saldo awal harus diinput manual di Master Data > COA
        if (isset($akun->saldo_awal) && is_numeric($akun->saldo_awal)) {
            return (float) $akun->saldo_awal;
        }
        
        // PRIORITAS 2: Cari dari periode balance (backup)
        $periode = \App\Models\CoaPeriod::where('periode', date('Y-m', strtotime($startDate)))->first();
        
        if ($periode) {
            $periodBalance = \App\Models\CoaPeriodBalance::where('kode_akun', $akun->kode_akun)
                ->where('period_id', $periode->id)
                ->first();
            
            if ($periodBalance && is_numeric($periodBalance->saldo_awal)) {
                return (float) $periodBalance->saldo_awal;
            }
            
            // Cek periode sebelumnya
            $previousPeriod = $periode->getPreviousPeriod();
            if ($previousPeriod) {
                $previousBalance = \App\Models\CoaPeriodBalance::where('kode_akun', $akun->kode_akun)
                    ->where('period_id', $previousPeriod->id)
                    ->first();
                
                if ($previousBalance && is_numeric($previousBalance->saldo_akhir)) {
                    return (float) $previousBalance->saldo_akhir;
                }
            }
        }
        
        // Default: 0 jika tidak ada data
        return 0;
    }
    
    /**
     * Get total transaksi masuk dalam periode (Debit)
     * SAMA DENGAN LOGIKA BUKU BESAR - hanya dari jurnal_umum
     */
    private function getTransaksiMasuk($akun, $startDate, $endDate)
    {
        // Hanya dari jurnal_umum (sistem jurnal yang digunakan)
        $journalMasuk = \DB::table('jurnal_umum as ju')
            ->where('ju.coa_id', $akun->id)
            ->where('ju.user_id', auth()->id()) // MULTI-TENANT: Filter by user_id
            ->whereBetween('ju.tanggal', [$startDate, $endDate])
            ->sum('ju.debit') ?? 0;
        
        return (float) $journalMasuk;
    }
    
    /**
     * Get total transaksi keluar dalam periode (Kredit)
     * SAMA DENGAN LOGIKA BUKU BESAR - hanya dari jurnal_umum
     */
    private function getTransaksiKeluar($akun, $startDate, $endDate)
    {
        // Hanya dari jurnal_umum (sistem jurnal yang digunakan)
        $journalKeluar = \DB::table('jurnal_umum as ju')
            ->where('ju.coa_id', $akun->id)
            ->where('ju.user_id', auth()->id()) // MULTI-TENANT: Filter by user_id
            ->whereBetween('ju.tanggal', [$startDate, $endDate])
            ->sum('ju.kredit') ?? 0;
        
        return (float) $journalKeluar;
    }
    
    /**
     * Map COA code to accounts table code
     */
    private function mapCoaToAccountCode($coaCode)
    {
        $mapping = [
            '1110' => '101', // Kas COA -> Kas Account
            '1120' => '102', // Bank COA -> Bank Account
            '101' => '101',  // Direct mapping
            '102' => '102',  // Direct mapping
        ];
        
        return $mapping[$coaCode] ?? $coaCode;
    }
    
    /**
     * Get detail transaksi masuk
     */
    public function getDetailMasuk(Request $request, $coaId)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));
        
        // Try to find COA by ID first, then by kode_akun
        $coa = Coa::find($coaId);
        if (!$coa) {
            $coa = Coa::where('kode_akun', $coaId)->first();
        }
        
        if (!$coa) {
            return response()->json([]);
        }
        
        $transaksi = collect();
        
        // Ambil transaksi masuk (debit) dari jurnal_umum (sistem jurnal)
        $transaksiBaru = \DB::table('jurnal_umum as ju')
            ->where('ju.coa_id', $coa->id)
            ->where('ju.user_id', auth()->id()) // MULTI-TENANT: Filter by user_id
            ->where('ju.debit', '>', 0)
            ->whereBetween('ju.tanggal', [$startDate, $endDate])
            ->orderBy('ju.tanggal', 'desc')
            ->orderBy('ju.id', 'desc')
            ->select(
                'ju.tanggal',
                'ju.keterangan',
                'ju.tipe_referensi',
                'ju.referensi',
                'ju.debit'
            )
            ->get()
            ->map(function($line) {
                // Get detailed information based on ref_type
                $detailInfo = $this->getTransactionDetail($line->tipe_referensi, $line->referensi);
                
                return [
                    'tanggal' => date('d/m/Y', strtotime($line->tanggal)),
                    'nomor_transaksi' => $detailInfo['nomor_transaksi'] ?? $line->keterangan,
                    'keterangan' => $line->keterangan,
                    'nominal' => $line->debit,
                    'tipe' => 'debit',
                    'referensi' => $detailInfo['referensi'] ?? null,
                    'sistem' => 'new'
                ];
            });

        // Ambil transaksi masuk (debit) dari jurnal_umum (sistem jurnal lama)
        $transaksiLama = \App\Models\JurnalUmum::where('coa_id', $coa->id)
            ->where('debit', '>', 0)
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc')
            ->get()
            ->map(function($jurnal) {
                return [
                    'tanggal' => date('d/m/Y', strtotime($jurnal->tanggal)),
                    'nomor_transaksi' => $jurnal->referensi ?? 'JU-' . $jurnal->id,
                    'jenis' => ucfirst(str_replace('_', ' ', $jurnal->tipe_referensi ?? 'jurnal_umum')),
                    'keterangan' => $jurnal->keterangan ?? '-',
                    'nominal' => (float)$jurnal->debit,
                    'source' => 'jurnal_umum',
                    'ref_key' => $jurnal->tipe_referensi . '_' . $jurnal->referensi . '_' . $jurnal->debit
                ];
            });

        // Add raw data to transactions for proper deduplication
        $transaksiBaruWithRaw = \DB::table('jurnal_umum as ju')
            ->where('ju.coa_id', $coa->id)
            ->where('ju.user_id', auth()->id()) // MULTI-TENANT: Filter by user_id
            ->where('ju.debit', '>', 0)
            ->whereBetween('ju.tanggal', [$startDate, $endDate])
            ->orderBy('ju.tanggal', 'desc')
            ->orderBy('ju.id', 'desc')
            ->select(
                'ju.tanggal',
                'ju.tipe_referensi',
                'ju.referensi',
                'ju.debit'
            )
            ->get();

        $transaksiLamaWithRaw = \App\Models\JurnalUmum::where('coa_id', $coa->id)
            ->where('debit', '>', 0)
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc')
            ->select('tanggal', 'tipe_referensi', 'referensi', 'debit')
            ->get();

        // Create a set of duplicates to remove
        $duplicatesToRemove = collect();
        
        foreach ($transaksiBaruWithRaw as $jl) {
            foreach ($transaksiLamaWithRaw as $ju) {
                $jlDate = date('Y-m-d', strtotime($jl->tanggal));
                $juDate = date('Y-m-d', strtotime($ju->tanggal));
                
                if ($jlDate === $juDate && abs($jl->debit - $ju->debit) < 0.01) {
                    // Check ref type match
                    $refTypeMatch = (
                        ($jl->tipe_referensi === 'purchase' && $ju->tipe_referensi === 'pembelian') ||
                        ($jl->tipe_referensi === 'purchase' && $ju->tipe_referensi === 'purchase') ||
                        ($jl->tipe_referensi === 'pembelian' && $ju->tipe_referensi === 'pembelian') ||
                        ($jl->tipe_referensi === 'sale' && $ju->tipe_referensi === 'sale') ||
                        ($jl->tipe_referensi === 'sale' && $ju->tipe_referensi === 'penjualan') ||
                        ($jl->tipe_referensi === 'expense_payment' && $ju->tipe_referensi === 'pembayaran_beban') ||
                        ($jl->tipe_referensi === 'expense_payment' && $ju->tipe_referensi === 'expense_payment') ||
                        ($jl->tipe_referensi === 'pembayaran_beban' && $ju->tipe_referensi === 'pembayaran_beban') ||
                        ($jl->tipe_referensi === 'penggajian' && $ju->tipe_referensi === 'penggajian')
                    );
                    
                    // Check penjualan match
                    $penjualanMatch = false;
                    if ($jl->tipe_referensi === 'sale' && ($ju->tipe_referensi === 'sale' || $ju->tipe_referensi === 'penjualan')) {
                        if (preg_match('/sale#(\d+)/', $ju->referensi, $matches)) {
                            $penjualanId = (int)$matches[1];
                            if ($jl->referensi == $penjualanId) {
                                $penjualanMatch = true;
                            }
                        } elseif (preg_match('/SJ-\d+-(\d+)/', $ju->referensi, $matches)) {
                            $penjualanId = (int)$matches[1];
                            if ($jl->referensi == $penjualanId) {
                                $penjualanMatch = true;
                            }
                        }
                    }
                    
                    // Check pembelian match by ID
                    $pembelianMatch = false;
                    if (($jl->tipe_referensi === 'purchase' || $jl->tipe_referensi === 'pembelian') && 
                        ($ju->tipe_referensi === 'pembelian' || $ju->tipe_referensi === 'purchase')) {
                        if (preg_match('/purchase#(\d+)|pembelian#(\d+)|PB-(\d+)/', $ju->referensi, $matches)) {
                            $pembelianId = (int)($matches[1] ?? $matches[2] ?? $matches[3] ?? 0);
                            if ($pembelianId > 0 && $jl->referensi == $pembelianId) {
                                $pembelianMatch = true;
                            }
                        }
                    }
                    
                    // Check expense payment match by ID
                    $expenseMatch = false;
                    if (($jl->tipe_referensi === 'expense_payment' || $jl->tipe_referensi === 'pembayaran_beban') && 
                        ($ju->tipe_referensi === 'pembayaran_beban' || $ju->tipe_referensi === 'expense_payment')) {
                        if (preg_match('/expense_payment#(\d+)|pembayaran_beban#(\d+)|BP-(\d+)|PS-(\d+)/', $ju->referensi, $matches)) {
                            $expenseId = (int)($matches[1] ?? $matches[2] ?? $matches[3] ?? $matches[4] ?? 0);
                            if ($expenseId > 0 && $jl->referensi == $expenseId) {
                                $expenseMatch = true;
                            }
                        }
                    }
                    
                    if ($refTypeMatch || $penjualanMatch || $pembelianMatch || $expenseMatch) {
                        // Mark the old system transaction for removal
                        $duplicateKey = $ju->tanggal . '_' . $ju->referensi . '_' . $ju->debit;
                        $duplicatesToRemove->push($duplicateKey);
                        break;
                    }
                }
            }
        }

        // Combine transactions and remove duplicates
        $allTransactions = $transaksiBaru->concat($transaksiLama);
        
        $uniqueTransactions = $allTransactions->filter(function($transaction) use ($duplicatesToRemove) {
            // If this is from the old system, check if it's a duplicate
            if (isset($transaction['source']) && $transaction['source'] === 'jurnal_umum') {
                // Use the same date format as in detection (Y-m-d with time from formatted d/m/Y)
                $dateParts = explode('/', $transaction['tanggal']);
                if (count($dateParts) === 3) {
                    $ymdDate = $dateParts[2] . '-' . $dateParts[1] . '-' . $dateParts[0] . ' 00:00:00';
                } else {
                    $ymdDate = $transaction['tanggal'];
                }
                // Format nominal to match detection format (with 2 decimal places)
                $formattedNominal = number_format($transaction['nominal'], 2, '.', '');
                $duplicateKey = $ymdDate . '_' . $transaction['nomor_transaksi'] . '_' . $formattedNominal;
                return !$duplicatesToRemove->contains($duplicateKey);
            }
            return true; // Keep all new system transactions
        });

        $transaksi = $uniqueTransactions
            ->filter(function($item) {
                return $item['nominal'] > 0;
            })
            ->sortByDesc('tanggal')
            ->values();
        
        return response()->json($transaksi);
    }

    public function getDetailKeluar(Request $request, $coaId)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));
        
        // Try to find COA by ID first, then by kode_akun
        $coa = Coa::find($coaId);
        if (!$coa) {
            $coa = Coa::where('kode_akun', $coaId)->first();
        }
        
        if (!$coa) {
            return response()->json([]);
        }
        
        $transaksi = collect();
        $processedTransactions = collect(); // Track processed transactions to avoid duplicates
        
        // Ambil transaksi keluar (kredit) dari jurnal_umum (sistem jurnal)
        $transaksiBaru = \DB::table('jurnal_umum as ju')
            ->where('ju.coa_id', $coa->id)
            ->where('ju.user_id', auth()->id()) // MULTI-TENANT: Filter by user_id
            ->where('ju.kredit', '>', 0)
            ->whereBetween('ju.tanggal', [$startDate, $endDate])
            ->orderBy('ju.tanggal', 'desc')
            ->orderBy('ju.id', 'desc')
            ->select(
                'ju.tanggal',
                'ju.keterangan',
                'ju.tipe_referensi',
                'ju.referensi',
                'ju.kredit'
            )
            ->get()
            ->map(function($line) {
                // Get detailed information based on ref_type
                $detailInfo = $this->getTransactionDetail($line->tipe_referensi, $line->referensi);
                
                return [
                    'tanggal' => date('d/m/Y', strtotime($line->tanggal)),
                    'nomor_transaksi' => $detailInfo['nomor_transaksi'] ?? $line->keterangan,
                    'keterangan' => $line->keterangan,
                    'nominal' => $line->kredit,
                    'tipe' => 'kredit',
                    'referensi' => $detailInfo['referensi'] ?? null,
                    'sistem' => 'new'
                ];
            });

        // Ambil transaksi keluar (kredit) dari jurnal_umum (sistem jurnal lama)
        $transaksiLama = \App\Models\JurnalUmum::where('coa_id', $coa->id)
            ->where('kredit', '>', 0)
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc')
            ->get()
            ->map(function($jurnal) {
                return [
                    'tanggal' => date('d/m/Y', strtotime($jurnal->tanggal)),
                    'nomor_transaksi' => $jurnal->referensi ?? 'JU-' . $jurnal->id,
                    'jenis' => ucfirst(str_replace('_', ' ', $jurnal->tipe_referensi ?? 'jurnal_umum')),
                    'keterangan' => $jurnal->keterangan ?? '-',
                    'nominal' => (float)$jurnal->kredit,
                    'source' => 'jurnal_umum',
                    'ref_key' => $jurnal->tipe_referensi . '_' . $jurnal->referensi . '_' . $jurnal->kredit
                ];
            });

        // Add raw data to transactions for proper deduplication
        $transaksiBaruWithRaw = \DB::table('jurnal_umum as ju')
            ->where('ju.coa_id', $coa->id)
            ->where('ju.user_id', auth()->id()) // MULTI-TENANT: Filter by user_id
            ->where('ju.kredit', '>', 0)
            ->whereBetween('ju.tanggal', [$startDate, $endDate])
            ->orderBy('ju.tanggal', 'desc')
            ->orderBy('ju.id', 'desc')
            ->select(
                'ju.tanggal',
                'ju.tipe_referensi',
                'ju.referensi',
                'ju.kredit'
            )
            ->get();

        $transaksiLamaWithRaw = \App\Models\JurnalUmum::where('coa_id', $coa->id)
            ->where('kredit', '>', 0)
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc')
            ->select('tanggal', 'tipe_referensi', 'referensi', 'kredit')
            ->get();

        // Create a set of duplicates to remove
        $duplicatesToRemove = collect();
        
        foreach ($transaksiBaruWithRaw as $jl) {
            foreach ($transaksiLamaWithRaw as $ju) {
                $jlDate = date('Y-m-d', strtotime($jl->tanggal));
                $juDate = date('Y-m-d', strtotime($ju->tanggal));
                
                if ($jlDate === $juDate && abs($jl->kredit - $ju->kredit) < 0.01) {
                    // Check ref type match
                    $refTypeMatch = (
                        ($jl->tipe_referensi === 'purchase' && $ju->tipe_referensi === 'pembelian') ||
                        ($jl->tipe_referensi === 'purchase' && $ju->tipe_referensi === 'purchase') ||
                        ($jl->tipe_referensi === 'pembelian' && $ju->tipe_referensi === 'pembelian') ||
                        ($jl->tipe_referensi === 'sale' && $ju->tipe_referensi === 'sale') ||
                        ($jl->tipe_referensi === 'sale' && $ju->tipe_referensi === 'penjualan') ||
                        ($jl->tipe_referensi === 'expense_payment' && $ju->tipe_referensi === 'pembayaran_beban') ||
                        ($jl->tipe_referensi === 'expense_payment' && $ju->tipe_referensi === 'expense_payment') ||
                        ($jl->tipe_referensi === 'pembayaran_beban' && $ju->tipe_referensi === 'pembayaran_beban') ||
                        ($jl->tipe_referensi === 'penggajian' && $ju->tipe_referensi === 'penggajian')
                    );
                    
                    // Check penjualan match
                    $penjualanMatch = false;
                    if ($jl->tipe_referensi === 'sale' && ($ju->tipe_referensi === 'sale' || $ju->tipe_referensi === 'penjualan')) {
                        if (preg_match('/sale#(\d+)/', $ju->referensi, $matches)) {
                            $penjualanId = (int)$matches[1];
                            if ($jl->referensi == $penjualanId) {
                                $penjualanMatch = true;
                            }
                        } elseif (preg_match('/SJ-\d+-(\d+)/', $ju->referensi, $matches)) {
                            $penjualanId = (int)$matches[1];
                            if ($jl->referensi == $penjualanId) {
                                $penjualanMatch = true;
                            }
                        }
                    }
                    
                    // Check pembelian match by ID
                    $pembelianMatch = false;
                    if (($jl->tipe_referensi === 'purchase' || $jl->tipe_referensi === 'pembelian') && 
                        ($ju->tipe_referensi === 'pembelian' || $ju->tipe_referensi === 'purchase')) {
                        if (preg_match('/purchase#(\d+)|pembelian#(\d+)|PB-(\d+)/', $ju->referensi, $matches)) {
                            $pembelianId = (int)($matches[1] ?? $matches[2] ?? $matches[3] ?? 0);
                            if ($pembelianId > 0 && $jl->referensi == $pembelianId) {
                                $pembelianMatch = true;
                            }
                        }
                    }
                    
                    // Check expense payment match by ID
                    $expenseMatch = false;
                    if (($jl->tipe_referensi === 'expense_payment' || $jl->tipe_referensi === 'pembayaran_beban') && 
                        ($ju->tipe_referensi === 'pembayaran_beban' || $ju->tipe_referensi === 'expense_payment')) {
                        if (preg_match('/expense_payment#(\d+)|pembayaran_beban#(\d+)|BP-(\d+)|PS-(\d+)/', $ju->referensi, $matches)) {
                            $expenseId = (int)($matches[1] ?? $matches[2] ?? $matches[3] ?? $matches[4] ?? 0);
                            if ($expenseId > 0 && $jl->referensi == $expenseId) {
                                $expenseMatch = true;
                            }
                        }
                    }
                    
                    if ($refTypeMatch || $penjualanMatch || $pembelianMatch || $expenseMatch) {
                        // Mark the old system transaction for removal
                        $duplicateKey = $ju->tanggal . '_' . $ju->referensi . '_' . $ju->kredit;
                        $duplicatesToRemove->push($duplicateKey);
                        break;
                    }
                }
            }
        }

        // Combine transactions and remove duplicates
        $allTransactions = $transaksiBaru->concat($transaksiLama);
        
        $uniqueTransactions = $allTransactions->filter(function($transaction) use ($duplicatesToRemove) {
            // If this is from the old system, check if it's a duplicate
            if (isset($transaction['source']) && $transaction['source'] === 'jurnal_umum') {
                // Use the same date format as in detection (Y-m-d with time from formatted d/m/Y)
                $dateParts = explode('/', $transaction['tanggal']);
                if (count($dateParts) === 3) {
                    $ymdDate = $dateParts[2] . '-' . $dateParts[1] . '-' . $dateParts[0] . ' 00:00:00';
                } else {
                    $ymdDate = $transaction['tanggal'];
                }
                // Format nominal to match detection format (with 2 decimal places)
                $formattedNominal = number_format($transaction['nominal'], 2, '.', '');
                $duplicateKey = $ymdDate . '_' . $transaction['nomor_transaksi'] . '_' . $formattedNominal;
                return !$duplicatesToRemove->contains($duplicateKey);
            }
            return true; // Keep all new system transactions
        });

        $transaksi = $uniqueTransactions
            ->filter(function($item) {
                return $item && $item['nominal'] > 0;
            })
            ->sortByDesc('tanggal')
            ->values();
        
        return response()->json($transaksi);
    }

    /**
     * Extract ref_id from referensi string
     */
    private function extractRefId($referensi)
    {
        // Handle format like "PB-2" -> extract 2
        if (preg_match('/(\d+)$/', $referensi, $matches)) {
            return (int)$matches[1];
        }
        return null;
    }

    /**
     * Get transaction detail based on ref_type and ref_id
     */
    private function getTransactionDetail($refType, $refId)
    {
        $defaultDetail = [
            'nomor_transaksi' => 'N/A',
            'jenis' => 'Transaksi',
            'keterangan' => 'Transaksi umum'
        ];

        try {
            switch ($refType) {
                case 'sale':
                case 'penjualan':
                    $sale = \App\Models\Penjualan::where('id', $refId)
                        ->where('user_id', auth()->id())
                        ->first();
                    if ($sale) {
                        return [
                            'nomor_transaksi' => $sale->nomor_penjualan ?? "PJ-{$refId}",
                            'jenis' => 'Penjualan',
                            'keterangan' => 'Penjualan ' . ucfirst($sale->payment_method ?? 'cash')
                        ];
                    }
                    // Fallback if sale not found
                    return [
                        'nomor_transaksi' => "PJ-{$refId}",
                        'jenis' => 'Penjualan',
                        'keterangan' => 'Penjualan'
                    ];
                    break;
                    
                case 'purchase':
                case 'pembelian':
                    $purchase = \App\Models\Pembelian::where('id', $refId)
                        ->where('user_id', auth()->id())
                        ->first();
                    if ($purchase) {
                        return [
                            'nomor_transaksi' => $purchase->nomor_pembelian ?? "PB-{$refId}",
                            'jenis' => 'Pembelian',
                            'keterangan' => 'Pembelian ' . ($purchase->vendor->nama ?? '') . ' - ' . ucfirst($purchase->payment_method ?? 'cash')
                        ];
                    }
                    // Fallback if purchase not found
                    return [
                        'nomor_transaksi' => "PB-{$refId}",
                        'jenis' => 'Pembelian',
                        'keterangan' => 'Pembelian'
                    ];
                    break;
                    
                case 'expense_payment':
                case 'expense':
                    $expense = \App\Models\ExpensePayment::with('bebanOperasional')->find($refId);
                    if ($expense) {
                        $bebanName = $expense->bebanOperasional->nama_beban ?? 'Beban';
                        return [
                            'nomor_transaksi' => "BP-{$refId}",
                            'jenis' => 'Pembayaran Beban',
                            'keterangan' => "Pembayaran {$bebanName}"
                        ];
                    }
                    break;
                    
                case 'pembayaran_beban':
                    $pembayaranBeban = \App\Models\PembayaranBeban::with('bebanOperasional')->find($refId);
                    if ($pembayaranBeban) {
                        $bebanName = $pembayaranBeban->bebanOperasional->nama_beban ?? 'Beban';
                        return [
                            'nomor_transaksi' => "PB-{$refId}",
                            'jenis' => 'Pembayaran Beban',
                            'keterangan' => "Pembayaran {$bebanName}"
                        ];
                    }
                    break;
                    
                case 'penggajian':
                case 'payroll':
                    $penggajian = \App\Models\Penggajian::find($refId);
                    if ($penggajian) {
                        return [
                            'nomor_transaksi' => "GJ-{$refId}",
                            'jenis' => 'Penggajian',
                            'keterangan' => 'Penggajian karyawan'
                        ];
                    }
                    break;
                    
                case 'retur':
                case 'return':
                    $retur = \App\Models\Retur::find($refId);
                    if ($retur) {
                        return [
                            'nomor_transaksi' => "RTR-{$refId}",
                            'jenis' => 'Retur',
                            'keterangan' => 'Retur penjualan'
                        ];
                    }
                    break;
                    
                case 'retur_penjualan':
                    $returPenjualan = \DB::table('retur_penjualans')->find($refId);
                    if ($returPenjualan) {
                        return [
                            'nomor_transaksi' => $returPenjualan->nomor_retur ?? "RET-{$refId}",
                            'jenis' => 'Retur Penjualan',
                            'keterangan' => 'Refund retur penjualan'
                        ];
                    }
                    break;
                    
                case 'purchase_return_refund':
                    $purchaseReturn = \DB::table('purchase_returns')->find($refId);
                    if ($purchaseReturn) {
                        return [
                            'nomor_transaksi' => $purchaseReturn->return_number ?? "PRTN-{$refId}",
                            'jenis' => 'Retur Pembelian',
                            'keterangan' => 'Refund retur pembelian'
                        ];
                    }
                    break;
                    
                case 'pelunasan_utang':
                case 'ap_settlement':
                    return [
                        'nomor_transaksi' => "PU-{$refId}",
                        'jenis' => 'Pelunasan Utang',
                        'keterangan' => 'Pelunasan utang supplier'
                    ];
                    break;
                    
                case 'produksi':
                case 'production':
                    return [
                        'nomor_transaksi' => "PRD-{$refId}",
                        'jenis' => 'Produksi',
                        'keterangan' => 'Proses produksi'
                    ];
                    break;
                    
                case 'saldo_awal':
                    return [
                        'nomor_transaksi' => "SA-{$refId}",
                        'jenis' => 'Saldo Awal',
                        'keterangan' => 'Saldo awal periode'
                    ];
                    break;
            }
        } catch (\Exception $e) {
            // Return default if error
        }

        return $defaultDetail;
    }
    
    /**
     * Get nomor transaksi dari data langsung
     */
    private function getNomorTransaksiFromData($line)
    {
        if (!$line) return '-';
        
        $referenceType = $line->ref_type ?? '';
        $referenceId = $line->ref_id ?? null;
        
        if (!$referenceId) {
            return 'JU-' . $line->journal_entry_id;
        }
        
        // Ambil nomor transaksi dari tabel asli
        try {
            switch ($referenceType) {
                case 'sale':
                case 'sale_cogs':
                case 'penjualan':
                    return 'PJ-' . $referenceId;
                    
                case 'purchase':
                case 'pembelian':
                    return 'PB-' . $referenceId;
                    
                case 'expense_payment':
                case 'expense':
                    return 'BP-' . $referenceId;
                    
                case 'pelunasan_utang':
                    return 'PU-' . $referenceId;
                    
                case 'penggajian':
                    return 'GJ-' . $referenceId;
                    
                case 'retur':
                    return 'RTR-' . $referenceId;
                    
                case 'produksi':
                    return 'PRD-' . $referenceId;
                    
                case 'saldo_awal':
                    return 'SA-' . $referenceId;
                    
                default:
                    return 'JU-' . $line->journal_entry_id;
            }
        } catch (\Exception $e) {
            return 'TRX-' . $referenceId;
        }
    }
    
    /**
     * Get jenis transaksi dari data langsung
     */
    private function getJenisTransaksiFromData($line)
    {
        if (!$line) return 'Jurnal Umum';
        
        $referenceType = $line->ref_type ?? '';
        
        $jenisMap = [
            'sale' => 'Penjualan',
            'sale_cogs' => 'HPP Penjualan',
            'penjualan' => 'Penjualan',
            'purchase' => 'Pembelian',
            'pembelian' => 'Pembelian',
            'expense_payment' => 'Pembayaran Beban',
            'expense' => 'Pembayaran Beban',
            'pelunasan_utang' => 'Pelunasan Utang',
            'ap_settlement' => 'Pelunasan Utang',
            'penggajian' => 'Penggajian',
            'payroll' => 'Penggajian',
            'retur' => 'Retur',
            'produksi' => 'Produksi',
            'production' => 'Produksi',
            'saldo_awal' => 'Saldo Awal',
        ];
        
        return $jenisMap[$referenceType] ?? 'Jurnal Umum';
    }
    
    /**
     * Get nomor transaksi berdasarkan reference
     */
    private function getNomorTransaksi($entry)
    {
        if (!$entry) return '-';
        
        $referenceType = $entry->ref_type ?? '';
        $referenceId = $entry->ref_id ?? null;
        
        if (!$referenceId) {
            return 'JU-' . $entry->id;
        }
        
        // Ambil nomor transaksi dari tabel asli
        try {
            switch ($referenceType) {
                case 'sale':
                case 'sale_cogs':
                case 'penjualan':
                    $transaksi = Penjualan::find($referenceId);
                    return $transaksi ? ('PJ-' . $referenceId) : 'PJ-' . $referenceId;
                    
                case 'purchase':
                case 'pembelian':
                    $transaksi = Pembelian::find($referenceId);
                    return $transaksi ? ('PB-' . $referenceId) : 'PB-' . $referenceId;
                    
                case 'expense_payment':
                case 'expense':
                    return 'BP-' . $referenceId;
                    
                case 'pelunasan_utang':
                    $transaksi = PelunasanUtang::find($referenceId);
                    return $transaksi ? ($transaksi->kode_pelunasan ?? 'PU-' . $referenceId) : 'PU-' . $referenceId;
                    
                case 'penggajian':
                    $transaksi = Penggajian::find($referenceId);
                    return $transaksi ? ($transaksi->kode_penggajian ?? 'GJ-' . $referenceId) : 'GJ-' . $referenceId;
                    
                case 'retur':
                    $transaksi = Retur::find($referenceId);
                    return $transaksi ? ($transaksi->kode_retur ?? 'RTR-' . $referenceId) : 'RTR-' . $referenceId;
                    
                case 'produksi':
                    return 'PRD-' . $referenceId;
                    
                case 'ap_settlement':
                    return 'AP-' . $referenceId;
                    
                default:
                    return 'JU-' . $entry->id;
            }
        } catch (\Exception $e) {
            return 'TRX-' . $referenceId;
        }
    }
    
    /**
     * Get jenis transaksi berdasarkan reference_type
     */
    private function getJenisTransaksi($entry)
    {
        if (!$entry) return 'Jurnal Umum';
        
        $referenceType = $entry->ref_type ?? '';
        
        $jenisMap = [
            'sale' => 'Penjualan',
            'sale_cogs' => 'HPP Penjualan',
            'penjualan' => 'Penjualan',
            'purchase' => 'Pembelian',
            'pembelian' => 'Pembelian',
            'expense_payment' => 'Pembayaran Beban',
            'expense' => 'Pembayaran Beban',
            'pelunasan_utang' => 'Pelunasan Utang',
            'ap_settlement' => 'Pelunasan Utang',
            'penggajian' => 'Penggajian',
            'payroll' => 'Penggajian',
            'retur' => 'Retur',
            'produksi' => 'Produksi',
            'production' => 'Produksi',
            'saldo_awal' => 'Saldo Awal',
        ];
        
        return $jenisMap[$referenceType] ?? 'Jurnal Umum';
    }

    /**
     * Export laporan kas bank ke PDF
     */
    public function exportPdf(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));
        
        // Ambil HANYA akun Kas dan Bank menggunakan helper untuk konsistensi
        $akunKasBank = AccountHelper::getKasBankAccounts();
        
        $dataKasBank = [];
        $totalKeseluruhan = 0;
        $totalSaldoAwal = 0;
        $totalTransaksiMasuk = 0;
        $totalTransaksiKeluar = 0;
        
        foreach ($akunKasBank as $akun) {
            $saldoAwal = $this->getSaldoAwal($akun, $startDate);
            $transaksiMasuk = $this->getTransaksiMasuk($akun, $startDate, $endDate);
            $transaksiKeluar = $this->getTransaksiKeluar($akun, $startDate, $endDate);
            $saldoAkhir = $saldoAwal + $transaksiMasuk - $transaksiKeluar;
            
            $dataKasBank[] = [
                'kode_akun' => $akun->kode_akun,
                'nama_akun' => $akun->nama_akun,
                'saldo_awal' => $saldoAwal,
                'transaksi_masuk' => $transaksiMasuk,
                'transaksi_keluar' => $transaksiKeluar,
                'saldo_akhir' => $saldoAkhir
            ];
            
            $totalKeseluruhan += $saldoAkhir;
            $totalSaldoAwal += $saldoAwal;
            $totalTransaksiMasuk += $transaksiMasuk;
            $totalTransaksiKeluar += $transaksiKeluar;
        }
        
        $pdf = Pdf::loadView('laporan.kas-bank.pdf', compact('dataKasBank', 'totalKeseluruhan', 'totalSaldoAwal', 'totalTransaksiMasuk', 'totalTransaksiKeluar', 'startDate', 'endDate'))
            ->setPaper('a4', 'portrait');
        
        return $pdf->download('laporan-kas-bank-'.date('Y-m-d').'.pdf');
    }

    /**
     * Detect duplicate transactions between jurnal_umum systems
     */
    private function detectDuplicateTransactions($akun, $startDate, $endDate, $type)
    {
        $duplicateAmount = 0;
        
        // Get transactions from both systems
        if ($type === 'debit') {
            $journalLines = \DB::table('jurnal_umum as ju')
                ->where('ju.coa_id', $akun->id)
                ->where('ju.user_id', auth()->id()) // MULTI-TENANT: Filter by user_id
                ->where('ju.debit', '>', 0)
                ->whereBetween('ju.tanggal', [$startDate, $endDate])
                ->select('ju.tanggal', 'ju.tipe_referensi', 'ju.referensi', 'ju.debit as amount')
                ->get();
                
            $jurnalUmum = \App\Models\JurnalUmum::where('coa_id', $akun->id)
                ->where('debit', '>', 0)
                ->whereBetween('tanggal', [$startDate, $endDate])
                ->select('tanggal', 'tipe_referensi', 'referensi', 'debit as amount')
                ->get();
        } else {
            $journalLines = \DB::table('jurnal_umum as ju')
                ->where('ju.coa_id', $akun->id)
                ->where('ju.user_id', auth()->id()) // MULTI-TENANT: Filter by user_id
                ->where('ju.kredit', '>', 0)
                ->whereBetween('ju.tanggal', [$startDate, $endDate])
                ->select('ju.tanggal', 'ju.tipe_referensi', 'ju.referensi', 'ju.kredit as amount')
                ->get();
                
            $jurnalUmum = \App\Models\JurnalUmum::where('coa_id', $akun->id)
                ->where('kredit', '>', 0)
                ->whereBetween('tanggal', [$startDate, $endDate])
                ->select('tanggal', 'tipe_referensi', 'referensi', 'kredit as amount')
                ->get();
        }
        
        // Check for duplicates based on date, amount, and transaction type
        foreach ($journalLines as $jl) {
            foreach ($jurnalUmum as $ju) {
                $jlDate = date('Y-m-d', strtotime($jl->tanggal));
                $juDate = date('Y-m-d', strtotime($ju->tanggal));
                
                // Check if same date, same amount, and likely same transaction
                if ($jlDate === $juDate && abs($jl->amount - $ju->amount) < 0.01) {
                    // Additional check: if ref_type matches tipe_referensi
                    $refTypeMatch = (
                        ($jl->tipe_referensi === 'purchase' && $ju->tipe_referensi === 'pembelian') ||
                        ($jl->tipe_referensi === 'sale' && $ju->tipe_referensi === 'sale') ||
                        ($jl->tipe_referensi === 'sale' && $ju->tipe_referensi === 'penjualan') ||
                        ($jl->tipe_referensi === 'expense_payment' && $ju->tipe_referensi === 'pembayaran_beban') ||
                        ($jl->tipe_referensi === 'penggajian' && $ju->tipe_referensi === 'penggajian')
                    );
                    
                    // Also check for penjualan reference match by comparing ref_id with referensi
                    $penjualanMatch = false;
                    if ($jl->tipe_referensi === 'sale' && ($ju->tipe_referensi === 'sale' || $ju->tipe_referensi === 'penjualan')) {
                        // Check if jurnal_umum referensi matches penjualan ID from referensi
                        // Handle both formats: "sale#1" and "SJ-20260421-001"
                        if (preg_match('/sale#(\d+)/', $ju->referensi, $matches)) {
                            $penjualanId = (int)$matches[1];
                            if ($jl->referensi == $penjualanId) {
                                $penjualanMatch = true;
                            }
                        } elseif (preg_match('/SJ-\d+-(\d+)/', $ju->referensi, $matches)) {
                            $penjualanId = (int)$matches[1];
                            if ($jl->referensi == $penjualanId) {
                                $penjualanMatch = true;
                            }
                        }
                    }
                    
                    if ($refTypeMatch || $penjualanMatch) {
                        $duplicateAmount += $jl->amount;
                        break; // Avoid counting the same JL transaction multiple times
                    }
                }
            }
        }
        
        return $duplicateAmount;
    }
}
