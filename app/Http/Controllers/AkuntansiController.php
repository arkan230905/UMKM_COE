<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\Coa;
use App\Models\CoaPeriod;
use App\Models\CoaPeriodBalance;
use App\Models\JurnalUmum;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\JurnalUmumExport;
use App\Exports\BukuBesarExport;
use Maatwebsite\Excel\Facades\Excel;

class AkuntansiController extends Controller
{
    public function jurnalUmum(Request $request)
    {
        $from = $request->get('from');
        $to   = $request->get('to');
        $refType = $request->get('ref_type');
        $refId   = $request->get('ref_id');
        $accountCode = $request->get('account_code');

        // Auto-generate journal jika belum ada untuk purchase
        if ($refType === 'purchase' && $refId) {
            $this->ensurePurchaseJournalExists($refId);
        }

        // Gunakan query dengan leftJoin untuk memastikan nama akun selalu diambil
        $query = \DB::table('journal_entries as je')
            ->leftJoin('journal_lines as jl', 'jl.journal_entry_id', '=', 'je.id')
            ->leftJoin('coas', 'coas.id', '=', 'jl.coa_id') // Perbaikan: join berdasarkan coa_id
            ->select([
                'je.*',
                'jl.id as line_id',
                'jl.debit',
                'jl.credit',
                'jl.memo as line_memo',
                'coas.kode_akun',
                'coas.nama_akun',
                'coas.tipe_akun'
            ])
            ->where(function($q) {
                $q->where('jl.debit', '!=', 0)
                  ->orWhere('jl.credit', '!=', 0);
            })
            ->orderBy('je.tanggal','asc')
            ->orderBy('je.id','asc')
            ->orderBy('jl.id','asc');
            
        if ($from) { $query->whereDate('je.tanggal','>=',$from); }
        if ($to)   { $query->whereDate('je.tanggal','<=',$to); }
        if ($refType) { $query->where('je.ref_type', $refType); }
        if ($refId)   { $query->where('je.ref_id', $refId); }
        if ($accountCode) { 
            $query->where('coas.kode_akun', $accountCode);
        }
        
        $results = $query->get();
        
        // Group results by journal entry untuk tampilan per transaksi
        $entries = collect();
        $groupedResults = $results->groupBy('id');
        
        foreach ($groupedResults as $entryId => $lines) {
            $firstLine = $lines->first();
            
            // Skip jika tidak ada lines yang valid
            if ($lines->isEmpty()) continue;
            
            $entry = (object) [
                'id' => $firstLine->id,
                'tanggal' => $firstLine->tanggal,
                'ref_type' => $firstLine->ref_type,
                'ref_id' => $firstLine->ref_id,
                'memo' => $firstLine->memo,
                'lines' => $lines->map(function($line) {
                    return (object) [
                        'id' => $line->line_id,
                        'debit' => $line->debit,
                        'credit' => $line->credit,
                        'memo' => $line->line_memo,
                        'account_code' => $line->kode_akun,
                        'account_name' => $line->nama_akun,
                        'account_type' => $line->tipe_akun,
                        'coa' => (object) [
                            'kode_akun' => $line->kode_akun,
                            'nama_akun' => $line->nama_akun,
                            'tipe_akun' => $line->tipe_akun
                        ]
                    ];
                })
            ];
            $entries->push($entry);
        }
        
        return view('akuntansi.jurnal-umum', compact('entries','from','to','refType','refId','accountCode'));
    }

