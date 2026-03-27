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
        
        // 4. Jika tidak ada periode atau saldo, gunakan saldo awal dari COA atau 0 untuk virtual accounts
        if (isset($akun->saldo_awal)) {
            return is_numeric($akun->saldo_awal) ? (float) ($akun->saldo_awal ?? 0) : 0;
        }
        
        return 0;
    }
    
    /**
     * Get total transaksi masuk dalam periode (Debit)
     */
    private function getTransaksiMasuk($akun, $startDate, $endDate)
    {
        $totalMasuk = 0;
        
        // 1. Penjualan (cash/transfer masuk ke kas/bank)
        $penjualanMasuk = DB::table('penjualans')
            ->whereBetween('tanggal', [$startDate, $endDate])
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
            ->sum('total');
            
        $totalMasuk += (float) ($penjualanMasuk ?? 0);
            
        // 2. Pelunasan Utang (pembayaran utang masuk ke kas/bank)
        try {
            $pelunasanUtangMasuk = DB::table('pelunasan_utangs')
                ->whereBetween('tanggal', [$startDate, $endDate])
                ->where(function($query) use ($akun) {
                    $query->where(function($subQuery) use ($akun) {
                        // Jika akun adalah Bank (mengandung kata 'bank') - check this first
                        if (stripos($akun->nama_akun, 'bank') !== false) {
                            $subQuery->where('metode_bayar', 'transfer');
                        }
                        // Jika akun adalah Kas (mengandung kata 'kas' tapi bukan 'bank')
                        elseif (stripos($akun->nama_akun, 'kas') !== false) {
                            $subQuery->where('metode_bayar', 'tunai');
                        }
                    });
                })
                ->sum('dibayar_bersih');
                
            $totalMasuk += (float) ($pelunasanUtangMasuk ?? 0);
        } catch (\Exception $e) {
            // Tabel tidak ada, skip
        }
            
        // 3. Retur Pembelian (uang kembali masuk ke kas/bank)
        try {
            $returPembelianMasuk = DB::table('purchase_returns')
                ->whereBetween('tanggal', [$startDate, $endDate])
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
            // Tabel tidak ada, skip
        }
        
        return $totalMasuk;
    }
    
    /**
     * Get total transaksi keluar dalam periode (Kredit)
     */
    private function getTransaksiKeluar($akun, $startDate, $endDate)
    {
        $totalKeluar = 0;
        
        // Prioritas 1: Ambil dari journal lines (jurnal akuntansi)
        try {
            $accountCode = $this->mapCoaToAccountCode($akun->kode_akun);
            $account = DB::table('accounts')->where('code', $accountCode)->first();
            
            if ($account) {
                $journalKeluar = DB::table('journal_lines')
                    ->join('journal_entries', 'journal_lines.journal_entry_id', '=', 'journal_entries.id')
                    ->where('journal_lines.account_id', $account->id)
                    ->where('journal_lines.credit', '>', 0)
                    ->whereBetween('journal_entries.tanggal', [$startDate, $endDate])
                    ->sum('journal_lines.credit');
                    
                $totalKeluar += (float) ($journalKeluar ?? 0);
            }
        } catch (\Exception $e) {
            // Skip journal errors, fallback to direct transactions
        }
        
        // Prioritas 2: Ambil dari transaksi langsung (jika journal tidak ada)
        // 1. Pembelian (cash/transfer keluar dari kas/bank ke persediaan)
        $pembelianKeluar = DB::table('pembelians')
            ->whereBetween('tanggal', [$startDate, $endDate])
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
            ->sum('total_harga');
            
        $totalKeluar += (float) ($pembelianKeluar ?? 0);
            
        // 2. Pembayaran Beban (cash/transfer keluar dari kas/bank)
        try {
            $bebanKeluar = DB::table('expense_payments')
                ->whereBetween('tanggal', [$startDate, $endDate])
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
                ->sum('jumlah');
                
            $totalKeluar += (float) ($bebanKeluar ?? 0);
        } catch (\Exception $e) {
            // Tabel tidak ada, skip
        }
            
        // 3. Penggajian (cash/transfer keluar dari kas/bank)
        try {
            $penggajianKeluar = DB::table('penggajians')
                ->whereBetween('tanggal', [$startDate, $endDate])
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
                ->sum('total_gaji');
                
            $totalKeluar += (float) ($penggajianKeluar ?? 0);
        } catch (\Exception $e) {
            // Tabel tidak ada, skip
        }
            
        // 4. Retur Penjualan (uang kembali keluar dari kas/bank)
        try {
            $returPenjualanKeluar = DB::table('returns')
                ->whereBetween('tanggal', [$startDate, $endDate])
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
            // Tabel tidak ada, skip
        }
        
        return $totalKeluar;
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
        
        // Try to find COA first, then use coaId as kode_akun directly
        $coa = Coa::find($coaId);
        $kodeAkun = $coa ? $coa->kode_akun : $coaId;
        
        $transaksi = collect();
        
        // 1. Ambil transaksi dari journal_lines (jurnal akuntansi)
        try {
            $accountCode = $this->mapCoaToAccountCode($kodeAkun);
            $account = DB::table('accounts')->where('code', $accountCode)->first();
            
            if ($account) {
                $journalTransaksi = DB::table('journal_lines')
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
                    });
                    
                $transaksi = $transaksi->concat($journalTransaksi);
            }
        } catch (\Exception $e) {
            // Skip journal errors
        }
        
        // 2. Ambil transaksi penjualan langsung (cash/transfer)
        $penjualanTransaksi = DB::table('penjualans')
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->where(function($query) use ($coa) {
                $query->where(function($subQuery) use ($coa) {
                    // Jika akun adalah Kas (mengandung kata 'kas')
                    if ($coa && stripos($coa->nama_akun, 'kas') !== false) {
                        $subQuery->where('payment_method', 'cash');
                    }
                    // Jika akun adalah Bank (mengandung kata 'bank')
                    elseif ($coa && stripos($coa->nama_akun, 'bank') !== false) {
                        $subQuery->where('payment_method', 'transfer');
                    }
                });
            })
            ->orderBy('tanggal', 'desc')
            ->get()
            ->map(function($penjualan) {
                return [
                    'tanggal' => date('d/m/Y', strtotime($penjualan->tanggal)),
                    'nomor_transaksi' => 'PJ-' . date('Y', strtotime($penjualan->tanggal)) . '-' . str_pad($penjualan->id, 3, '0', STR_PAD_LEFT),
                    'jenis' => 'Penjualan',
                    'keterangan' => 'Penjualan ' . ucfirst($penjualan->payment_method),
                    'nominal' => (float)$penjualan->total
                ];
            });
            
        $transaksi = $transaksi->concat($penjualanTransaksi);
        
        // 3. Ambil transaksi pelunasan utang langsung (jika ada)
        try {
            $pelunasanTransaksi = DB::table('pelunasan_utangs')
                ->whereBetween('tanggal', [$startDate, $endDate])
                ->where(function($query) use ($coa) {
                    $query->where(function($subQuery) use ($coa) {
                        // Jika akun adalah Kas (mengandung kata 'kas')
                        if ($coa && stripos($coa->nama_akun, 'kas') !== false) {
                            $subQuery->where('metode_bayar', 'tunai');
                        }
                        // Jika akun adalah Bank (mengandung kata 'bank')
                        elseif ($coa && stripos($coa->nama_akun, 'bank') !== false) {
                            $subQuery->where('metode_bayar', 'transfer');
                        }
                    });
                })
                ->orderBy('tanggal', 'desc')
                ->get()
                ->map(function($pelunasan) {
                    return [
                        'tanggal' => date('d/m/Y', strtotime($pelunasan->tanggal)),
                        'nomor_transaksi' => 'PU-' . date('Y', strtotime($pelunasan->tanggal)) . '-' . str_pad($pelunasan->id, 3, '0', STR_PAD_LEFT),
                        'jenis' => 'Pelunasan Utang',
                        'keterangan' => 'Pelunasan Utang ' . ucfirst($pelunasan->metode_bayar),
                        'nominal' => (float)$pelunasan->dibayar_bersih
                    ];
                });
                
            $transaksi = $transaksi->concat($pelunasanTransaksi);
        } catch (\Exception $e) {
            // Skip pelunasan errors
        }
        
        // Sort by date
        $transaksi = $transaksi->sortByDesc(function($item) {
            return strtotime(str_replace('/', '-', $item['tanggal']));
        })->values();
        
        return response()->json($transaksi);
    }
    
    /**
     * Get detail transaksi keluar
     */
    public function getDetailKeluar(Request $request, $coaId)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));
        
        // Try to find COA first, then use coaId as kode_akun directly
        $coa = Coa::find($coaId);
        $kodeAkun = $coa ? $coa->kode_akun : $coaId;
        
        $transaksi = collect();
        
        // 1. Ambil transaksi dari journal_lines (jurnal akuntansi)
        try {
            $accountCode = $this->mapCoaToAccountCode($kodeAkun);
            $account = DB::table('accounts')->where('code', $accountCode)->first();
            
            if ($account) {
                $journalTransaksi = DB::table('journal_lines')
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
                    });
                    
                $transaksi = $transaksi->concat($journalTransaksi);
            }
        } catch (\Exception $e) {
            // Skip journal errors
        }
        
        // 2. Ambil transaksi pembelian langsung (cash/transfer)
        $pembelianTransaksi = DB::table('pembelians')
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->where(function($query) use ($coa) {
                $query->where(function($subQuery) use ($coa) {
                    // Jika akun adalah Kas (mengandung kata 'kas')
                    if ($coa && stripos($coa->nama_akun, 'kas') !== false) {
                        $subQuery->where('payment_method', 'cash');
                    }
                    // Jika akun adalah Bank (mengandung kata 'bank')
                    elseif ($coa && stripos($coa->nama_akun, 'bank') !== false) {
                        $subQuery->where('payment_method', 'transfer');
                    }
                });
            })
            ->orderBy('tanggal', 'desc')
            ->get()
            ->map(function($pembelian) {
                return [
                    'tanggal' => date('d/m/Y', strtotime($pembelian->tanggal)),
                    'nomor_transaksi' => $pembelian->nomor_pembelian ?? 'PB-' . date('Y', strtotime($pembelian->tanggal)) . '-' . str_pad($pembelian->id, 3, '0', STR_PAD_LEFT),
                    'jenis' => 'Pembelian',
                    'keterangan' => 'Pembelian ' . ucfirst($pembelian->payment_method),
                    'nominal' => (float)$pembelian->total_harga
                ];
            });
            
        $transaksi = $transaksi->concat($pembelianTransaksi);
        
        // 3. Ambil transaksi beban langsung (jika ada)
        try {
            $bebanTransaksi = DB::table('expense_payments')
                ->whereBetween('tanggal', [$startDate, $endDate])
                ->where(function($query) use ($coa) {
                    $query->where(function($subQuery) use ($coa) {
                        // Jika akun adalah Kas (mengandung kata 'kas')
                        if ($coa && stripos($coa->nama_akun, 'kas') !== false) {
                            $subQuery->where('payment_method', 'cash');
                        }
                        // Jika akun adalah Bank (mengandung kata 'bank')
                        elseif ($coa && stripos($coa->nama_akun, 'bank') !== false) {
                            $subQuery->where('payment_method', 'transfer');
                        }
                    });
                })
                ->orderBy('tanggal', 'desc')
                ->get()
                ->map(function($beban) {
                    return [
                        'tanggal' => date('d/m/Y', strtotime($beban->tanggal)),
                        'nomor_transaksi' => 'EXP-' . date('Y', strtotime($beban->tanggal)) . '-' . str_pad($beban->id, 3, '0', STR_PAD_LEFT),
                        'jenis' => 'Beban',
                        'keterangan' => $beban->keterangan ?? 'Pembayaran Beban',
                        'nominal' => (float)$beban->jumlah
                    ];
                });
                
            $transaksi = $transaksi->concat($bebanTransaksi);
        } catch (\Exception $e) {
            // Skip expense errors
        }
        
        // Sort by date
        $transaksi = $transaksi->sortByDesc(function($item) {
            return strtotime(str_replace('/', '-', $item['tanggal']));
        })->values();
        
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
