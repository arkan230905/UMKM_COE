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
            ->whereNotIn('ju.tipe_referensi', ['purchase', 'sale', 'retur_pembelian', 'retur_penjualan']) // Exclude yang sudah di journal_entries
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

        // Get semua COA tanpa global scope
        $coas = Coa::withoutGlobalScopes()->orderBy('kode_akun')->get()->unique('kode_akun');

        // ==========================================
        // 1. HITUNG SALDO AWAL (transaksi SEBELUM periode ini)
        // ==========================================

        // Dari journal_lines - join dengan coas untuk dapat kode_akun yang benar
        $saldoAwalJournalLines = DB::table('journal_lines as jl')
            ->join('journal_entries as je', 'jl.journal_entry_id', '=', 'je.id')
            ->join('coas', 'coas.id', '=', 'jl.coa_id')
            ->whereDate('je.tanggal', '<', $from)
            ->select(
                'coas.kode_akun',
                DB::raw('COALESCE(SUM(jl.debit),0) as total_debit'),
                DB::raw('COALESCE(SUM(jl.credit),0) as total_kredit')
            )
            ->groupBy('coas.kode_akun')
            ->get();
        
        // Dari jurnal_umum - join dengan coas untuk dapat kode_akun yang benar
        $saldoAwalJurnalUmum = DB::table('jurnal_umum as ju')
            ->join('coas', 'coas.id', '=', 'ju.coa_id')
            ->whereDate('ju.tanggal', '<', $from)
            ->select(
                'coas.kode_akun',
                DB::raw('COALESCE(SUM(ju.debit),0) as total_debit'),
                DB::raw('COALESCE(SUM(ju.kredit),0) as total_kredit')
            )
            ->groupBy('coas.kode_akun')
            ->get();

        // Gabungkan saldo awal dari kedua sumber - gunakan kode_akun sebagai key
        $saldoAwalByKodeAkun = [];
        foreach ($saldoAwalJournalLines as $line) {
            $saldoAwalByKodeAkun[$line->kode_akun] = [
                'total_debit' => $line->total_debit,
                'total_kredit' => $line->total_kredit
            ];
        }
        foreach ($saldoAwalJurnalUmum as $line) {
            if (isset($saldoAwalByKodeAkun[$line->kode_akun])) {
                $saldoAwalByKodeAkun[$line->kode_akun]['total_debit'] += $line->total_debit;
                $saldoAwalByKodeAkun[$line->kode_akun]['total_kredit'] += $line->total_kredit;
            } else {
                $saldoAwalByKodeAkun[$line->kode_akun] = [
                    'total_debit' => $line->total_debit,
                    'total_kredit' => $line->total_kredit
                ];
            }
        }

        // ==========================================
        // 2. HITUNG MUTASI PERIODE INI
        // ==========================================

        // Dari journal_lines - join dengan coas untuk dapat kode_akun yang benar
        $mutasiJournalLines = DB::table('journal_lines as jl')
            ->join('journal_entries as je', 'jl.journal_entry_id', '=', 'je.id')
            ->join('coas', 'coas.id', '=', 'jl.coa_id')
            ->whereBetween('je.tanggal', [$from, $to])
            ->select(
                'coas.kode_akun',
                DB::raw('COALESCE(SUM(jl.debit),0) as total_debit'),
                DB::raw('COALESCE(SUM(jl.credit),0) as total_kredit')
            )
            ->groupBy('coas.kode_akun')
            ->get();

        // Dari jurnal_umum - join dengan coas untuk dapat kode_akun yang benar
        $mutasiJurnalUmum = DB::table('jurnal_umum as ju')
            ->join('coas', 'coas.id', '=', 'ju.coa_id')
            ->whereBetween('ju.tanggal', [$from, $to])
            ->select(
                'coas.kode_akun',
                DB::raw('COALESCE(SUM(ju.debit),0) as total_debit'),
                DB::raw('COALESCE(SUM(ju.kredit),0) as total_kredit')
            )
            ->groupBy('coas.kode_akun')
            ->get();

        // Gabungkan mutasi periode dari kedua sumber - gunakan kode_akun sebagai key
        $mutasiByKodeAkun = [];
        foreach ($mutasiJournalLines as $line) {
            $mutasiByKodeAkun[$line->kode_akun] = [
                'total_debit' => $line->total_debit,
                'total_kredit' => $line->total_kredit
            ];
        }
        foreach ($mutasiJurnalUmum as $line) {
            if (isset($mutasiByKodeAkun[$line->kode_akun])) {
                $mutasiByKodeAkun[$line->kode_akun]['total_debit'] += $line->total_debit;
                $mutasiByKodeAkun[$line->kode_akun]['total_kredit'] += $line->total_kredit;
            } else {
                $mutasiByKodeAkun[$line->kode_akun] = [
                    'total_debit' => $line->total_debit,
                    'total_kredit' => $line->total_kredit
                ];
            }
        }

        // ==========================================
        // 3. BUILD TOTALS ARRAY
        // ==========================================
        $totals = [];
        foreach ($coas as $coa) {
            // Determine normal balance
            $tipeNormal = strtolower($coa->saldo_normal ?? $coa->tipe_akun);
            $isDebitNormal = in_array($tipeNormal, ['debit', 'asset', 'aset', 'expense', 'beban', 'biaya']);

            // Hitung saldo awal dari transaksi sebelum periode - gunakan kode_akun
            $saldoAwalDebit  = $saldoAwalByKodeAkun[$coa->kode_akun]['total_debit']  ?? 0;
            $saldoAwalKredit = $saldoAwalByKodeAkun[$coa->kode_akun]['total_kredit'] ?? 0;

            if ($isDebitNormal) {
                $saldoAwal = $saldoAwalDebit - $saldoAwalKredit;
            } else {
                $saldoAwal = $saldoAwalKredit - $saldoAwalDebit;
            }

            // Mutasi periode ini - gunakan kode_akun
            $totalDebit  = $mutasiByKodeAkun[$coa->kode_akun]['total_debit']  ?? 0;
            $totalKredit = $mutasiByKodeAkun[$coa->kode_akun]['total_kredit'] ?? 0;

            // Hitung saldo akhir
            if ($isDebitNormal) {
                $saldoAkhir = $saldoAwal + $totalDebit - $totalKredit;
            } else {
                $saldoAkhir = $saldoAwal - $totalDebit + $totalKredit;
            }

            // Tentukan penempatan saldo akhir di debit/kredit
            $posisi = $this->posisiNeracaSaldo($saldoAkhir, $coa->tipe_akun);

            $totals[$coa->kode_akun] = [
                'saldo_awal' => $saldoAwal,
                'debit' => $totalDebit,
                'kredit' => $totalKredit,
                'saldo_debit' => $posisi['debit'],
                'saldo_kredit' => $posisi['kredit'],
                'saldo_akhir' => $saldoAkhir
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

        // Get semua COA tanpa global scope (untuk menghindari filter company_id)
        // Gunakan unique() untuk memastikan tiap kode_akun hanya muncul sekali
        $coas = Coa::withoutGlobalScopes()->orderBy('kode_akun')->get()->unique('kode_akun');

        // Subquery untuk agregasi mutasi per akun dari journal_lines - join dengan coas
        $mutasiJournalLines = DB::table('journal_lines as jl')
            ->join('journal_entries as je', 'jl.journal_entry_id', '=', 'je.id')
            ->join('coas', 'coas.id', '=', 'jl.coa_id')
            ->whereBetween('je.tanggal', [$from, $to])
            ->select(
                'coas.kode_akun',
                DB::raw('COALESCE(SUM(jl.debit),0) as total_debit'),
                DB::raw('COALESCE(SUM(jl.credit),0) as total_kredit')
            )
            ->groupBy('coas.kode_akun')
            ->get();

        // Subquery untuk agregasi mutasi dari jurnal_umum - join dengan coas
        $mutasiJurnalUmum = DB::table('jurnal_umum as ju')
            ->join('coas', 'coas.id', '=', 'ju.coa_id')
            ->whereBetween('ju.tanggal', [$from, $to])
            ->select(
                'coas.kode_akun',
                DB::raw('COALESCE(SUM(ju.debit),0) as total_debit'),
                DB::raw('COALESCE(SUM(ju.kredit),0) as total_kredit')
            )
            ->groupBy('coas.kode_akun')
            ->get();

        // Gabungkan kedua sumber data - gunakan kode_akun sebagai key
        $mutasiByKodeAkun = [];
        
        // Proses data dari journal_lines
        foreach ($mutasiJournalLines as $line) {
            $mutasiByKodeAkun[$line->kode_akun] = [
                'total_debit' => $line->total_debit,
                'total_kredit' => $line->total_kredit
            ];
        }
        
        // Proses data dari jurnal_umum dan gabungkan dengan yang sudah ada
        foreach ($mutasiJurnalUmum as $line) {
            if (isset($mutasiByKodeAkun[$line->kode_akun])) {
                $mutasiByKodeAkun[$line->kode_akun]['total_debit'] += $line->total_debit;
                $mutasiByKodeAkun[$line->kode_akun]['total_kredit'] += $line->total_kredit;
            } else {
                $mutasiByKodeAkun[$line->kode_akun] = [
                    'total_debit' => $line->total_debit,
                    'total_kredit' => $line->total_kredit
                ];
            }
        }

        // Build totals array - SATU BARIS per akun
        $totals = [];
        foreach ($coas as $coa) {
            // Lookup mutasi menggunakan kode_akun
            $totalDebit  = $mutasiByKodeAkun[$coa->kode_akun]['total_debit']  ?? 0;
            $totalKredit = $mutasiByKodeAkun[$coa->kode_akun]['total_kredit'] ?? 0;
            $saldoAwal   = $coa->saldo_awal ?? 0;

            // Hitung saldo akhir menggunakan helper function
            $saldoAkhir = $this->hitungSaldoAkhir(
                $saldoAwal,
                $totalDebit,
                $totalKredit,
                $coa->tipe_akun
            );

            // Tentukan penempatan saldo akhir di debit/kredit
            $posisi = $this->posisiNeracaSaldo($saldoAkhir, $coa->tipe_akun);

            // SATU entry per kode_akun - tidak ada duplikasi
            $totals[$coa->kode_akun] = [
                'saldo_awal' => $saldoAwal,
                'debit' => $totalDebit,
                'kredit' => $totalKredit,
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

        // Get semua COA (sama seperti neraca saldo)
        $coas = Coa::all();
        
        // Hitung total debit/kredit per akun dari journal_lines (sama seperti neraca saldo)
        $mutasi = [];
        $journalEntryIds = JournalEntry::whereBetween('tanggal', [$from, $to])->pluck('id');
        
        $journalLines = JournalLine::whereIn('journal_entry_id', $journalEntryIds)
            ->select('coa_id', DB::raw('COALESCE(SUM(debit),0) as total_debit'), DB::raw('COALESCE(SUM(credit),0) as total_kredit'))
            ->groupBy('coa_id')
            ->get();
        
        // Convert to array for easier lookup
        foreach ($journalLines as $line) {
            $mutasi[$line->coa_id] = [
                'total_debit' => $line->total_debit,
                'total_kredit' => $line->total_kredit
            ];
        }

        // Calculate trial balance data (sama seperti neraca saldo)
        $trialBalanceData = [];
        foreach ($coas as $coa) {
            $totalDebit  = $mutasi[$coa->id]['total_debit']  ?? 0;
            $totalKredit = $mutasi[$coa->id]['total_kredit'] ?? 0;
            $saldoAwal   = $coa->saldo_awal ?? 0;

            // Hitung saldo akhir menggunakan helper function (sama seperti neraca saldo)
            $saldoAkhir = $this->hitungSaldoAkhir(
                $saldoAwal,
                $totalDebit,
                $totalKredit,
                $coa->tipe_akun
            );

            $trialBalanceData[$coa->id] = [
                'coa' => $coa,
                'saldo_akhir' => $saldoAkhir
            ];
        }
        
        // Function to get final balance from trial balance data (TIDAK menghitung ulang)
        $getFinalBalance = function($coa) use ($trialBalanceData) {
            return $trialBalanceData[$coa->id]['saldo_akhir'] ?? 0;
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
                $totalExpense += $saldoAkhir;
            }
        }
        
        // Calculate Profit/Loss = Revenue - Expense
        $profitLoss = $totalRevenue - $totalExpense;
        
        // Group accounts by category - only show Asset, Liability, and Equity accounts
        $asetLancar = $coas->filter(function($coa) use ($isParentAccount, $getFinalBalance) {
            // Only include leaf accounts (not parent accounts)
            if ($isParentAccount($coa)) return false;
            
            // Only include Asset accounts (Aktiva → Aset)
            if (!in_array($coa->tipe_akun, ['Asset', 'asset', 'Aktiva'])) return false;
            
            // Only include accounts with non-zero balance (hide zero balances for cleaner display)
            $balance = $getFinalBalance($coa);
            if ($balance == 0) return false;
            
            // Current Assets: Based on account code (1xx) and specific categories
            return (
                // Account code 1xx (current assets)
                substr($coa->kode_akun, 0, 1) === '1' ||
                // Or specific categories
                stripos($coa->kategori_akun, 'Aset Lancar') !== false || 
                stripos($coa->kategori_akun, 'Kas & Bank') !== false ||
                stripos($coa->kategori_akun, 'Persediaan') !== false ||
                stripos($coa->kategori_akun, 'Piutang') !== false ||
                // Or specific account names
                stripos($coa->nama_akun, 'Kas') !== false ||
                stripos($coa->nama_akun, 'Bank') !== false ||
                stripos($coa->nama_akun, 'Persediaan') !== false ||
                stripos($coa->nama_akun, 'Barang Dalam Proses') !== false ||
                stripos($coa->nama_akun, 'WIP') !== false ||
                stripos($coa->nama_akun, 'Piutang') !== false ||
                stripos($coa->nama_akun, 'PPN Masukan') !== false ||
                stripos($coa->nama_akun, 'Biaya Dibayar Dimuka') !== false
            ) && 
            // Exclude fixed assets (2xx codes and specific fixed asset names)
            !(substr($coa->kode_akun, 0, 1) === '2' ||
              stripos($coa->nama_akun, 'Peralatan') !== false ||
              stripos($coa->nama_akun, 'Mesin') !== false ||
              stripos($coa->nama_akun, 'Kendaraan') !== false ||
              stripos($coa->nama_akun, 'Inventaris') !== false ||
              stripos($coa->nama_akun, 'Akumulasi Penyusutan') !== false ||
              stripos($coa->nama_akun, 'Aset Tetap') !== false ||
              stripos($coa->nama_akun, 'Gedung') !== false ||
              stripos($coa->nama_akun, 'Tanah') !== false);
        })->sortBy('kode_akun'); // Sort by account code
        
        $asetTidakLancar = $coas->filter(function($coa) use ($isParentAccount, $getFinalBalance) {
            // Only include leaf accounts (not parent accounts)
            if ($isParentAccount($coa)) return false;
            
            // Only include Asset accounts (Aktiva → Aset)
            if (!in_array($coa->tipe_akun, ['Asset', 'asset', 'Aktiva'])) return false;
            
            // Only include accounts with non-zero balance (hide zero balances for cleaner display)
            $balance = $getFinalBalance($coa);
            if ($balance == 0) return false;
            
            // Non-Current Assets: Based on account code (2xx) and specific categories
            return (
                // Account code 2xx (fixed assets)
                substr($coa->kode_akun, 0, 1) === '2' ||
                // Or specific categories
                stripos($coa->kategori_akun, 'Tidak Lancar') !== false ||
                stripos($coa->kategori_akun, 'Aset Tetap') !== false ||
                // Or specific account names
                stripos($coa->nama_akun, 'Peralatan') !== false ||
                stripos($coa->nama_akun, 'Mesin') !== false ||
                stripos($coa->nama_akun, 'Kendaraan') !== false ||
                stripos($coa->nama_akun, 'Inventaris') !== false ||
                stripos($coa->nama_akun, 'Akumulasi Penyusutan') !== false ||
                stripos($coa->nama_akun, 'Aset Tetap') !== false ||
                stripos($coa->nama_akun, 'Gedung') !== false ||
                stripos($coa->nama_akun, 'Tanah') !== false
            );
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
        
        // Add Profit/Loss to Equity (Laba Rugi Berjalan)
        $totalEkuitas += $profitLoss;
        
        // Calculate grand totals
        $totalAset = $totalAsetLancar + $totalAsetTidakLancar;
        $totalKewajiban = $totalKewajibanPendek + $totalKewajibanPanjang;
        $totalKewajibanEkuitas = $totalKewajiban + $totalEkuitas;
        
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
