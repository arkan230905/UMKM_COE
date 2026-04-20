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
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AkuntansiController extends Controller
{
    /**
     * Hitung saldo akhir berdasarkan tipe akun
     */
    private function hitungSaldoAkhir($saldoAwal, $totalDebit, $totalKredit, $tipeAkun)
    {
        // Normalisasi tipe akun (handle case insensitive)
        $tipeAkun = ucfirst(strtolower($tipeAkun));
        
        if (in_array($tipeAkun, ['Asset', 'Aset', 'Expense', 'Beban', 'Biaya'])) {
            // Akun normal DEBIT
            $saldo = $saldoAwal + $totalDebit - $totalKredit;
        } else {
            // Akun normal KREDIT: Kewajiban, Modal, Pendapatan
            $saldo = $saldoAwal - $totalDebit + $totalKredit;
        }

        return $saldo;
    }

    /**
     * Tentukan posisi debit/kredit untuk neraca saldo
     */
    private function posisiNeracaSaldo($saldo, $tipeAkun)
    {
        // Normalisasi tipe akun
        $tipeAkun = ucfirst(strtolower($tipeAkun));
        
        $debit  = 0;
        $kredit = 0;

        // Jika saldo adalah 0, tidak perlu ditampilkan di debit atau kredit
        if ($saldo == 0) {
            return ['debit' => 0, 'kredit' => 0];
        }

        if ($saldo > 0) {
            if (in_array($tipeAkun, ['Asset', 'Aset', 'Expense', 'Beban', 'Biaya'])) {
                // saldo normalnya di DEBIT
                $debit = $saldo;
            } else {
                // saldo normalnya di KREDIT
                $kredit = $saldo;
            }
        } elseif ($saldo < 0) {
            // kalau minus, pindahkan ke sisi sebaliknya (saldo abnormal)
            $nilai = abs($saldo);

            if (in_array($tipeAkun, ['Asset', 'Aset', 'Expense', 'Beban', 'Biaya'])) {
                $kredit = $nilai;
            } else {
                $debit = $nilai;
            }
        }

        return ['debit' => $debit, 'kredit' => $kredit];
    }

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
        
        // TAMBAHAN: Ambil data dari tabel jurnal_umum (untuk penyusutan dan transaksi lain)
        // Hanya ambil yang tidak ada di journal_entries untuk menghindari duplikasi
        $jurnalUmumQuery = \DB::table('jurnal_umum as ju')
            ->leftJoin('coas', 'coas.id', '=', 'ju.coa_id')
            ->select([
                'ju.id',
                'ju.tanggal',
                'ju.keterangan as memo',
                'ju.referensi',
                'ju.tipe_referensi as ref_type',
                'ju.debit',
                'ju.kredit as credit',
                'coas.kode_akun',
                'coas.nama_akun',
                'coas.tipe_akun'
            ])
            ->where(function($q) {
                $q->where('ju.debit', '>', 0)
                  ->orWhere('ju.kredit', '>', 0);
            })
            // PERBAIKAN: Exclude semua tipe yang sudah ada di journal_entries
            ->whereNotIn('ju.tipe_referensi', [
                'purchase', 'sale', 'retur_pembelian', 'retur_penjualan',
                'production_material', 'production_labor_overhead', 'production_finished',
                'produksi' // Exclude tipe lama dari ProduksiObserver
            ])
            ->orderBy('ju.tanggal','asc')
            ->orderBy('ju.id','asc');
            
        if ($from) { $jurnalUmumQuery->whereDate('ju.tanggal','>=',$from); }
        if ($to)   { $jurnalUmumQuery->whereDate('ju.tanggal','<=',$to); }
        if ($refType) { $jurnalUmumQuery->where('ju.tipe_referensi', $refType); }
        if ($accountCode) { 
            $jurnalUmumQuery->where('coas.kode_akun', $accountCode);
        }
        
        $jurnalUmumResults = $jurnalUmumQuery->get();
        
        // Group jurnal_umum results by date and memo untuk menggabungkan debit/kredit
        $jurnalUmumGrouped = $jurnalUmumResults->groupBy(function($item) {
            return $item->tanggal . '|' . $item->memo;
        });
        
        foreach ($jurnalUmumGrouped as $key => $group) {
            $firstItem = $group->first();
            
            $entry = (object) [
                'id' => 'ju_' . $firstItem->id, // Prefix untuk membedakan dengan journal_entries
                'tanggal' => $firstItem->tanggal,
                'ref_type' => $firstItem->ref_type,
                'ref_id' => null,
                'memo' => $firstItem->memo,
                'lines' => $group->map(function($item) {
                    return (object) [
                        'id' => $item->id,
                        'debit' => $item->debit,
                        'credit' => $item->credit,
                        'memo' => null,
                        'account_code' => $item->kode_akun,
                        'account_name' => $item->nama_akun,
                        'account_type' => $item->tipe_akun,
                        'coa' => (object) [
                            'kode_akun' => $item->kode_akun,
                            'nama_akun' => $item->nama_akun,
                            'tipe_akun' => $item->tipe_akun
                        ]
                    ];
                })
            ];
            $entries->push($entry);
        }
        
        // Sort all entries by date
        $entries = $entries->sortBy('tanggal');
        
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

        // Ambil semua COA yang ada di sistem (distinct by kode_akun)
        $coas = \App\Models\Coa::select('kode_akun', 'nama_akun', 'tipe_akun')
            ->groupBy('kode_akun', 'nama_akun', 'tipe_akun')
            ->orderBy('kode_akun')
            ->get();
        $lines = collect();
        $saldoAwal = 0.0;
        $from = null;
        $to = null;

        if ($accountCode) {
            // Cari COA berdasarkan kode_akun langsung
            $coa = \App\Models\Coa::where('kode_akun', $accountCode)->first();
            
            if (!$coa) {
                return view('akuntansi.buku-besar', compact('coas','accountCode','lines','from','to','saldoAwal','month','year'));
            }
            
            // Get saldo awal dari COA berdasarkan kode_akun
            $saldoAwal = $coa->saldo_awal ?? 0;
            
            // Build query untuk journal entries (from JournalEntry system)
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
                ->where(function($q) {
                    $q->where('jl.debit', '>', 0)
                      ->orWhere('jl.credit', '>', 0);
                })
                ->where('coas.kode_akun', $accountCode)
                ->orderBy('je.tanggal','asc')
                ->orderBy('je.id','asc')
                ->orderBy('jl.id','asc');
            
            // Apply date filter only if both month and year are provided
            if ($month && $year) {
                $from = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-01';
                $to = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-' . date('t', mktime(0, 0, 0, $month, 1, $year));
                
                $query->whereMonth('je.tanggal', $month)
                      ->whereYear('je.tanggal', $year);
            }
            
            $results = $query->get();
            
            // Group results by journal entry
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
            
            // TAMBAHAN: Ambil data dari tabel jurnal_umum (untuk transaksi yang hanya ada di sana)
            // Exclude transactions that already exist in journal_entries to avoid duplicates
            $jurnalUmumQuery = \DB::table('jurnal_umum as ju')
                ->leftJoin('coas', 'coas.id', '=', 'ju.coa_id')
                ->select([
                    'ju.id',
                    'ju.tanggal',
                    'ju.keterangan as memo',
                    'ju.referensi',
                    'ju.tipe_referensi as ref_type',
                    'ju.debit',
                    'ju.kredit as credit',
                    'coas.kode_akun',
                    'coas.nama_akun',
                    'coas.tipe_akun'
                ])
                ->where(function($q) {
                    $q->where('ju.debit', '>', 0)
                      ->orWhere('ju.kredit', '>', 0);
                })
                ->where('coas.kode_akun', $accountCode)
                ->whereNotIn('ju.tipe_referensi', ['purchase', 'sale', 'sales_return', 'debt_payment', 'penggajian']) // Exclude types that exist in journal_entries
                ->orderBy('ju.tanggal','asc')
                ->orderBy('ju.id','asc');
            
            // Apply date filter for jurnal_umum as well
            if ($month && $year) {
                $jurnalUmumQuery->whereMonth('ju.tanggal', $month)
                               ->whereYear('ju.tanggal', $year);
            }
            
            $jurnalUmumResults = $jurnalUmumQuery->get();
            
            // Group jurnal_umum results by date and memo untuk menggabungkan debit/kredit
            $jurnalUmumGrouped = $jurnalUmumResults->groupBy(function($item) {
                return $item->tanggal . '|' . $item->memo;
            });
            
            foreach ($jurnalUmumGrouped as $key => $group) {
                $firstItem = $group->first();
                
                $entry = (object) [
                    'id' => 'ju_' . $firstItem->id, // Prefix untuk membedakan dengan journal_entries
                    'tanggal' => $firstItem->tanggal,
                    'ref_type' => $firstItem->ref_type,
                    'ref_id' => null,
                    'memo' => $firstItem->memo,
                    'lines' => $group->map(function($item) {
                        return (object) [
                            'id' => $item->id,
                            'debit' => $item->debit,
                            'credit' => $item->credit,
                            'memo' => null,
                            'account_code' => $item->kode_akun,
                            'account_name' => $item->nama_akun,
                            'account_type' => $item->tipe_akun,
                            'coa' => (object) [
                                'kode_akun' => $item->kode_akun,
                                'nama_akun' => $item->nama_akun,
                                'tipe_akun' => $item->tipe_akun
                            ]
                        ];
                    })
                ];
                $entries->push($entry);
            }
            
            // Sort all entries by date
            $lines = $entries->sortBy('tanggal');
        }

        return view('akuntansi.buku-besar', compact('coas','accountCode','lines','from','to','saldoAwal','month','year'));
    }

    public function bukuBesarExportExcel(Request $request)
    {
        $from = $request->get('from');
        $to   = $request->get('to');

        $export = new BukuBesarExport($from, $to);
        return $export->download('buku-besar-'.date('Y-m-d').'.csv');
    }

    public function neracaSaldo(Request $request)
    {
        // Get bulan & tahun dari request
        $bulan = $request->get('bulan', date('m'));
        $tahun = $request->get('tahun', date('Y'));

        // Hitung tanggal awal dan akhir bulan
        $from = Carbon::create($tahun, $bulan, 1)->format('Y-m-d');
        $to = Carbon::create($tahun, $bulan, 1)->endOfMonth()->format('Y-m-d');

        // Query neraca saldo dengan logika yang diperbaiki
        // Menghindari duplikasi dengan menggunakan DISTINCT kode_akun
        $neracaSaldoData = DB::select("
            SELECT 
                coa_summary.kode_akun,
                coa_summary.nama_akun,
                coa_summary.tipe_akun,
                coa_summary.kategori_akun,
                coa_summary.saldo_normal,
                coa_summary.saldo_awal,
                
                -- Total mutasi sampai akhir periode dari journal_lines
                COALESCE(jl_data.total_debit, 0) as jl_total_debit,
                COALESCE(jl_data.total_kredit, 0) as jl_total_kredit,
                
                -- Total mutasi sampai akhir periode dari jurnal_umum
                COALESCE(ju_data.total_debit, 0) as ju_total_debit,
                COALESCE(ju_data.total_kredit, 0) as ju_total_kredit,
                
                -- Mutasi periode ini saja dari journal_lines
                COALESCE(jl_periode.mutasi_debit, 0) as jl_mutasi_debit,
                COALESCE(jl_periode.mutasi_kredit, 0) as jl_mutasi_kredit,
                
                -- Mutasi periode ini saja dari jurnal_umum
                COALESCE(ju_periode.mutasi_debit, 0) as ju_mutasi_debit,
                COALESCE(ju_periode.mutasi_kredit, 0) as ju_mutasi_kredit
                
            FROM (
                -- Subquery untuk mendapatkan summary COA tanpa duplikasi
                SELECT 
                    c.kode_akun,
                    MIN(c.nama_akun) as nama_akun,
                    MIN(c.tipe_akun) as tipe_akun,
                    MIN(c.kategori_akun) as kategori_akun,
                    MIN(c.saldo_normal) as saldo_normal,
                    SUM(COALESCE(c.saldo_awal, 0)) as saldo_awal
                FROM coas c
                GROUP BY c.kode_akun
            ) coa_summary
            
            -- LEFT JOIN untuk total mutasi sampai akhir periode dari journal_lines
            LEFT JOIN (
                SELECT 
                    c2.kode_akun,
                    SUM(jl.debit) as total_debit,
                    SUM(jl.credit) as total_kredit
                FROM journal_lines jl
                JOIN journal_entries je ON jl.journal_entry_id = je.id
                JOIN coas c2 ON jl.coa_id = c2.id
                WHERE je.tanggal <= ?
                GROUP BY c2.kode_akun
            ) jl_data ON coa_summary.kode_akun = jl_data.kode_akun
            
            -- LEFT JOIN untuk total mutasi sampai akhir periode dari jurnal_umum
            LEFT JOIN (
                SELECT 
                    c2.kode_akun,
                    SUM(ju.debit) as total_debit,
                    SUM(ju.kredit) as total_kredit
                FROM jurnal_umum ju
                JOIN coas c2 ON ju.coa_id = c2.id
                WHERE ju.tanggal <= ?
                GROUP BY c2.kode_akun
            ) ju_data ON coa_summary.kode_akun = ju_data.kode_akun
            
            -- LEFT JOIN untuk mutasi periode ini saja dari journal_lines
            LEFT JOIN (
                SELECT 
                    c2.kode_akun,
                    SUM(jl.debit) as mutasi_debit,
                    SUM(jl.credit) as mutasi_kredit
                FROM journal_lines jl
                JOIN journal_entries je ON jl.journal_entry_id = je.id
                JOIN coas c2 ON jl.coa_id = c2.id
                WHERE je.tanggal BETWEEN ? AND ?
                GROUP BY c2.kode_akun
            ) jl_periode ON coa_summary.kode_akun = jl_periode.kode_akun
            
            -- LEFT JOIN untuk mutasi periode ini saja dari jurnal_umum
            LEFT JOIN (
                SELECT 
                    c2.kode_akun,
                    SUM(ju.debit) as mutasi_debit,
                    SUM(ju.kredit) as mutasi_kredit
                FROM jurnal_umum ju
                JOIN coas c2 ON ju.coa_id = c2.id
                WHERE ju.tanggal BETWEEN ? AND ?
                GROUP BY c2.kode_akun
            ) ju_periode ON coa_summary.kode_akun = ju_periode.kode_akun
            
            ORDER BY coa_summary.kode_akun
        ", [$to, $to, $from, $to, $from, $to]);

        // Convert to collection dan hitung saldo akhir
        $coas = collect($neracaSaldoData)->map(function($item) {
            return (object) $item;
        });

        // Build totals array dengan perhitungan saldo akhir yang benar
        $totals = [];
        foreach ($coas as $coa) {
            // Ambil saldo awal dari COA (jika ada)
            $saldoAwal = $coa->saldo_awal;
            
            // Total mutasi sampai akhir periode
            $totalDebitSampaiPeriode = $coa->jl_total_debit + $coa->ju_total_debit;
            $totalKreditSampaiPeriode = $coa->jl_total_kredit + $coa->ju_total_kredit;
            
            // Mutasi periode ini (untuk kolom debit/kredit di neraca saldo)
            $mutasiDebitPeriode = $coa->jl_mutasi_debit + $coa->ju_mutasi_debit;
            $mutasiKreditPeriode = $coa->jl_mutasi_kredit + $coa->ju_mutasi_kredit;
            
            // Gunakan saldo_normal dari COA untuk menentukan normal debit/kredit
            // Jika saldo_normal tidak ada, fallback ke tipe_akun
            $saldoNormal = strtolower($coa->saldo_normal ?? '');
            if (empty($saldoNormal)) {
                $isDebitNormal = in_array(strtolower($coa->tipe_akun), ['asset', 'aset', 'expense', 'beban', 'biaya']);
            } else {
                $isDebitNormal = ($saldoNormal === 'debit');
            }
            
            // Hitung saldo akhir sesuai dengan buku besar - SAMA PERSIS seperti di buku besar
            if ($isDebitNormal) {
                // Akun normal DEBIT (Asset, Expense)
                // Saldo Akhir = Saldo Awal + Total Debit - Total Kredit
                $saldoAkhir = $saldoAwal + $totalDebitSampaiPeriode - $totalKreditSampaiPeriode;
            } else {
                // Akun normal KREDIT (Liability, Equity, Revenue)
                // Saldo Akhir = Saldo Awal + Total Kredit - Total Debit
                $saldoAkhir = $saldoAwal + $totalKreditSampaiPeriode - $totalDebitSampaiPeriode;
            }

            // Tentukan penempatan saldo akhir di debit/kredit untuk tampilan neraca saldo
            $posisi = $this->posisiNeracaSaldo($saldoAkhir, $coa->tipe_akun);

            $totals[$coa->kode_akun] = [
                'saldo_awal' => $saldoAwal,
                'debit' => $mutasiDebitPeriode,          // mutasi debit periode ini
                'kredit' => $mutasiKreditPeriode,        // mutasi kredit periode ini
                'saldo_debit' => $posisi['debit'],
                'saldo_kredit' => $posisi['kredit'],
                'saldo_akhir' => $saldoAkhir,
                'saldo_normal' => $saldoNormal,
                'is_debit_normal' => $isDebitNormal
            ];
        }

        return view('akuntansi.neraca-saldo', compact('coas','totals'));
    }

    public function neracaSaldoPdf(Request $request)
    {
        // Get bulan & tahun dari request
        $bulan = $request->get('bulan', date('m'));
        $tahun = $request->get('tahun', date('Y'));

        // Hitung tanggal awal dan akhir bulan
        $from = Carbon::create($tahun, $bulan, 1)->format('Y-m-d');
        $to = Carbon::create($tahun, $bulan, 1)->endOfMonth()->format('Y-m-d');

        // Query neraca saldo dengan logika yang diperbaiki (sama seperti web view)
        // Menghindari duplikasi dengan menggunakan DISTINCT kode_akun
        $neracaSaldoData = DB::select("
            SELECT 
                coa_summary.kode_akun,
                coa_summary.nama_akun,
                coa_summary.tipe_akun,
                coa_summary.kategori_akun,
                coa_summary.saldo_normal,
                coa_summary.saldo_awal,
                
                -- Total mutasi sampai akhir periode dari journal_lines
                COALESCE(jl_data.total_debit, 0) as jl_total_debit,
                COALESCE(jl_data.total_kredit, 0) as jl_total_kredit,
                
                -- Total mutasi sampai akhir periode dari jurnal_umum
                COALESCE(ju_data.total_debit, 0) as ju_total_debit,
                COALESCE(ju_data.total_kredit, 0) as ju_total_kredit,
                
                -- Mutasi periode ini saja dari journal_lines
                COALESCE(jl_periode.mutasi_debit, 0) as jl_mutasi_debit,
                COALESCE(jl_periode.mutasi_kredit, 0) as jl_mutasi_kredit,
                
                -- Mutasi periode ini saja dari jurnal_umum
                COALESCE(ju_periode.mutasi_debit, 0) as ju_mutasi_debit,
                COALESCE(ju_periode.mutasi_kredit, 0) as ju_mutasi_kredit
                
            FROM (
                -- Subquery untuk mendapatkan summary COA tanpa duplikasi
                SELECT 
                    c.kode_akun,
                    MIN(c.nama_akun) as nama_akun,
                    MIN(c.tipe_akun) as tipe_akun,
                    MIN(c.kategori_akun) as kategori_akun,
                    MIN(c.saldo_normal) as saldo_normal,
                    SUM(COALESCE(c.saldo_awal, 0)) as saldo_awal
                FROM coas c
                GROUP BY c.kode_akun
            ) coa_summary
            
            -- LEFT JOIN untuk total mutasi sampai akhir periode dari journal_lines
            LEFT JOIN (
                SELECT 
                    c2.kode_akun,
                    SUM(jl.debit) as total_debit,
                    SUM(jl.credit) as total_kredit
                FROM journal_lines jl
                JOIN journal_entries je ON jl.journal_entry_id = je.id
                JOIN coas c2 ON jl.coa_id = c2.id
                WHERE je.tanggal <= ?
                GROUP BY c2.kode_akun
            ) jl_data ON coa_summary.kode_akun = jl_data.kode_akun
            
            -- LEFT JOIN untuk total mutasi sampai akhir periode dari jurnal_umum
            LEFT JOIN (
                SELECT 
                    c2.kode_akun,
                    SUM(ju.debit) as total_debit,
                    SUM(ju.kredit) as total_kredit
                FROM jurnal_umum ju
                JOIN coas c2 ON ju.coa_id = c2.id
                WHERE ju.tanggal <= ?
                GROUP BY c2.kode_akun
            ) ju_data ON coa_summary.kode_akun = ju_data.kode_akun
            
            -- LEFT JOIN untuk mutasi periode ini saja dari journal_lines
            LEFT JOIN (
                SELECT 
                    c2.kode_akun,
                    SUM(jl.debit) as mutasi_debit,
                    SUM(jl.credit) as mutasi_kredit
                FROM journal_lines jl
                JOIN journal_entries je ON jl.journal_entry_id = je.id
                JOIN coas c2 ON jl.coa_id = c2.id
                WHERE je.tanggal BETWEEN ? AND ?
                GROUP BY c2.kode_akun
            ) jl_periode ON coa_summary.kode_akun = jl_periode.kode_akun
            
            -- LEFT JOIN untuk mutasi periode ini saja dari jurnal_umum
            LEFT JOIN (
                SELECT 
                    c2.kode_akun,
                    SUM(ju.debit) as mutasi_debit,
                    SUM(ju.kredit) as mutasi_kredit
                FROM jurnal_umum ju
                JOIN coas c2 ON ju.coa_id = c2.id
                WHERE ju.tanggal BETWEEN ? AND ?
                GROUP BY c2.kode_akun
            ) ju_periode ON coa_summary.kode_akun = ju_periode.kode_akun
            
            ORDER BY coa_summary.kode_akun
        ", [$to, $to, $from, $to, $from, $to]);

        // Convert to collection dan hitung saldo akhir
        $coas = collect($neracaSaldoData)->map(function($item) {
            return (object) $item;
        });

        // Build totals array dengan perhitungan saldo akhir yang benar
        $totals = [];
        foreach ($coas as $coa) {
            // Ambil saldo awal dari COA (jika ada)
            $saldoAwal = $coa->saldo_awal;
            
            // Total mutasi sampai akhir periode
            $totalDebitSampaiPeriode = $coa->jl_total_debit + $coa->ju_total_debit;
            $totalKreditSampaiPeriode = $coa->jl_total_kredit + $coa->ju_total_kredit;
            
            // Mutasi periode ini (untuk kolom debit/kredit di neraca saldo)
            $mutasiDebitPeriode = $coa->jl_mutasi_debit + $coa->ju_mutasi_debit;
            $mutasiKreditPeriode = $coa->jl_mutasi_kredit + $coa->ju_mutasi_kredit;
            
            // Gunakan saldo_normal dari COA untuk menentukan normal debit/kredit
            // Jika saldo_normal tidak ada, fallback ke tipe_akun
            $saldoNormal = strtolower($coa->saldo_normal ?? '');
            if (empty($saldoNormal)) {
                $isDebitNormal = in_array(strtolower($coa->tipe_akun), ['asset', 'aset', 'expense', 'beban', 'biaya']);
            } else {
                $isDebitNormal = ($saldoNormal === 'debit');
            }
            
            // Hitung saldo akhir sesuai dengan buku besar - SAMA PERSIS seperti di buku besar
            if ($isDebitNormal) {
                // Akun normal DEBIT (Asset, Expense)
                // Saldo Akhir = Saldo Awal + Total Debit - Total Kredit
                $saldoAkhir = $saldoAwal + $totalDebitSampaiPeriode - $totalKreditSampaiPeriode;
            } else {
                // Akun normal KREDIT (Liability, Equity, Revenue)
                // Saldo Akhir = Saldo Awal + Total Kredit - Total Debit
                $saldoAkhir = $saldoAwal + $totalKreditSampaiPeriode - $totalDebitSampaiPeriode;
            }

            // Tentukan penempatan saldo akhir di debit/kredit untuk tampilan neraca saldo
            $posisi = $this->posisiNeracaSaldo($saldoAkhir, $coa->tipe_akun);

            $totals[$coa->kode_akun] = [
                'saldo_awal' => $saldoAwal,
                'debit' => $mutasiDebitPeriode,          // mutasi debit periode ini
                'kredit' => $mutasiKreditPeriode,        // mutasi kredit periode ini
                'saldo_debit' => $posisi['debit'],
                'saldo_kredit' => $posisi['kredit'],
                'saldo_akhir' => $saldoAkhir
            ];
        }

        $pdf = Pdf::loadView('akuntansi.neraca-saldo-pdf', compact('coas','totals','bulan','tahun'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('neraca-saldo-'.$tahun.'-'.$bulan.'.pdf');
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
        $totalHpp = $sum($hppAccounts); // HPP from journal entries (trial balance)
        $totalExpense = $sum($expense);
        
        $labaKotor = $totalRevenue - $totalHpp;
        $labaBersih = $labaKotor - $totalExpense;

        return view('akuntansi.laba-rugi', compact(
            'from','to','totalRevenue','totalHpp','totalExpense',
            'labaKotor','labaBersih','revenue','hppAccounts','expense'
        ));
    }

    public function laporanPosisiKeuangan(Request $request)
    {
        $periode = $request->get('periode', now()->format('Y-m'));
        
        // Parse periode to get bulan and tahun
        $tahun = substr($periode, 0, 4);
        $bulan = substr($periode, 5, 2);
        
        // Get data using helper method
        $data = $this->getLaporanPosisiKeuanganData($bulan, $tahun);
        
        // Add periode to data for view
        $data['periode'] = $periode;
        
        return view('akuntansi.laporan_posisi_keuangan', $data);
    }

    /**
     * Helper method to get Financial Position Report data
     * Used by both web view and PDF export
     */
    private function getLaporanPosisiKeuanganData($bulan, $tahun)
    {
        // Hitung tanggal awal dan akhir bulan (sama seperti neraca saldo)
        $from = Carbon::create($tahun, $bulan, 1)->format('Y-m-d');
        $to = Carbon::create($tahun, $bulan, 1)->endOfMonth()->format('Y-m-d');

        // Use EXACT same query as neracaSaldo method for consistency
        $neracaSaldoData = DB::select("
            SELECT 
                coa_summary.kode_akun,
                coa_summary.nama_akun,
                coa_summary.tipe_akun,
                coa_summary.kategori_akun,
                coa_summary.saldo_normal,
                coa_summary.saldo_awal,
                
                -- Total mutasi sampai akhir periode dari journal_lines
                COALESCE(jl_data.total_debit, 0) as jl_total_debit,
                COALESCE(jl_data.total_kredit, 0) as jl_total_kredit,
                
                -- Total mutasi sampai akhir periode dari jurnal_umum
                COALESCE(ju_data.total_debit, 0) as ju_total_debit,
                COALESCE(ju_data.total_kredit, 0) as ju_total_kredit
                
            FROM (
                -- Subquery untuk mendapatkan summary COA tanpa duplikasi
                SELECT 
                    c.kode_akun,
                    MIN(c.nama_akun) as nama_akun,
                    MIN(c.tipe_akun) as tipe_akun,
                    MIN(c.kategori_akun) as kategori_akun,
                    MIN(c.saldo_normal) as saldo_normal,
                    SUM(COALESCE(c.saldo_awal, 0)) as saldo_awal
                FROM coas c
                GROUP BY c.kode_akun
            ) coa_summary
            
            -- LEFT JOIN untuk total mutasi sampai akhir periode dari journal_lines
            LEFT JOIN (
                SELECT 
                    c2.kode_akun,
                    SUM(jl.debit) as total_debit,
                    SUM(jl.credit) as total_kredit
                FROM journal_lines jl
                JOIN journal_entries je ON jl.journal_entry_id = je.id
                JOIN coas c2 ON jl.coa_id = c2.id
                WHERE je.tanggal <= ?
                GROUP BY c2.kode_akun
            ) jl_data ON coa_summary.kode_akun = jl_data.kode_akun
            
            -- LEFT JOIN untuk total mutasi sampai akhir periode dari jurnal_umum
            LEFT JOIN (
                SELECT 
                    c2.kode_akun,
                    SUM(ju.debit) as total_debit,
                    SUM(ju.kredit) as total_kredit
                FROM jurnal_umum ju
                JOIN coas c2 ON ju.coa_id = c2.id
                WHERE ju.tanggal <= ?
                GROUP BY c2.kode_akun
            ) ju_data ON coa_summary.kode_akun = ju_data.kode_akun
            
            ORDER BY coa_summary.kode_akun
        ", [$to, $to]);

        // Convert to collection and calculate final balances using EXACT same logic as neracaSaldo
        $coas = collect($neracaSaldoData)->map(function($item) {
            return (object) $item;
        });

        // Calculate trial balance data using EXACT same logic as neracaSaldo
        $trialBalanceData = [];
        foreach ($coas as $coa) {
            // Ambil saldo awal dari COA (jika ada)
            $saldoAwal = $coa->saldo_awal;
            
            // Total mutasi sampai akhir periode
            $totalDebitSampaiPeriode = $coa->jl_total_debit + $coa->ju_total_debit;
            $totalKreditSampaiPeriode = $coa->jl_total_kredit + $coa->ju_total_kredit;
            
            // Gunakan saldo_normal dari COA untuk menentukan normal debit/kredit
            // Jika saldo_normal tidak ada, fallback ke tipe_akun
            $saldoNormal = strtolower($coa->saldo_normal ?? '');
            if (empty($saldoNormal)) {
                $isDebitNormal = in_array(strtolower($coa->tipe_akun), ['asset', 'aset', 'expense', 'beban', 'biaya']);
            } else {
                $isDebitNormal = ($saldoNormal === 'debit');
            }
            
            // Hitung saldo akhir sesuai dengan buku besar - SAMA PERSIS seperti di buku besar
            if ($isDebitNormal) {
                // Akun normal DEBIT (Asset, Expense)
                // Saldo Akhir = Saldo Awal + Total Debit - Total Kredit
                $saldoAkhir = $saldoAwal + $totalDebitSampaiPeriode - $totalKreditSampaiPeriode;
            } else {
                // Akun normal KREDIT (Liability, Equity, Revenue)
                // Saldo Akhir = Saldo Awal + Total Kredit - Total Debit
                $saldoAkhir = $saldoAwal + $totalKreditSampaiPeriode - $totalDebitSampaiPeriode;
            }

            $trialBalanceData[$coa->kode_akun] = [
                'coa' => $coa,
                'saldo_akhir' => $saldoAkhir
            ];
        }
        
        // Function to get final balance from trial balance data (TIDAK menghitung ulang)
        $getFinalBalance = function($coa) use ($trialBalanceData) {
            return $trialBalanceData[$coa->kode_akun]['saldo_akhir'] ?? 0;
        };
        
        // Helper function to check if account is a parent (has children)
        $isParentAccount = function($coa) use ($coas) {
            $prefix = $coa->kode_akun;
            return $coas->filter(function($child) use ($prefix) {
                return $child->kode_akun !== $prefix && 
                       str_starts_with($child->kode_akun, $prefix) && 
                       strlen($child->kode_akun) > strlen($prefix);
            })->count() > 0;
        };
        
        // Calculate Profit/Loss from Revenue and Expense accounts (menggunakan saldo akhir dari neraca saldo)
        $totalRevenue = 0;
        $totalExpense = 0;
        
        // Calculate total revenue (dari saldo akhir neraca saldo)
        foreach ($coas as $coa) {
            if (in_array($coa->tipe_akun, ['Revenue', 'revenue', 'Pendapatan'])) {
                $saldoAkhir = $getFinalBalance($coa);
                $totalRevenue += $saldoAkhir;
            }
        }
        
        // Calculate total expense (dari saldo akhir neraca saldo)
        foreach ($coas as $coa) {
            if (in_array($coa->tipe_akun, ['Expense', 'expense', 'Beban', 'Biaya'])) {
                $saldoAkhir = $getFinalBalance($coa);
                // For expense accounts, use the actual balance (positive = expense, negative = contra-expense)
                $totalExpense += $saldoAkhir;
            }
        }
        
        // Calculate Profit/Loss = Revenue - Expense
        $profitLoss = $totalRevenue - $totalExpense;
        
        // Group accounts by category - only show Asset, Liability, and Equity accounts
        $asetLancar = $coas->filter(function($coa) use ($isParentAccount, $getFinalBalance) {
            // Only include Asset accounts (Aktiva → Aset)
            if (!in_array($coa->tipe_akun, ['Asset', 'asset', 'Aktiva'])) return false;
            
            // Only include accounts with non-zero balance (hide zero balances for cleaner display)
            $balance = $getFinalBalance($coa);
            if ($balance == 0) return false;
            
            // Current Assets: Account codes 1xx (all accounts starting with 1)
            // Include both leaf accounts AND parent accounts that have their own balances
            $isCurrentAsset = substr($coa->kode_akun, 0, 1) === '1';
            if (!$isCurrentAsset) return false;
            
            // Include if it's a leaf account OR if it's a parent account with non-zero balance
            return !$isParentAccount($coa) || $balance != 0;
        })->sortBy('kode_akun'); // Sort by account code
        
        $asetTidakLancar = $coas->filter(function($coa) use ($isParentAccount, $getFinalBalance) {
            // Only include Asset accounts (Aktiva → Aset)
            if (!in_array($coa->tipe_akun, ['Asset', 'asset', 'Aktiva'])) return false;
            
            // Only include accounts with non-zero balance (hide zero balances for cleaner display)
            $balance = $getFinalBalance($coa);
            if ($balance == 0) return false;
            
            // Non-Current Assets: Account codes 2xx (all accounts starting with 2)
            $isNonCurrentAsset = substr($coa->kode_akun, 0, 1) === '2';
            if (!$isNonCurrentAsset) return false;
            
            // Include if it's a leaf account OR if it's a parent account with non-zero balance
            return !$isParentAccount($coa) || $balance != 0;
        })->sortBy('kode_akun'); // Sort by account code
        
        $kewajibanPendek = $coas->filter(function($coa) use ($isParentAccount, $getFinalBalance) {
            // Only include leaf accounts (not parent accounts)
            if ($isParentAccount($coa)) return false;
            
            // Only include Liability accounts (Pasiva → Kewajiban)
            if (!in_array($coa->tipe_akun, ['Liability', 'liability', 'Pasiva'])) return false;
            
            // Only include accounts with positive balance for liabilities
            $balance = $getFinalBalance($coa);
            if ($balance <= 0) return false;
            
            // Kewajiban Jangka Pendek: kategori contains "Hutang" (not Jangka Panjang) 
            // OR specific short-term liabilities
            return (stripos($coa->kategori_akun, 'Hutang') !== false &&
                    stripos($coa->kategori_akun, 'Jangka Panjang') === false) ||
                   (stripos($coa->nama_akun, 'Hutang Usaha') !== false) ||
                   (stripos($coa->nama_akun, 'Hutang Pajak') !== false);
        })->sortBy('kode_akun'); // Sort by account code
        
        $kewajibanPanjang = $coas->filter(function($coa) use ($isParentAccount, $getFinalBalance) {
            // Only include leaf accounts (not parent accounts)
            if ($isParentAccount($coa)) return false;
            
            // Only include Liability accounts (Pasiva → Kewajiban)
            if (!in_array($coa->tipe_akun, ['Liability', 'liability', 'Pasiva'])) return false;
            
            // Only include accounts with positive balance for liabilities
            $balance = $getFinalBalance($coa);
            if ($balance <= 0) return false;
            
            // Kewajiban Jangka Panjang: kategori contains "Jangka Panjang" 
            // OR specific long-term liabilities
            return (stripos($coa->kategori_akun, 'Jangka Panjang') !== false) ||
                   (stripos($coa->nama_akun, 'Hutang Bank') !== false) ||
                   (stripos($coa->nama_akun, 'Hutang Jangka Panjang') !== false) ||
                   (stripos($coa->nama_akun, 'Obligasi') !== false);
        })->sortBy('kode_akun'); // Sort by account code
        
        $ekuitas = $coas->filter(function($coa) use ($isParentAccount, $getFinalBalance) {
            // Only include leaf accounts (not parent accounts)
            if ($isParentAccount($coa)) return false;
            
            // Only include Equity accounts (Ekuitas → Ekuitas)
            if (!in_array($coa->tipe_akun, ['Equity', 'equity', 'Modal', 'Ekuitas'])) return false;
            
            // Only include accounts with non-zero balance for cleaner display
            $balance = $getFinalBalance($coa);
            if ($balance == 0) return false;
            
            return true;
        })->sortBy('kode_akun'); // Sort by account code
        
        // Calculate totals for each group using final balances from trial balance
        $totalAsetLancar = $asetLancar->sum(function($coa) use ($getFinalBalance) {
            return $getFinalBalance($coa);
        });
        
        $totalAsetTidakLancar = $asetTidakLancar->sum(function($coa) use ($getFinalBalance) {
            return $getFinalBalance($coa);
        });
        
        $totalKewajibanPendek = $kewajibanPendek->sum(function($coa) use ($getFinalBalance) {
            $balance = $getFinalBalance($coa);
            // For liability accounts, only count positive balances
            return $balance > 0 ? $balance : 0;
        });
        
        $totalKewajibanPanjang = $kewajibanPanjang->sum(function($coa) use ($getFinalBalance) {
            $balance = $getFinalBalance($coa);
            // For liability accounts, only count positive balances
            return $balance > 0 ? $balance : 0;
        });
        
        $totalEkuitas = $ekuitas->sum(function($coa) use ($getFinalBalance) {
            return $getFinalBalance($coa);
        });
        
        // Calculate grand totals
        $totalAset = $totalAsetLancar + $totalAsetTidakLancar;
        $totalKewajiban = $totalKewajibanPendek + $totalKewajibanPanjang;
        
        // Add current period profit/loss to equity for balance sheet equation
        $totalEkuitasWithProfitLoss = $totalEkuitas + $profitLoss;
        $totalKewajibanEkuitas = $totalKewajiban + $totalEkuitasWithProfitLoss;
        
        return compact(
            'asetLancar', 'asetTidakLancar', 
            'kewajibanPendek', 'kewajibanPanjang', 'ekuitas',
            'totalAsetLancar', 'totalAsetTidakLancar',
            'totalKewajibanPendek', 'totalKewajibanPanjang', 'totalEkuitas',
            'totalAset', 'totalKewajiban', 'totalKewajibanEkuitas',
            'getFinalBalance', 'profitLoss', 'totalRevenue', 'totalExpense'
        );
    }

    public function laporanPosisiKeuanganPdf(Request $request)
    {
        // Get bulan & tahun dari request
        $bulan = $request->get('bulan', date('m'));
        $tahun = $request->get('tahun', date('Y'));

        // Get data laporan posisi keuangan menggunakan helper method
        $data = $this->getLaporanPosisiKeuanganData($bulan, $tahun);
        
        // Add bulan and tahun to data
        $data['bulan'] = $bulan;
        $data['tahun'] = $tahun;

        // Generate PDF
        $pdf = Pdf::loadView('akuntansi.laporan-posisi-keuangan-pdf', $data)
            ->setPaper('a4', 'portrait');

        // Dynamic filename
        $fileName = 'laporan-posisi-keuangan-' . $tahun . '-' . $bulan . '.pdf';

        return $pdf->download($fileName);
    }
}
