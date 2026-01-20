<?php

namespace App\Http\Controllers;

use App\Models\Coa;
use App\Models\JournalEntry;
use App\Models\JournalLine;
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
use App\Exports\LaporanKasBankExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Helpers\AccountHelper;

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
        
        // Ambil HANYA akun Kas dan Bank menggunakan helper untuk konsistensi
        $akunKasBank = AccountHelper::getKasBankAccounts();
        
        // Hitung saldo untuk setiap akun
        $dataKasBank = [];
        $totalKeseluruhan = 0;
        $totalSaldoAwal = 0;
        $totalTransaksiMasuk = 0;
        $totalTransaksiKeluar = 0;
        
        foreach ($akunKasBank as $akun) {
            $saldoAwal = $this->getSaldoAwal($akun, $startDate);
            $transaksiMasuk = $this->getTransaksiMasuk($akun, $startDate, $endDate);
            $transaksiKeluar = $this->getTransaksiKeluar($akun, $startDate, $endDate);
            
            // Untuk akun Kas & Bank (Aset), saldo normal adalah Debit
            // Saldo Akhir = Saldo Awal + Debit (Masuk) - Kredit (Keluar)
            $saldoAkhir = $saldoAwal + $transaksiMasuk - $transaksiKeluar;
            
            $dataKasBank[] = [
                'id' => $akun->kode_akun, // Use kode_akun as ID for API calls
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
        
        return view('laporan.kas-bank.index', compact(
            'dataKasBank',
            'totalKeseluruhan',
            'totalSaldoAwal',
            'totalTransaksiMasuk',
            'totalTransaksiKeluar',
            'startDate',
            'endDate'
        ));
    }
    
    /**
     * Get saldo awal sebelum periode - sama dengan logic COA
     * Saldo Awal = Saldo dari CoaPeriodBalance atau saldo awal COA
     */
    private function getSaldoAwal($akun, $startDate)
    {
        // 1. Cari periode yang sesuai dengan start date
        $periode = \App\Models\CoaPeriod::where('periode', date('Y-m', strtotime($startDate)))->first();
        
        if ($periode) {
            // 2. Cek apakah ada saldo periode
            $periodBalance = \App\Models\CoaPeriodBalance::where('kode_akun', $akun->kode_akun)
                ->where('period_id', $periode->id)
                ->first();
            
            if ($periodBalance) {
                return is_numeric($periodBalance->saldo_awal) ? (float) $periodBalance->saldo_awal : 0;
            }
            
            // 3. Jika tidak ada, cek periode sebelumnya
            $previousPeriod = $periode->getPreviousPeriod();
            if ($previousPeriod) {
                $previousBalance = \App\Models\CoaPeriodBalance::where('kode_akun', $akun->kode_akun)
                    ->where('period_id', $previousPeriod->id)
                    ->first();
                
                if ($previousBalance) {
                    return is_numeric($previousBalance->saldo_akhir) ? (float) $previousBalance->saldo_akhir : 0;
                }
            }
        }
        
        // 4. Jika tidak ada periode atau saldo, gunakan saldo awal dari COA
        return is_numeric($akun->saldo_awal) ? (float) ($akun->saldo_awal ?? 0) : 0;
    }
    
    /**
     * Get total transaksi masuk dalam periode (Debit)
     */
    private function getTransaksiMasuk($akun, $startDate, $endDate)
    {
        // Cari account_id yang sesuai
        $account = DB::table('accounts')
            ->where('code', $akun->kode_akun)
            ->first();
        
        if (!$account) {
            return 0;
        }
        
        // Hitung debit (transaksi masuk) dalam periode
        $masuk = DB::table('journal_lines')
            ->join('journal_entries', 'journal_lines.journal_entry_id', '=', 'journal_entries.id')
            ->where('journal_lines.account_id', $account->id)
            ->whereBetween('journal_entries.tanggal', [$startDate, $endDate])
            ->sum('debit');
            
        return is_numeric($masuk) ? (float) $masuk : 0;
    }
    
    /**
     * Get total transaksi keluar dalam periode (Kredit)
     */
    private function getTransaksiKeluar($akun, $startDate, $endDate)
    {
        // Cari account_id yang sesuai
        $account = DB::table('accounts')
            ->where('code', $akun->kode_akun)
            ->first();
        
        if (!$account) {
            return 0;
        }
        
        // Hitung credit (transaksi keluar) dalam periode
        $keluar = DB::table('journal_lines')
            ->join('journal_entries', 'journal_lines.journal_entry_id', '=', 'journal_entries.id')
            ->where('journal_lines.account_id', $account->id)
            ->whereBetween('journal_entries.tanggal', [$startDate, $endDate])
            ->sum('credit');
            
        return is_numeric($keluar) ? (float) $keluar : 0;
    }
    
    /**
     * Get detail transaksi masuk
     */
    public function getDetailMasuk(Request $request, $coaId)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));
        
        // Ambil COA
        $coa = Coa::find($coaId);
        if (!$coa) {
            return response()->json([]);
        }
        
        // Cari account_id yang sesuai
        $account = DB::table('accounts')
            ->where('code', $coa->kode_akun)
            ->first();
        
        if (!$account) {
            return response()->json([]);
        }
        
        $transaksi = DB::table('journal_lines')
            ->join('journal_entries', 'journal_lines.journal_entry_id', '=', 'journal_entries.id')
            ->where('journal_lines.account_id', $account->id)
            ->where('journal_lines.debit', '>', 0)
            ->whereBetween('journal_entries.tanggal', [$startDate, $endDate])
            ->orderBy('journal_entries.tanggal', 'desc')
            ->get()
            ->map(function($line) {
                return [
                    'tanggal' => date('d/m/Y', strtotime($line->tanggal)),
                    'nomor_transaksi' => $this->getNomorTransaksiFromData($line),
                    'jenis' => $this->getJenisTransaksiFromData($line),
                    'keterangan' => $line->memo ?? '-',
                    'nominal' => (float)$line->debit
                ];
            })
            ->filter(function($item) {
                return !is_null($item['nomor_transaksi']);
            })
            ->values();
        
        return response()->json($transaksi);
    }
    
    /**
     * Get detail transaksi keluar
     */
    public function getDetailKeluar(Request $request, $coaId)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));
        
        // Ambil COA
        $coa = Coa::find($coaId);
        if (!$coa) {
            return response()->json([]);
        }
        
        // Cari account_id yang sesuai
        $account = DB::table('accounts')
            ->where('code', $coa->kode_akun)
            ->first();
        
        if (!$account) {
            return response()->json([]);
        }
        
        $transaksi = DB::table('journal_lines')
            ->join('journal_entries', 'journal_lines.journal_entry_id', '=', 'journal_entries.id')
            ->where('journal_lines.account_id', $account->id)
            ->where('journal_lines.credit', '>', 0)
            ->whereBetween('journal_entries.tanggal', [$startDate, $endDate])
            ->orderBy('journal_entries.tanggal', 'desc')
            ->get()
            ->map(function($line) {
                return [
                    'tanggal' => date('d/m/Y', strtotime($line->tanggal)),
                    'nomor_transaksi' => $this->getNomorTransaksiFromData($line),
                    'jenis' => $this->getJenisTransaksiFromData($line),
                    'keterangan' => $line->memo ?? '-',
                    'nominal' => (float)$line->credit
                ];
            })
            ->filter(function($item) {
                return !is_null($item['nomor_transaksi']);
            })
            ->values();
        
        return response()->json($transaksi);
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
     * Export laporan kas bank ke Excel
     */
    public function exportExcel(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));
        
        return Excel::download(
            new LaporanKasBankExport($startDate, $endDate), 
            'laporan-kas-bank-'.date('Y-m-d').'.xlsx'
        );
    }
}
