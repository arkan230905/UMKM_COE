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
        // Gunakan tipe_akun Indonesian values langsung
        // ASET, BEBAN have normal DEBIT balance
        // KEWAJIBAN, MODAL, PENDAPATAN have normal KREDIT balance
        $isDebitNormal = in_array($tipeAkun, ['ASET', 'BEBAN']);

        $debit  = 0;
        $kredit = 0;

        // Jika saldo adalah 0, tidak perlu ditampilkan di debit atau kredit
        if ($saldo == 0) {
            return ['debit' => 0, 'kredit' => 0];
        }

        if ($saldo > 0) {
            if ($isDebitNormal) {
                // saldo normalnya di DEBIT
                $debit = $saldo;
            } else {
                // saldo normalnya di KREDIT
                $kredit = $saldo;
            }
        } elseif ($saldo < 0) {
            // kalau minus, pindahkan ke sisi sebaliknya (saldo abnormal)
            $nilai = abs($saldo);

            if ($isDebitNormal) {
                $kredit = $nilai;
            } else {
                $debit = $nilai;
            }
        }

        return ['debit' => $debit, 'kredit' => $kredit];
    }

    /**
     * Helper function untuk mendapatkan ringkasan akun (sama untuk Buku Besar & Neraca Saldo)
     * @param string $from Tanggal awal periode (Y-m-d)
     * @param string $to Tanggal akhir periode (Y-m-d)
     * @param string|null $kodeAkun Filter by kode_akun (optional, untuk Buku Besar)
     * @return array Array dengan key kode_akun berisi summary per akun
     */
    private function getAccountSummary($from, $to, $kodeAkun = null)
    {
        // Dari journal_lines - join dengan coas untuk dapat kode_akun yang benar
        $queryJournalLines = DB::table('journal_lines as jl')
            ->join('journal_entries as je', 'jl.journal_entry_id', '=', 'je.id')
            ->join('coas', 'coas.id', '=', 'jl.coa_id')
            ->whereBetween('je.tanggal', [$from, $to])
            ->select(
                'coas.kode_akun',
                DB::raw('COALESCE(SUM(jl.debit),0) as total_debit'),
                DB::raw('COALESCE(SUM(jl.credit),0) as total_kredit')
            )
            ->groupBy('coas.kode_akun');

        // Filter by kode_akun jika diberikan
        if ($kodeAkun) {
            $queryJournalLines->where('coas.kode_akun', $kodeAkun);
        }

        $mutasiJournalLines = $queryJournalLines->get();

        // Dari jurnal_umum - join dengan coas untuk dapat kode_akun yang benar
        $queryJurnalUmum = DB::table('jurnal_umum as ju')
            ->join('coas', 'coas.id', '=', 'ju.coa_id')
            ->whereBetween('ju.tanggal', [$from, $to])
            ->select(
                'coas.kode_akun',
                DB::raw('COALESCE(SUM(ju.debit),0) as total_debit'),
                DB::raw('COALESCE(SUM(ju.kredit),0) as total_kredit')
            )
            ->groupBy('coas.kode_akun');

        // Filter by kode_akun jika diberikan
        if ($kodeAkun) {
            $queryJurnalUmum->where('coas.kode_akun', $kodeAkun);
        }

        $mutasiJurnalUmum = $queryJurnalUmum->get();

        // Gabungkan mutasi dari kedua sumber - gunakan kode_akun sebagai key
        $summaryByKodeAkun = [];
        foreach ($mutasiJournalLines as $line) {
            $summaryByKodeAkun[$line->kode_akun] = [
                'total_debit' => $line->total_debit,
                'total_kredit' => $line->total_kredit
            ];
        }
        foreach ($mutasiJurnalUmum as $line) {
            if (isset($summaryByKodeAkun[$line->kode_akun])) {
                $summaryByKodeAkun[$line->kode_akun]['total_debit'] += $line->total_debit;
                $summaryByKodeAkun[$line->kode_akun]['total_kredit'] += $line->total_kredit;
            } else {
                $summaryByKodeAkun[$line->kode_akun] = [
                    'total_debit' => $line->total_debit,
                    'total_kredit' => $line->total_kredit
                ];
            }
        }

        return $summaryByKodeAkun;
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
            ->orderBy('je.created_at','asc')
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
                'created_at' => $firstLine->created_at,
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
                'ju.created_at',
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
                'produksi', // Exclude tipe lama dari ProduksiObserver
                'expense_payment' // Exclude expense_payment karena sudah ada di journal_entries
            ])
            ->orderBy('ju.tanggal','asc')
            ->orderBy('ju.created_at','asc')
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
                'created_at' => $firstItem->created_at,
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
        
        // Sort all entries by date and created_at
        $entries = $entries->sortBy(function($entry) {
            return $entry->tanggal . ' ' . $entry->created_at;
        });
        
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
            ->orderBy('je.created_at','asc')
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
        $accountCode = $request->get('account_code');

        $coas = \App\Models\Coa::select('kode_akun', 'nama_akun', 'tipe_akun')
            ->groupBy('kode_akun', 'nama_akun', 'tipe_akun')
            ->orderBy('kode_akun')
            ->get();
            
        $lines = collect();
        $saldoAwal = 0.0;
        $from = null;
        $to = null;
        $totalDebit = 0;
        $totalKredit = 0;
        $saldoAkhir = 0;

        if ($accountCode) {
            $coa = \App\Models\Coa::where('kode_akun', $accountCode)->first();

            if (!$coa) {
                return view('akuntansi.buku-besar', compact('coas','accountCode','lines','from','to','saldoAwal','month','year','totalDebit','totalKredit','saldoAkhir'));
            }

            // Use same saldo awal logic as neraca saldo
            $saldoAwal = (float)($coa->saldo_awal ?? 0);

            // Simple query for journal entries
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
            
            if ($month && $year) {
                $query->whereMonth('je.tanggal', $month)
                       ->whereYear('je.tanggal', $year);
            }

            $journalLines = $query->get();

            foreach ($journalLines as $line) {
                $lines->push((object) [
                    'tanggal' => $line->tanggal,
                    'keterangan' => $line->memo,
                    'debit' => $line->debit,
                    'kredit' => $line->credit,
                    'saldo' => 0
                ]);
            }

            $totalDebit = $journalLines->sum('debit');
            $totalKredit = $journalLines->sum('credit');
            $saldoAkhir = $saldoAwal + $totalDebit - $totalKredit;
        }

        return view('akuntansi.buku-besar', compact('coas','accountCode','lines','from','to','saldoAwal','month','year','totalDebit','totalKredit','saldoAkhir'));
    }

    public function bukuBesar(Request $request)
    {
        $month = $request->get("month");
        $year = $request->get("year");
        $accountCode = $request->get("account_code");

        // Get all COAs
        $coas = \App\Models\Coa::select("kode_akun", "nama_akun", "tipe_akun")
            ->groupBy("kode_akun", "nama_akun", "tipe_akun")
            ->orderBy("kode_akun")
            ->get();

        $lines = collect();
        $saldoAwal = 0.0;
        $from = null;
        $to = null;
        $totalDebit = 0;
        $totalKredit = 0;
        $saldoAkhir = 0;

        if ($accountCode) {
            $coa = \App\Models\Coa::where("kode_akun", $accountCode)->first();

            if (!$coa) {
                return view("akuntansi.buku-besar", compact("coas","accountCode","lines","from","to","saldoAwal","month","year","totalDebit","totalKredit","saldoAkhir"));
            }

            // Get saldo awal from COA (temporary fix - matches neraca saldo logic)
            $saldoAwal = (float)($coa->saldo_awal ?? 0);

            // Get journal entries
            $query = \DB::table("journal_entries as je")
                ->leftJoin("journal_lines as jl", "jl.journal_entry_id", "=", "je.id")
                ->leftJoin("coas", "coas.id", "=", "jl.coa_id")
                ->select([
                    "je.*",
                    "jl.id as line_id",
                    "jl.debit",
                    "jl.credit",
                    "jl.memo as line_memo",
                    "coas.kode_akun",
                    "coas.nama_akun",
                    "coas.tipe_akun"
                ])
                ->where(function($q) {
                    $q->where("jl.debit", ">", 0)
                      ->orWhere("jl.credit", ">", 0);
                })
                ->where("coas.kode_akun", $accountCode)
                ->orderBy("je.tanggal","asc")
                ->orderBy("je.id","asc")
                ->orderBy("jl.id","asc");
            
            if ($month && $year) {
                $query->whereMonth("je.tanggal", $month)
                       ->whereYear("je.tanggal", $year);
            }

            $journalLines = $query->get();

            foreach ($journalLines as $line) {
                $lines->push((object) [
                    "tanggal" => $line->tanggal,
                    "keterangan" => $line->memo,
                    "debit" => $line->debit,
                    "kredit" => $line->credit,
                    "saldo" => 0
                ]);
            }

            $totalDebit = $journalLines->sum("debit");
            $totalKredit = $journalLines->sum("credit");
            $saldoAkhir = $saldoAwal + $totalDebit - $totalKredit;
        }

        return view("akuntansi.buku-besar", compact("coas","accountCode","lines","from","to","saldoAwal","month","year","totalDebit","totalKredit","saldoAkhir"));


    public function bukuBesar(Request $request)
    {
        $month = $request->get("month");
        $year = $request->get("year");
        $accountCode = $request->get("account_code");

        // Get all COAs
        $coas = \App\Models\Coa::select("kode_akun", "nama_akun", "tipe_akun")
            ->groupBy("kode_akun", "nama_akun", "tipe_akun")
            ->orderBy("kode_akun")
            ->get();

        $lines = collect();
        $saldoAwal = 0.0;
        $from = null;
        $to = null;
        $totalDebit = 0;
        $totalKredit = 0;
        $saldoAkhir = 0;

        if ($accountCode) {
            $coa = \App\Models\Coa::where("kode_akun", $accountCode)->first();

            if (!$coa) {
                return view("akuntansi.buku-besar", compact("coas","accountCode","lines","from","to","saldoAwal","month","year","totalDebit","totalKredit","saldoAkhir"));
            }

            // Get saldo awal from COA (temporary fix)
            $saldoAwal = (float)($coa->saldo_awal ?? 0);

            // Get journal entries
            $query = \DB::table("journal_entries as je")
                ->leftJoin("journal_lines as jl", "jl.journal_entry_id", "=", "je.id")
                ->leftJoin("coas", "coas.id", "=", "jl.coa_id")
                ->select([
                    "je.*",
                    "jl.id as line_id",
                    "jl.debit",
                    "jl.credit",
                    "jl.memo as line_memo",
                    "coas.kode_akun",
                    "coas.nama_akun",
                    "coas.tipe_akun"
                ])
                ->where(function($q) {
                    $q->where("jl.debit", ">", 0)
                      ->orWhere("jl.credit", ">", 0);
                })
                ->where("coas.kode_akun", $accountCode)
                ->orderBy("je.tanggal","asc")
                ->orderBy("je.id","asc")
                ->orderBy("jl.id","asc");
            
            if ($month && $year) {
                $query->whereMonth("je.tanggal", $month)
                       ->whereYear("je.tanggal", $year);
            }

            $journalLines = $query->get();

            foreach ($journalLines as $line) {
                $lines->push((object) [
                    "tanggal" => $line->tanggal,
                    "keterangan" => $line->memo,
                    "debit" => $line->debit,
                    "kredit" => $line->credit,
                    "saldo" => 0
                ]);
            }

            $totalDebit = $journalLines->sum("debit");
            $totalKredit = $journalLines->sum("credit");
            $saldoAkhir = $saldoAwal + $totalDebit - $totalKredit;
        }

        return view("akuntansi.buku-besar", compact("coas","accountCode","lines","from","to","saldoAwal","month","year","totalDebit","totalKredit","saldoAkhir"));
    }    }
}