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
        // PERBAIKAN: Exclude tipe yang sudah ada di journal_entries untuk menghindari duplikasi
        $queryJurnalUmum = DB::table('jurnal_umum as ju')
            ->join('coas', 'coas.id', '=', 'ju.coa_id')
            ->whereBetween('ju.tanggal', [$from, $to])
            ->whereNotIn('ju.tipe_referensi', [
                'purchase', 'sale', 'retur_pembelian', 'retur_penjualan',
                'production_material', 'production_labor_overhead', 'production_finished',
                'produksi',
                'expense_payment' // Exclude expense_payment karena sudah ada di journal_entries
            ])
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

        // Auto-set date filter for purchase transactions
        if ($refType === 'purchase' && $refId && !$from && !$to) {
            $pembelian = \App\Models\Pembelian::find($refId);
            if ($pembelian) {
                // Set filter tanggal berdasarkan tanggal pembelian
                $tanggalPembelian = \Carbon\Carbon::parse($pembelian->tanggal);
                $from = $tanggalPembelian->format('Y-m-d');
                $to = $tanggalPembelian->format('Y-m-d');
                
                \Log::info('Auto-set date filter for purchase journal', [
                    'purchase_id' => $refId,
                    'purchase_date' => $tanggalPembelian->format('Y-m-d'),
                    'from' => $from,
                    'to' => $to
                ]);
            }
        }

        // Auto-generate journal jika belum ada untuk purchase
        if ($refType === 'purchase' && $refId) {
            $this->ensurePurchaseJournalExists($refId);
        }

        // Gunakan query dengan leftJoin untuk memastikan nama akun selalu diambil
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
                $q->where('jl.debit', '!=', 0)
                  ->orWhere('jl.credit', '!=', 0);
            })
            ->where('coas.user_id', auth()->id())
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
        
        // TAMBAHAN: Ambil data dari tabel jurnal_umum (untuk penyusutan, pembelian, dan transaksi lain)
        // Ambil semua transaksi dari jurnal_umum KECUALI yang sudah ada di journal_entries
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
            // PERBAIKAN: Exclude pembelian transactions to avoid duplicates with journal_entries
            ->whereIn('ju.tipe_referensi', [
                'penyusutan', 'adjustment', 'manual' // Only manual entries, exclude 'pembelian'
            ])
            ->where('coas.user_id', auth()->id())
            // PENTING: Exclude production entries that are already in journal_entries
            // to avoid duplication
            ->whereNotIn('ju.tipe_referensi', [
                'production_material',
                'production_labor_overhead', 
                'production_bop',
                'production_finish'
            ])
            ->orderBy('ju.tanggal','asc')
            ->orderBy('ju.created_at','asc')
            ->orderBy('ju.id','asc');
            
        if ($from) { $jurnalUmumQuery->whereDate('ju.tanggal','>=',$from); }
        if ($to)   { $jurnalUmumQuery->whereDate('ju.tanggal','<=',$to); }
        
        // Handle ref_type filtering
        if ($refType) {
            // Map ref_type from URL parameter to database value
            $mappedRefType = $refType;
            if ($refType === 'purchase') {
                $mappedRefType = 'pembelian';
            } elseif ($refType === 'sale') {
                $mappedRefType = 'penjualan';
            }
            
            $jurnalUmumQuery->where('ju.tipe_referensi', $mappedRefType);
        }
        
        // Handle ref_id filtering for purchase
        if ($refType === 'purchase' && $refId) {
            // Get pembelian nomor to match with referensi
            $pembelian = \App\Models\Pembelian::find($refId);
            if ($pembelian && $pembelian->nomor_pembelian) {
                $jurnalUmumQuery->where('ju.referensi', $pembelian->nomor_pembelian);
            }
        }
        
        if ($accountCode) { 
            $jurnalUmumQuery->where('coas.kode_akun', $accountCode);
        }
        
        // Only execute query if not filtering for purchase
        if (!isset($jurnalUmumResults)) {
            $jurnalUmumResults = $jurnalUmumQuery->get();
        }
        
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
            $pembelian = \App\Models\Pembelian::with([
                'vendor',
                'details.bahanBaku',
                'details.bahanPendukung'
            ])->find($purchaseId);

            if (!$pembelian) return;

            // Cek apakah sudah ada di journal_entries (sistem modern)
            $existingEntry = \App\Models\JournalEntry::where('ref_type', 'purchase')
                ->where('ref_id', $purchaseId)
                ->first();

            if ($existingEntry) return; // Sudah ada, tidak perlu dibuat ulang

            // Buat jurnal menggunakan JournalService (sistem modern)
            \App\Services\JournalService::createJournalFromPembelian($pembelian);

        } catch (\Exception $e) {
            \Log::error('Failed to auto-generate purchase journal', [
                'purchase_id' => $purchaseId,
                'error' => $e->getMessage(),
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
            $bahanBakuCoas = ['1101', '114', '1141', '1142', '1143'];
            $bahanPendukungCoas = ['1150', '1151', '1152', '1153', '1154', '1155', '1156', '1157', '115'];
            
            if (in_array($accountCode, $bahanBakuCoas) || in_array($accountCode, $bahanPendukungCoas)) {
                $saldoAwal = $this->getInventorySaldoAwal($accountCode);
            } else {
                $saldoAwal = (float)($coa->saldo_awal ?? 0);
            }

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

            // Group by journal entry untuk struktur yang sesuai dengan view
            $groupedLines = $journalLines->groupBy('id');
            
            foreach ($groupedLines as $entryId => $entryLines) {
                $firstLine = $entryLines->first();
                
                $lines->push((object) [
                    'id' => $firstLine->id,
                    'tanggal' => $firstLine->tanggal,
                    'memo' => $firstLine->memo,
                    'lines' => $entryLines->map(function($line) {
                        return (object) [
                            'id' => $line->line_id,
                            'debit' => $line->debit,
                            'credit' => $line->credit,
                            'memo' => $line->line_memo,
                            'coa' => (object) [
                                'kode_akun' => $line->kode_akun,
                                'nama_akun' => $line->nama_akun,
                                'tipe_akun' => $line->tipe_akun
                            ]
                        ];
                    })
                ]);
            }

            $totalDebit = $journalLines->sum('debit');
            $totalKredit = $journalLines->sum('credit');
            $saldoAkhir = $saldoAwal + $totalDebit - $totalKredit;
        }

        return view('akuntansi.buku-besar', compact('coas','accountCode','lines','from','to','saldoAwal','month','year','totalDebit','totalKredit','saldoAkhir'));
    }

    private function getInventorySaldoAwal($kodeAkun)
    {
        // Map kode akun persediaan ke COA yang terhubung dengan bahan baku/pendukung
        $bahanBakuCoas = ['1101', '114', '1141', '1142', '1143']; // Persediaan Bahan Baku
        $bahanPendukungCoas = ['1150', '1151', '1152', '1153', '1154', '1155', '1156', '1157', '115']; // Persediaan Bahan Pendukung
        
        $saldoAwal = 0;
        
        // Untuk akun bahan baku
        if (in_array($kodeAkun, $bahanBakuCoas)) {
            // Ambil total saldo awal dari database bahan_bakus
            // Saldo awal = saldo_awal (quantity) * harga_satuan
            if (in_array($kodeAkun, ['1101', '114'])) {
                // Parent accounts - return 0 (not used directly)
                $saldoAwal = 0;
            } else {
                // Specific child account
                $saldoAwal = \DB::table('bahan_bakus')
                    ->where('coa_persediaan_id', $kodeAkun)
                    ->where('saldo_awal', '>', 0)
                    ->sum(\DB::raw('saldo_awal * harga_satuan'));
            }
        }
        
        // Untuk akun bahan pendukung
        if (in_array($kodeAkun, $bahanPendukungCoas)) {
            // Ambil total saldo awal dari database bahan_pendukungs
            // Saldo awal = saldo_awal (quantity) * harga_satuan
            if ($kodeAkun === '115') {
                // Parent account - return 0 (not used directly)
                $saldoAwal = 0;
            } else {
                // Specific child account
                $saldoAwal = \DB::table('bahan_pendukungs')
                    ->where('coa_persediaan_id', $kodeAkun)
                    ->where('saldo_awal', '>', 0)
                    ->sum(\DB::raw('saldo_awal * harga_satuan'));
            }
        }
        
        return (float)$saldoAwal;
    }

    public function neracaSaldo(Request $request)
    {
        $bulan = $request->get('bulan', date('m'));
        $tahun = $request->get('tahun', date('Y'));

        $from = \Carbon\Carbon::create($tahun, $bulan, 1)->format('Y-m-d');
        $to   = \Carbon\Carbon::create($tahun, $bulan, 1)->endOfMonth()->format('Y-m-d');

        // Ambil semua COA distinct by kode_akun — sama seperti buku besar
        $coas = \App\Models\Coa::select('kode_akun', 'nama_akun', 'tipe_akun', 'saldo_normal', 'saldo_awal', 'kategori_akun')
            ->groupBy('kode_akun', 'nama_akun', 'tipe_akun', 'saldo_normal', 'saldo_awal', 'kategori_akun')
            ->orderBy('kode_akun')
            ->get();

        // Ambil mutasi periode menggunakan helper yang sama dengan buku besar
        // Saldo akhir neraca saldo = saldo akhir buku besar (selalu sinkron)
        $mutasiByKodeAkun = $this->getAccountSummary($from, $to);

        $totals = [];
        foreach ($coas as $coa) {
            // Untuk akun persediaan bahan baku dan bahan pendukung, ambil saldo awal dari stock_movements
            $bahanBakuCoas = ['1101', '114', '1141', '1142', '1143'];
            $bahanPendukungCoas = ['1150', '1151', '1152', '1153', '1154', '1155', '1156', '1157', '115'];
            
            if (in_array($coa->kode_akun, $bahanBakuCoas) || in_array($coa->kode_akun, $bahanPendukungCoas)) {
                $saldoAwal = $this->getInventorySaldoAwal($coa->kode_akun);
            } else {
                $saldoAwal = (float)($coa->saldo_awal ?? 0);
            }
            
            $totalDebit  = $mutasiByKodeAkun[$coa->kode_akun]['total_debit']  ?? 0;
            $totalKredit = $mutasiByKodeAkun[$coa->kode_akun]['total_kredit'] ?? 0;

            // Gunakan posisi akun berdasarkan digit pertama kode akun (sama seperti balance sheet)
            // Akun 1xx, 5xx, 6xx = debit normal
            // Akun 2xx, 3xx, 4xx = kredit normal
            $firstDigit = substr($coa->kode_akun, 0, 1);
            $isDebitNormal = !in_array($firstDigit, ['2', '3', '4']);
            
            // Override dengan saldo_normal jika ada dan tidak 'debit' untuk akun 2,3,4
            $saldoNormal = strtolower($coa->saldo_normal ?? '');
            if (!empty($saldoNormal)) {
                // Untuk akun 2,3,4, forced ke credit normal regardless of saldo_normal
                if (in_array($firstDigit, ['2', '3', '4'])) {
                    $isDebitNormal = false;
                } else {
                    $isDebitNormal = ($saldoNormal === 'debit');
                }
            }

            // Saldo akhir = saldo akhir buku besar untuk akun ini
            if ($isDebitNormal) {
                $saldoAkhir = $saldoAwal + $totalDebit - $totalKredit;
            } else {
                $saldoAkhir = $saldoAwal - $totalDebit + $totalKredit;
            }

            $posisi = $this->posisiNeracaSaldo($saldoAkhir, $coa->tipe_akun);

            $totals[$coa->kode_akun] = [
                'saldo_awal'      => $saldoAwal,
                'debit'           => $totalDebit,
                'kredit'          => $totalKredit,
                'saldo_debit'     => $posisi['debit'],
                'saldo_kredit'    => $posisi['kredit'],
                'saldo_akhir'     => $saldoAkhir,
                'saldo_normal'    => $saldoNormal,
                'is_debit_normal' => $isDebitNormal,
            ];
        }

        // Hanya tampilkan akun yang punya aktivitas atau saldo
        $coas = $coas->filter(function ($coa) use ($totals) {
            $t = $totals[$coa->kode_akun] ?? null;
            if (!$t) return false;
            return $t['saldo_awal'] != 0 || $t['debit'] != 0 || $t['kredit'] != 0 || $t['saldo_akhir'] != 0;
        });

        return view('akuntansi.neraca-saldo', compact('coas', 'totals', 'bulan', 'tahun'));
    }

    public function laporanPosisiKeuangan(Request $request)
    {
        // Gunakan format bulan/tahun seperti neraca saldo untuk konsistensi
        $bulan = $request->get('bulan', date('m'));
        $tahun = $request->get('tahun', date('Y'));
        
        // Validasi range
        if ($bulan < 1 || $bulan > 12) {
            $bulan = date('m');
        }
        if ($tahun < 2020 || $tahun > 2030) {
            $tahun = date('Y');
        }
        
        // Ensure bulan is zero-padded
        $bulan = str_pad($bulan, 2, '0', STR_PAD_LEFT);
        
        // Hitung periode - sama seperti neraca saldo
        $tanggalAwal = \Carbon\Carbon::create($tahun, $bulan, 1)->format('Y-m-d');
        $tanggalAkhir = \Carbon\Carbon::create($tahun, $bulan, 1)->endOfMonth()->format('Y-m-d');

        // Gunakan NeracaService untuk konsistensi dengan neraca saldo
        $neracaService = app(\App\Services\NeracaService::class);
        $neraca = $neracaService->generateLaporanPosisiKeuangan($tanggalAwal, $tanggalAkhir);

        return view('akuntansi.laporan_posisi_keuangan', compact('neraca', 'bulan', 'tahun'));
    }

    private function getLaporanPosisiKeuanganData($bulan, $tahun)
    {
        // Hitung tanggal awal dan akhir bulan (sama seperti neraca saldo)
        $from = \Carbon\Carbon::create($tahun, $bulan, 1)->format('Y-m-d');
        $to = \Carbon\Carbon::create($tahun, $bulan, 1)->endOfMonth()->format('Y-m-d');

        // Ambil semua COA
        $coas = \App\Models\Coa::select('kode_akun', 'nama_akun', 'tipe_akun', 'saldo_normal', 'saldo_awal', 'kategori_akun')
            ->groupBy('kode_akun', 'nama_akun', 'tipe_akun', 'saldo_normal', 'saldo_awal', 'kategori_akun')
            ->orderBy('kode_akun')
            ->get();

        // Ambil mutasi periode menggunakan helper yang sama dengan neraca saldo
        $mutasiByKodeAkun = $this->getAccountSummary($from, $to);

        $trialBalanceData = [];
        foreach ($coas as $coa) {
            // Untuk akun persediaan bahan baku dan bahan pendukung, ambil saldo awal dari stock_movements
            $bahanBakuCoas = ['1101', '114', '1141', '1142', '1143'];
            $bahanPendukungCoas = ['1150', '1151', '1152', '1153', '1154', '1155', '1156', '1157', '115'];
            
            if (in_array($coa->kode_akun, $bahanBakuCoas) || in_array($coa->kode_akun, $bahanPendukungCoas)) {
                $saldoAwal = $this->getInventorySaldoAwal($coa->kode_akun);
            } else {
                $saldoAwal = (float)($coa->saldo_awal ?? 0);
            }
            
            $totalDebit  = $mutasiByKodeAkun[$coa->kode_akun]['total_debit']  ?? 0;
            $totalKredit = $mutasiByKodeAkun[$coa->kode_akun]['total_kredit'] ?? 0;

            $firstDigit = substr($coa->kode_akun, 0, 1);
            $isDebitNormal = !in_array($firstDigit, ['2', '3', '4']);
            
            $saldoNormal = strtolower($coa->saldo_normal ?? '');
            if (!empty($saldoNormal)) {
                if (in_array($firstDigit, ['2', '3', '4'])) {
                    $isDebitNormal = false;
                } else {
                    $isDebitNormal = ($saldoNormal === 'debit');
                }
            }

            if ($isDebitNormal) {
                $saldoAkhir = $saldoAwal + $totalDebit - $totalKredit;
            } else {
                $saldoAkhir = $saldoAwal - $totalDebit + $totalKredit;
            }

            $trialBalanceData[$coa->kode_akun] = [
                'coa' => $coa,
                'saldo_akhir' => $saldoAkhir
            ];
        }
        
        $getFinalBalance = function($coa) use ($trialBalanceData) {
            return $trialBalanceData[$coa->kode_akun]['saldo_akhir'] ?? 0;
        };
        
        $isParentAccount = function($coa) use ($coas) {
            $prefix = $coa->kode_akun;
            return $coas->filter(function($child) use ($prefix) {
                return $child->kode_akun !== $prefix && 
                       str_starts_with($child->kode_akun, $prefix) && 
                       strlen($child->kode_akun) > strlen($prefix);
            })->count() > 0;
        };
        
        // Calculate Profit/Loss
        $totalRevenue = 0;
        $totalExpense = 0;
        
        foreach ($coas as $coa) {
            if (in_array($coa->tipe_akun, ['Revenue', 'revenue', 'Pendapatan'])) {
                $totalRevenue += $getFinalBalance($coa);
            }
            if (in_array($coa->tipe_akun, ['Expense', 'expense', 'Beban', 'Biaya'])) {
                $totalExpense += $getFinalBalance($coa);
            }
        }
        
        $profitLoss = $totalRevenue - $totalExpense;
        
        // Group accounts
        $asetLancar = $coas->filter(function($coa) use ($isParentAccount, $getFinalBalance) {
            if (!in_array($coa->tipe_akun, ['Asset', 'asset', 'Aktiva', 'ASET', 'Aset'])) return false;
            $balance = $getFinalBalance($coa);
            if ($balance == 0) return false;
            $isCurrentAsset = substr($coa->kode_akun, 0, 1) === '1';
            if (!$isCurrentAsset) return false;
            return !$isParentAccount($coa) || $balance != 0;
        })->sortBy('kode_akun');
        
        $asetTidakLancar = $coas->filter(function($coa) use ($isParentAccount, $getFinalBalance) {
            if (!in_array($coa->tipe_akun, ['Asset', 'asset', 'Aktiva', 'ASET', 'Aset'])) return false;
            $balance = $getFinalBalance($coa);
            if ($balance == 0) return false;
            $isNonCurrentAsset = substr($coa->kode_akun, 0, 1) === '2';
            if (!$isNonCurrentAsset) return false;
            return !$isParentAccount($coa) || $balance != 0;
        })->sortBy('kode_akun');
        
        $kewajibanPendek = $coas->filter(function($coa) use ($isParentAccount, $getFinalBalance) {
            if ($isParentAccount($coa)) return false;
            if (!in_array($coa->tipe_akun, ['Liability', 'liability', 'Pasiva', 'Kewajiban'])) return false;
            $balance = $getFinalBalance($coa);
            if ($balance <= 0) return false;
            return (stripos($coa->kategori_akun, 'Hutang') !== false &&
                    stripos($coa->kategori_akun, 'Jangka Panjang') === false) ||
                   (stripos($coa->nama_akun, 'Hutang Usaha') !== false) ||
                   (stripos($coa->nama_akun, 'Hutang Pajak') !== false);
        })->sortBy('kode_akun');
        
        $kewajibanPanjang = $coas->filter(function($coa) use ($isParentAccount, $getFinalBalance) {
            if ($isParentAccount($coa)) return false;
            if (!in_array($coa->tipe_akun, ['Liability', 'liability', 'Pasiva', 'Kewajiban'])) return false;
            $balance = $getFinalBalance($coa);
            if ($balance <= 0) return false;
            return (stripos($coa->kategori_akun, 'Jangka Panjang') !== false) ||
                   (stripos($coa->nama_akun, 'Hutang Bank') !== false) ||
                   (stripos($coa->nama_akun, 'Hutang Jangka Panjang') !== false) ||
                   (stripos($coa->nama_akun, 'Obligasi') !== false);
        })->sortBy('kode_akun');
        
        $ekuitas = $coas->filter(function($coa) use ($isParentAccount, $getFinalBalance) {
            if ($isParentAccount($coa)) return false;
            if (!in_array($coa->tipe_akun, ['Equity', 'equity', 'Modal', 'Ekuitas'])) return false;
            $balance = $getFinalBalance($coa);
            if ($balance == 0) return false;
            return true;
        })->sortBy('kode_akun');
        
        // Calculate totals
        $totalAsetLancar = $asetLancar->sum(function($coa) use ($getFinalBalance) {
            return $getFinalBalance($coa);
        });
        
        $totalAsetTidakLancar = $asetTidakLancar->sum(function($coa) use ($getFinalBalance) {
            return $getFinalBalance($coa);
        });
        
        $totalKewajibanPendek = $kewajibanPendek->sum(function($coa) use ($getFinalBalance) {
            $balance = $getFinalBalance($coa);
            return $balance > 0 ? $balance : 0;
        });
        
        $totalKewajibanPanjang = $kewajibanPanjang->sum(function($coa) use ($getFinalBalance) {
            $balance = $getFinalBalance($coa);
            return $balance > 0 ? $balance : 0;
        });
        
        $totalEkuitas = $ekuitas->sum(function($coa) use ($getFinalBalance) {
            return $getFinalBalance($coa);
        });
        
        $totalAset = $totalAsetLancar + $totalAsetTidakLancar;
        $totalKewajiban = $totalKewajibanPendek + $totalKewajibanPanjang;
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

    /**
     * Laporan Laba Rugi
     */
    public function labaRugi(Request $request)
    {
        $periode = $request->get('periode', now()->format('Y-m'));
        
        // Parse periode to get bulan and tahun
        $tahun = substr($periode, 0, 4);
        $bulan = substr($periode, 5, 2);
        
        // Hitung tanggal awal dan akhir bulan
        $from = \Carbon\Carbon::create($tahun, $bulan, 1)->format('Y-m-d');
        $to = \Carbon\Carbon::create($tahun, $bulan, 1)->endOfMonth()->format('Y-m-d');

        // Ambil semua COA
        $coas = \App\Models\Coa::select('kode_akun', 'nama_akun', 'tipe_akun', 'saldo_normal', 'saldo_awal', 'kategori_akun')
            ->groupBy('kode_akun', 'nama_akun', 'tipe_akun', 'saldo_normal', 'saldo_awal', 'kategori_akun')
            ->orderBy('kode_akun')
            ->get();

        // Ambil mutasi periode
        $mutasiByKodeAkun = $this->getAccountSummary($from, $to);

        $accountData = [];
        foreach ($coas as $coa) {
            $saldoAwal = (float)($coa->saldo_awal ?? 0);
            $totalDebit  = $mutasiByKodeAkun[$coa->kode_akun]['total_debit']  ?? 0;
            $totalKredit = $mutasiByKodeAkun[$coa->kode_akun]['total_kredit'] ?? 0;

            // Hitung saldo akhir
            $firstDigit = substr($coa->kode_akun, 0, 1);
            $isDebitNormal = !in_array($firstDigit, ['2', '3', '4']);
            
            if ($isDebitNormal) {
                $saldoAkhir = $saldoAwal + $totalDebit - $totalKredit;
            } else {
                $saldoAkhir = $saldoAwal - $totalDebit + $totalKredit;
            }

            $accountData[$coa->kode_akun] = [
                'coa' => $coa,
                'saldo_akhir' => $saldoAkhir
            ];
        }
        
        // Filter akun pendapatan dan beban
        $pendapatan = $coas->filter(function($coa) use ($accountData) {
            if (!in_array($coa->tipe_akun, ['Revenue', 'revenue', 'Pendapatan'])) return false;
            $saldo = $accountData[$coa->kode_akun]['saldo_akhir'] ?? 0;
            return $saldo != 0;
        })->sortBy('kode_akun');
        
        $beban = $coas->filter(function($coa) use ($accountData) {
            if (!in_array($coa->tipe_akun, ['Expense', 'expense', 'Beban', 'Biaya'])) return false;
            $saldo = $accountData[$coa->kode_akun]['saldo_akhir'] ?? 0;
            return $saldo != 0;
        })->sortBy('kode_akun');
        
        // Hitung total
        $totalPendapatan = $pendapatan->sum(function($coa) use ($accountData) {
            return $accountData[$coa->kode_akun]['saldo_akhir'] ?? 0;
        });
        
        $totalBeban = $beban->sum(function($coa) use ($accountData) {
            return $accountData[$coa->kode_akun]['saldo_akhir'] ?? 0;
        });
        
        $labaRugi = $totalPendapatan - $totalBeban;
        
        return view('akuntansi.laba_rugi', compact(
            'periode', 'pendapatan', 'beban', 
            'totalPendapatan', 'totalBeban', 'labaRugi',
            'accountData'
        ));
    }
}