    /**
     * Ensure purchase journal exists, create if not
     */
    private function ensurePurchaseJournalExists($purchaseId)
    {
        try {
            \Log::info('Ensuring purchase journal exists', ['purchase_id' => $purchaseId]);
            
            // Check if journal already exists
            $existingJournal = \App\Models\JournalEntry::where('ref_type', 'purchase')
                ->where('ref_id', $purchaseId)
                ->first();

            if ($existingJournal) {
                \Log::info('Journal already exists, deleting to recreate with updated logic', [
                    'purchase_id' => $purchaseId, 
                    'journal_id' => $existingJournal->id
                ]);
                
                // Delete existing journal lines first
                \App\Models\JournalLine::where('journal_entry_id', $existingJournal->id)->delete();
                $existingJournal->delete();
            }

            // Get purchase data
            $pembelian = \App\Models\Pembelian::with([
                'vendor',
                'details.bahanBaku',
                'details.bahanPendukung'
            ])->find($purchaseId);

            if (!$pembelian) {
                \Log::warning('Purchase not found for journal generation', ['id' => $purchaseId]);
                return;
            }

            \Log::info('Creating journal for purchase', [
                'purchase_id' => $purchaseId,
                'nomor_pembelian' => $pembelian->nomor_pembelian,
                'total_harga' => $pembelian->total_harga,
                'details_count' => $pembelian->details->count()
            ]);

            // Create journal using observer
            $observer = app(\App\Observers\PembelianObserver::class);
            $observer->created($pembelian);

            \Log::info('Auto-generated journal for purchase', [
                'purchase_id' => $purchaseId,
                'nomor_pembelian' => $pembelian->nomor_pembelian
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to auto-generate purchase journal', [
                'purchase_id' => $purchaseId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    public function jurnalUmumExportPdf(Request $request)
    {
        $from = $request->get('from');
        $to   = $request->get('to');
        $refType = $request->get('ref_type');
        $refId   = $request->get('ref_id');

        // Gunakan query yang sama dengan jurnalUmum untuk konsistensi
        $query = \DB::table('journal_entries as je')
            ->leftJoin('journal_lines as jl', 'jl.journal_entry_id', '=', 'je.id')
            ->leftJoin('coas', 'coas.id', '=', 'jl.coa_id')
            ->select([
                'je.*',
                'jl.id as line_id',
                'jl.debit',
                'jl.credit',
                'jl.memo as line_memo',
                'coas.kode_akun',
                'coas.nama_akun',
                'coas.tipe_akun'
            ])
            ->orderBy('je.tanggal','asc')
            ->orderBy('je.id','asc')
            ->orderBy('jl.id','asc');
            
        if ($from) { $query->whereDate('je.tanggal','>=',$from); }
        if ($to)   { $query->whereDate('je.tanggal','<=',$to); }
        if ($refType) { $query->where('je.ref_type', $refType); }
        if ($refId)   { $query->where('je.ref_id', $refId); }
        
        $results = $query->get();
        
        // Group results by journal entry
        $entries = collect();
        $groupedResults = $results->groupBy('id');
        
        foreach ($groupedResults as $entryId => $lines) {
            $firstLine = $lines->first();
            
            $entry = (object) [
                'id' => $firstLine->id,
                'tanggal' => $firstLine->tanggal,
                'ref_type' => $firstLine->ref_type,
                'ref_id' => $firstLine->ref_id,
                'memo' => $firstLine->memo,
                'lines' => $lines->map(function($line) {
                    return (object) [
                        'id' => $line->line_id,
                        'debit' => $line->debit,
                        'credit' => $line->credit,
                        'memo' => $line->line_memo,
                        'account_code' => $line->kode_akun,
                        'coa' => (object) [
                            'kode_akun' => $line->kode_akun,
                            'nama_akun' => $line->nama_akun ?? 'COA tidak ditemukan',
                            'tipe_akun' => $line->tipe_akun
                        ]
                    ];
                })
            ];
            
            $entries->push($entry);
        }

        $pdf = Pdf::loadView('akuntansi.jurnal-umum-pdf', compact('entries','from','to','refType','refId'))
            ->setPaper('a4', 'landscape');
        
        return $pdf->download('jurnal-umum-'.date('Y-m-d').'.pdf');
    }

    public function jurnalUmumExportExcel(Request $request)
    {
        $from = $request->get('from');
        $to   = $request->get('to');
        $refType = $request->get('ref_type');
        $refId   = $request->get('ref_id');

        $export = new JurnalUmumExport($from, $to, $refType, $refId);
        return $export->download('jurnal-umum-'.date('Y-m-d').'.xlsx');
    }

    public function bukuBesar(Request $request)
    {
        $month = $request->get('month');
        $year = $request->get('year');
        $accountCode = $request->get('account_code'); // Ubah ke account_code (kode_akun)

        // Ambil semua COA yang ada di sistem
        $coas = \App\Models\Coa::all();
        $lines = collect();
        $saldoAwal = 0.0;
        $from = null;
        $to = null;

        if ($accountCode) {
            // Cari COA berdasarkan kode_akun langsung
            $coa = \App\Models\Coa::where('kode_akun', $accountCode)->first();
            
            if (!$coa) {
                return view('akuntansi.buku-besar', compact('coas','accountId','lines','from','to','saldoAwal','month','year'));
            }
            
            // Jika bulan dan tahun dipilih, buat rentang tanggal
            if ($month && $year) {
                $from = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-01';
                $to = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-' . date('t', mktime(0, 0, 0, $month, 1, $year));
                
                // Get saldo awal dari COA berdasarkan kode_akun
                $saldoAwal = $coa->saldo_awal ?? 0;
                
                // Gunakan relasi/logika yang sama dengan jurnal umum
                // Query jurnal umum berdasarkan kode_akun
                $query = \DB::table('journal_entries as je')
                    ->leftJoin('journal_lines as jl', 'jl.journal_entry_id', '=', 'je.id')
                    ->leftJoin('coas', 'coas.id', '=', 'jl.coa_id') // Join berdasarkan coa_id (sama dengan jurnal umum)
                    ->select([
                        'je.*',
                        'jl.id as line_id',
                        'jl.debit',
                        'jl.credit',
                        'jl.memo as line_memo',
                        'coas.kode_akun',
                        'coas.nama_akun',
                        'coas.tipe_akun'
                    ])
                    ->where(function($q) {
                        $q->where('jl.debit', '>', 0)
                          ->orWhere('jl.credit', '>', 0);
                    })
                    ->where('coas.kode_akun', $accountCode) // Filter per kode_akun langsung
                    ->whereMonth('je.tanggal', $month)
                    ->whereYear('je.tanggal', $year)
                    ->orderBy('je.tanggal','asc')
                    ->orderBy('je.id','asc')
                    ->orderBy('jl.id','asc');
                
                $results = $query->get();
                
                // Group results by journal entry (sama seperti jurnal umum)
                $entries = collect();
                $groupedResults = $results->groupBy('id');
                
                foreach ($groupedResults as $entryId => $lines) {
                    $firstLine = $lines->first();
                    
                    if ($lines->isEmpty()) continue;
                    
                    $entry = (object) [
                        'id' => $firstLine->id,
                        'tanggal' => $firstLine->tanggal,
                        'ref_type' => $firstLine->ref_type,
                        'ref_id' => $firstLine->ref_id,
                        'memo' => $firstLine->memo,
                        'lines' => $lines->map(function($line) {
                            return (object) [
                                'id' => $line->line_id,
                                'debit' => $line->debit,
                                'credit' => $line->credit,
                                'memo' => $line->line_memo,
                                'account_code' => $line->kode_akun,
                                'account_name' => $line->nama_akun,
                                'account_type' => $line->tipe_akun,
                                'coa' => (object) [
                                    'kode_akun' => $line->kode_akun,
                                    'nama_akun' => $line->nama_akun,
                                    'tipe_akun' => $line->tipe_akun
                                ]
                            ];
                        })
                    ];
                    $entries->push($entry);
                }
                
                $lines = $entries;
            }
        }

        return view('akuntansi.buku-besar', compact('coas','accountCode','lines','from','to','saldoAwal','month','year'));
    }

    public function bukuBesarExportExcel(Request $request)
    {
        $from = $request->get('from');
        $to   = $request->get('to');

        return Excel::download(
            new BukuBesarExport($from, $to),
            'buku-besar-'.date('Y-m-d').'.xlsx'
        );
    }

    public function neracaSaldo(Request $request)
    {
        // Get periode yang dipilih atau periode saat ini
        $periodId = $request->get('period_id');
        $periode = null;
        
        if ($periodId) {
            $periode = CoaPeriod::find($periodId);
        }
        
        // Jika tidak ada periode dipilih, gunakan periode saat ini
        if (!$periode) {
            $periode = CoaPeriod::getCurrentPeriod();
        }
        
        $from = $periode->tanggal_mulai->format('Y-m-d');
        $to = $periode->tanggal_selesai->format('Y-m-d');

        // Get semua periode untuk dropdown
        $periods = CoaPeriod::orderBy('periode', 'desc')->get();

        // Get semua COA
        $coas = Coa::all();
        
        $totals = [];
        foreach ($coas as $coa) {
            // Get saldo awal dari COA table (proper accounting method)
            $saldoAwal = $coa->saldo_awal ?? 0;
            
            // Hitung mutasi dalam periode menggunakan JournalEntry dan JournalLine (data baru)
            $journalEntryIds = JournalEntry::whereBetween('tanggal', [$from, $to])->pluck('id');
            
            $debit = JournalLine::whereIn('journal_entry_id', $journalEntryIds)
                ->where('coa_id', $coa->id)
                ->sum('debit');
                
            $credit = JournalLine::whereIn('journal_entry_id', $journalEntryIds)
                ->where('coa_id', $coa->id)
                ->sum('credit');
            
            // Hitung saldo akhir dengan metode yang sama seperti buku besar
            // (selalu: saldo_akhir = saldo_awal + debit - credit)
            $saldoAkhir = $saldoAwal + $debit - $credit;
            
            $totals[$coa->kode_akun] = [
                'saldo_awal' => $saldoAwal,
                'debit' => $debit,
                'kredit' => $credit,
                'saldo_akhir' => $saldoAkhir
            ];
        }

        return view('akuntansi.neraca-saldo', compact('periode','periods','coas','totals'));
    }

    /**
     * Get saldo awal untuk periode tertentu
     */
    private function getSaldoAwalPeriode($coa, $periode)
    {
        // Cek apakah ada saldo periode
        $periodBalance = CoaPeriodBalance::where('kode_akun', $coa->kode_akun)
            ->where('period_id', $periode->id)
            ->first();
        
        if ($periodBalance) {
            return $periodBalance->saldo_awal;
        }
        
        // Jika tidak ada, cek periode sebelumnya
        $previousPeriod = $periode->getPreviousPeriod();
        if ($previousPeriod) {
            $previousBalance = CoaPeriodBalance::where('kode_akun', $coa->kode_akun)
                ->where('period_id', $previousPeriod->id)
                ->first();
            
            if ($previousBalance) {
                return $previousBalance->saldo_akhir;
            }
        }
        
        // Jika tidak ada periode sebelumnya, gunakan saldo awal dari COA
        return $coa->saldo_awal ?? 0;
    }

    public function labaRugi(Request $request)
    {
        $from = $request->get('from');
        $to   = $request->get('to');

        // Get accounts by category - hanya pendapatan usaha
        $revenue = \App\Models\Coa::where('tipe_akun','Revenue')
                                  ->where('kategori_akun', 'Pendapatan')
                                  ->get();
        
        // Get HPP accounts from COA (16xx accounts)
        $hppAccounts = \App\Models\Coa::where('tipe_akun','Expense')
                                      ->where(function($query) {
                    $query->where('kode_akun', 'LIKE', '16%')
                           ->orWhere('kode_akun', '161');
                }) // HPP accounts
                                      ->get();
        
        $expense = \App\Models\Coa::where('tipe_akun','Expense')
                                  ->where('kode_akun', 'NOT LIKE', '16%')
                          ->where('kode_akun', '!=', '52') // Non-HPP expenses
                                  ->get();
        
        $sum = function($coas) use ($from,$to) {
            $total = 0.0;
            foreach ($coas as $coa) {
                $q = \App\Models\JournalLine::where('coa_id',$coa->id)->with('entry');
                if ($from) { $q->whereHas('entry', fn($qq)=>$qq->whereDate('tanggal','>=',$from)); }
                if ($to)   { $q->whereHas('entry', fn($qq)=>$qq->whereDate('tanggal','<=',$to)); }
                $row = $q->selectRaw('COALESCE(SUM(debit),0) as d, COALESCE(SUM(credit),0) as c')->first();
                $balance = ($coa->tipe_akun==='Revenue') ? (float)($row->c - $row->d) : (float)($row->d - $row->c);
                $total += $balance;
            }
            return $total;
        };
        
        $totalRevenue = $sum($revenue);
        $totalHpp = $sum($hppAccounts); // HPP from journal entries (neraca saldo)
        $totalExpense = $sum($expense);
        
        $labaKotor = $totalRevenue - $totalHpp;
        $labaBersih = $labaKotor - $totalExpense;

        return view('akuntansi.laba-rugi', compact(
            'from','to','totalRevenue','totalHpp','totalExpense',
            'labaKotor','labaBersih','revenue','hppAccounts','expense'
        ));
    }

    public function neraca(Request $request)
    {
        $periode = $request->get('periode', now()->format('Y-m'));
        
        // Get all COA accounts
        $allCoa = \App\Models\Coa::all();
        
        // Calculate balances for each account from journal entries
        $calculateBalance = function($coa) use ($periode) {
            $saldo = 0;
            
            // Get journal lines for this account up to selected period
            $journalLines = \App\Models\JournalLine::where('coa_id', $coa->id)
                ->whereHas('entry', function($q) use ($periode) {
                    $q->whereDate('tanggal', '<=', $periode . '-31');
                })->get();
            
            foreach ($journalLines as $line) {
                if ($coa->saldo_normal === 'debit') {
                    $saldo += $line->debit - $line->credit;
                } else {
                    $saldo += $line->credit - $line->debit;
                }
            }
            
            // Add initial balance
            $saldo += $coa->saldo_awal ?? 0;
            
            return $saldo;
        };
        
        // Group accounts by category based on COA fields - NO DUPLICATES
                $asetLancar = $allCoa->filter(function($coa) {
            // Aset Lancar: All current assets including inventory, cash, receivables, etc.
            return in_array($coa->tipe_akun, ['Asset', 'asset']) && (
                // Kas & Bank accounts
                stripos($coa->kategori_akun, 'Aset Lancar') !== false || 
                stripos($coa->kategori_akun, 'Kas & Bank') !== false ||
                stripos($coa->nama_akun, 'Kas') !== false ||
                stripos($coa->nama_akun, 'Bank') !== false ||
                
                // Inventory accounts (Persediaan)
                stripos($coa->nama_akun, 'Persediaan') !== false ||
                stripos($coa->nama_akun, 'Pers.') !== false ||
                stripos($coa->nama_akun, 'Barang Jadi') !== false ||
                stripos($coa->nama_akun, 'Barang dalam Proses') !== false ||
                stripos($coa->nama_akun, 'Bahan Baku') !== false ||
                stripos($coa->nama_akun, 'Bahan Pendukung') !== false ||
                
                // Receivables and prepaid
                stripos($coa->nama_akun, 'Piutang') !== false ||
                stripos($coa->nama_akun, 'PPN Masukan') !== false ||
                stripos($coa->nama_akun, 'Biaya Dibayar Dimuka') !== false ||
                
                // Exclude fixed assets
                !(stripos($coa->nama_akun, 'Peralatan') !== false ||
                  stripos($coa->nama_akun, 'Mesin') !== false ||
                  stripos($coa->nama_akun, 'Kendaraan') !== false ||
                  stripos($coa->nama_akun, 'Gedung') !== false ||
                  stripos($coa->nama_akun, 'Tanah') !== false ||
                  stripos($coa->nama_akun, 'Akumulasi Penyusutan') !== false)
            );
        });
        
        $asetTidakLancar = $allCoa->filter(function($coa) {
            // Aset Tidak Lancar: kategori contains "Tidak Lancar" 
            // OR specific account types that are clearly fixed assets
            return (stripos($coa->kategori_akun, 'Tidak Lancar') !== false ||
                   stripos($coa->nama_akun, 'Peralatan') !== false ||
                   stripos($coa->nama_akun, 'Mesin') !== false ||
                   stripos($coa->nama_akun, 'Kendaraan') !== false ||
                   stripos($coa->nama_akun, 'Inventaris') !== false ||
                   stripos($coa->nama_akun, 'Akumulasi Penyusutan') !== false ||
                   stripos($coa->nama_akun, 'Aset Tetap') !== false ||
                   stripos($coa->nama_akun, 'Gedung') !== false ||
                   stripos($coa->nama_akun, 'Tanah') !== false) &&
                   in_array($coa->tipe_akun, ['Asset', 'asset']);
        });
        
        $kewajibanPendek = $allCoa->filter(function($coa) {
            // Kewajiban Jangka Pendek: kategori contains "Hutang" (not Jangka Panjang) 
            // OR specific short-term liabilities
            return (stripos($coa->kategori_akun, 'Hutang') !== false &&
                    stripos($coa->kategori_akun, 'Jangka Panjang') === false) ||
                   (stripos($coa->nama_akun, 'Hutang Usaha') !== false) ||
                   (stripos($coa->nama_akun, 'Hutang Pajak') !== false);
        });
        
        $kewajibanPanjang = $allCoa->filter(function($coa) {
            // Kewajiban Jangka Panjang: kategori contains "Jangka Panjang" 
            // OR specific long-term liabilities (EXCLUDE PPN Masukan - it's an asset)
            return (stripos($coa->kategori_akun, 'Jangka Panjang') !== false) ||
                   (stripos($coa->nama_akun, 'Hutang Bank') !== false) ||
                   (stripos($coa->nama_akun, 'Hutang Jangka Panjang') !== false) ||
                   (stripos($coa->nama_akun, 'Obligasi') !== false);
        });
        
        $ekuitas = $allCoa->filter(function($coa) {
            // Ekuitas: starts with 3xxx or tipe_akun is Equity or kategori contains "Ekuitas"
            // OR specific equity accounts (excluding PPN Keluaran which should be liability)
            return substr($coa->kode_akun, 0, 1) === '3' || 
                   in_array($coa->tipe_akun, ['Equity', 'Modal']) ||
                   stripos($coa->kategori_akun, 'Ekuitas') !== false ||
                   (stripos($coa->nama_akun, 'Modal') !== false) ||
                   (stripos($coa->nama_akun, 'Laba Ditahan') !== false) ||
                   (stripos($coa->nama_akun, 'Prive') !== false);
        });
        
        // Calculate totals for each group
        $totalAsetLancar = $asetLancar->sum(function($coa) use ($calculateBalance) {
            return $calculateBalance($coa);
        });
        
        // Add negative liabilities as assets (overpayments become receivables)
        $negativeLiabilities = 0;
        $kewajibanPendek->each(function($coa) use ($calculateBalance, &$negativeLiabilities) {
            $balance = $calculateBalance($coa);
            if ($balance < 0) {
                $negativeLiabilities += abs($balance); // Convert negative liability to positive asset
            }
        });
        
        $kewajibanPanjang->each(function($coa) use ($calculateBalance, &$negativeLiabilities) {
            $balance = $calculateBalance($coa);
            if ($balance < 0) {
                $negativeLiabilities += abs($balance); // Convert negative liability to positive asset
            }
        });
        
        $totalAsetLancar += $negativeLiabilities;
        
        $totalAsetTidakLancar = $asetTidakLancar->sum(function($coa) use ($calculateBalance) {
            return $calculateBalance($coa);
        });
        
        $totalKewajibanPendek = $kewajibanPendek->sum(function($coa) use ($calculateBalance) {
            $balance = $calculateBalance($coa);
            // For liability accounts, if balance is negative, it should be treated as asset (overpayment)
            // So we only count positive liability balances here
            return $balance > 0 ? $balance : 0;
        });
        
        $totalKewajibanPanjang = $kewajibanPanjang->sum(function($coa) use ($calculateBalance) {
            $balance = $calculateBalance($coa);
            // For liability accounts, negative balance means overpayment - should be 0 in balance sheet
            return $coa->tipe_akun === 'Liability' && $balance < 0 ? 0 : $balance;
        });
        
        $totalEkuitas = $ekuitas->sum(function($coa) use ($calculateBalance) {
            return $calculateBalance($coa);
        });
        
        // Add current period P&L to equity (since we don't have closing entries)
        $currentPeriodPL = 0;
        
        // Calculate P&L from beginning of fiscal year (not just current month)
        // Get the current fiscal year start date
        $fiscalYearStart = now()->startOfYear()->format('Y-m-d');
        
        // Calculate revenue (credit balance) - from fiscal year start
        $revenueAccounts = \App\Models\Coa::where('tipe_akun', 'Revenue')->get();
        foreach ($revenueAccounts as $coa) {
            $journalLines = \App\Models\JournalLine::where('coa_id', $coa->id)
                ->whereHas('entry', function($q) use ($fiscalYearStart, $periode) {
                    $q->whereBetween('tanggal', [$fiscalYearStart, $periode . '-31']);
                })->get();
            
            $debit = $journalLines->sum('debit');
            $credit = $journalLines->sum('credit');
            $currentPeriodPL += ($credit - $debit); // Revenue increases P&L
        }
        
        // Calculate expense (debit balance) - from fiscal year start
        $expenseAccounts = \App\Models\Coa::where('tipe_akun', 'Expense')->get();
        foreach ($expenseAccounts as $coa) {
            $journalLines = \App\Models\JournalLine::where('coa_id', $coa->id)
                ->whereHas('entry', function($q) use ($fiscalYearStart, $periode) {
                    $q->whereBetween('tanggal', [$fiscalYearStart, $periode . '-31']);
                })->get();
            
            $debit = $journalLines->sum('debit');
            $credit = $journalLines->sum('credit');
            $currentPeriodPL -= ($debit - $credit); // Expense decreases P&L
        }
        
        // Add current period P&L to total equity
        $totalEkuitas += $currentPeriodPL;
        
        // Calculate grand totals
        $totalAset = $totalAsetLancar + $totalAsetTidakLancar;
        $totalKewajiban = $totalKewajibanPendek + $totalKewajibanPanjang;
        $totalKewajibanEkuitas = $totalKewajiban + $totalEkuitas;
        
        return view('akuntansi.neraca', compact(
            'periode', 
            'asetLancar', 'asetTidakLancar', 
            'kewajibanPendek', 'kewajibanPanjang', 'ekuitas',
            'totalAsetLancar', 'totalAsetTidakLancar',
            'totalKewajibanPendek', 'totalKewajibanPanjang', 'totalEkuitas',
            'totalAset', 'totalKewajiban', 'totalKewajibanEkuitas',
            'calculateBalance', 'currentPeriodPL', 'negativeLiabilities'
        ));
    }
}
