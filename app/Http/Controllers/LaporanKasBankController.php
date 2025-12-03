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
        
        foreach ($akunKasBank as $akun) {
            $saldoAwal = $this->getSaldoAwal($akun, $startDate);
            $transaksiMasuk = $this->getTransaksiMasuk($akun, $startDate, $endDate);
            $transaksiKeluar = $this->getTransaksiKeluar($akun, $startDate, $endDate);
            
            // Untuk akun Kas & Bank (Aset), saldo normal adalah Debit
            // Saldo Akhir = Saldo Awal + Debit (Masuk) - Kredit (Keluar)
            $saldoAkhir = $saldoAwal + $transaksiMasuk - $transaksiKeluar;
            
            $dataKasBank[] = [
                'id' => $akun->id,
                'kode_akun' => $akun->kode_akun,
                'nama_akun' => $akun->nama_akun,
                'saldo_awal' => $saldoAwal,
                'transaksi_masuk' => $transaksiMasuk,
                'transaksi_keluar' => $transaksiKeluar,
                'saldo_akhir' => $saldoAkhir
            ];
            
            $totalKeseluruhan += $saldoAkhir;
        }
        
        return view('laporan.kas-bank.index', compact(
            'dataKasBank',
            'totalKeseluruhan',
            'startDate',
            'endDate'
        ));
    }
    
    /**
     * Get saldo awal sebelum periode
     * Saldo Awal = Saldo Awal dari COA + Mutasi sebelum periode
     */
    private function getSaldoAwal($akun, $startDate)
    {
        // 1. Ambil saldo awal dari COA (neraca saldo)
        $saldoAwalCoa = $akun->saldo_awal ?? 0;
        
        // 2. Cari account_id yang sesuai dengan kode_akun ini
        $account = DB::table('accounts')
            ->where('code', $akun->kode_akun)
            ->first();
        
        if (!$account) {
            // Jika tidak ada di accounts, return saldo awal COA saja
            return $saldoAwalCoa;
        }
        
        // 3. Hitung mutasi dari journal lines sebelum start date
        $mutasiSebelumPeriode = JournalLine::where('account_id', $account->id)
            ->whereHas('entry', function($query) use ($startDate) {
                $query->where('tanggal', '<', $startDate);
            })
            ->selectRaw('SUM(debit) as total_debit, SUM(credit) as total_credit')
            ->first();
        
        $totalDebit = $mutasiSebelumPeriode->total_debit ?? 0;
        $totalCredit = $mutasiSebelumPeriode->total_credit ?? 0;
        
        // 4. Untuk akun Kas & Bank (Aset), saldo normal adalah Debit
        // Saldo = Saldo Awal + Total Debit - Total Credit
        $saldoAwal = $saldoAwalCoa + $totalDebit - $totalCredit;
        
        return $saldoAwal;
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
        $masuk = JournalLine::where('account_id', $account->id)
            ->whereHas('entry', function($query) use ($startDate, $endDate) {
                $query->whereBetween('tanggal', [$startDate, $endDate]);
            })
            ->sum('debit');
            
        return $masuk ?? 0;
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
        $keluar = JournalLine::where('account_id', $account->id)
            ->whereHas('entry', function($query) use ($startDate, $endDate) {
                $query->whereBetween('tanggal', [$startDate, $endDate]);
            })
            ->sum('credit');
            
        return $keluar ?? 0;
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
        
        $transaksi = JournalLine::where('account_id', $account->id)
            ->where('debit', '>', 0)
            ->whereHas('entry', function($query) use ($startDate, $endDate) {
                $query->whereBetween('tanggal', [$startDate, $endDate]);
            })
            ->with('entry')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($line) {
                $entry = $line->entry;
                if (!$entry) {
                    return null;
                }
                
                return [
                    'tanggal' => $entry->tanggal instanceof \Carbon\Carbon ? $entry->tanggal->format('d/m/Y') : date('d/m/Y', strtotime($entry->tanggal)),
                    'nomor_transaksi' => $this->getNomorTransaksi($entry),
                    'jenis' => $this->getJenisTransaksi($entry),
                    'keterangan' => $entry->memo ?? '-',
                    'nominal' => (float)$line->debit
                ];
            })
            ->filter() // Remove null values
            ->values(); // Re-index array
        
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
        
        $transaksi = JournalLine::where('account_id', $account->id)
            ->where('credit', '>', 0)
            ->whereHas('entry', function($query) use ($startDate, $endDate) {
                $query->whereBetween('tanggal', [$startDate, $endDate]);
            })
            ->with('entry')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($line) {
                $entry = $line->entry;
                if (!$entry) {
                    return null;
                }
                
                return [
                    'tanggal' => $entry->tanggal instanceof \Carbon\Carbon ? $entry->tanggal->format('d/m/Y') : date('d/m/Y', strtotime($entry->tanggal)),
                    'nomor_transaksi' => $this->getNomorTransaksi($entry),
                    'jenis' => $this->getJenisTransaksi($entry),
                    'keterangan' => $entry->memo ?? '-',
                    'nominal' => (float)$line->credit
                ];
            })
            ->filter() // Remove null values
            ->values(); // Re-index array
        
        return response()->json($transaksi);
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
        }
        
        $pdf = Pdf::loadView('laporan.kas-bank.pdf', compact('dataKasBank', 'totalKeseluruhan', 'startDate', 'endDate'))
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
