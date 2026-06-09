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
        // Normalize tipe_akun to uppercase for comparison
        $tipeAkunUpper = strtoupper(trim($tipeAkun));
        
        // ASET, BEBAN, BIAYA have normal DEBIT balance
        // KEWAJIBAN, MODAL, EKUITAS, PENDAPATAN have normal KREDIT balance
        $isDebitNormal = in_array($tipeAkunUpper, [
            'ASET', 'ASSET', 'AKTIVA',                    // Asset variations
            'BEBAN', 'EXPENSE', 'BIAYA', 'COST'           // Expense variations
        ]);

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
     * HANYA DARI jurnal_umum - konsisten dengan Buku Besar
     * @param string $from Tanggal awal periode (Y-m-d)
     * @param string $to Tanggal akhir periode (Y-m-d)
     * @param string|null $kodeAkun Filter by kode_akun (optional, untuk Buku Besar)
     * @return array Array dengan key kode_akun berisi summary per akun
     */
    private function getAccountSummary($from, $to, $kodeAkun = null)
    {
        // Hanya dari jurnal_umum - SAMA DENGAN LOGIKA BUKU BESAR
        $queryJurnalUmum = DB::table('jurnal_umum as ju')
            ->join('coas', 'coas.id', '=', 'ju.coa_id')
            ->where('ju.user_id', auth()->id()) // MULTI-TENANT: Filter by user_id
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

        // Buat array dengan kode_akun sebagai key
        $summaryByKodeAkun = [];
        foreach ($mutasiJurnalUmum as $line) {
            $summaryByKodeAkun[$line->kode_akun] = [
                'total_debit' => $line->total_debit,
                'total_kredit' => $line->total_kredit
            ];
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

        // Map ref_type to tipe_referensi - use actual values stored in database
        $mappedRefType = match($refType) {
            'purchase' => 'pembelian',
            'sale' => 'sale',  // Jurnal penjualan disimpan dengan 'sale', bukan 'penjualan'
            default => $refType
        };

        // Keep ref_id as is - it's stored as string in jurnal_umum.referensi
        $mappedRefId = $refId ? (string)$refId : null;

        // MULTI-TENANT: Query jurnal_umum (flat structure)
        $query = \DB::table('jurnal_umum as ju')
            ->leftJoin('coas', 'coas.id', '=', 'ju.coa_id')
            ->select([
                'ju.*',
                'coas.kode_akun',
                'coas.nama_akun',
                'coas.tipe_akun'
            ])
            ->where('ju.user_id', auth()->id()) // MULTI-TENANT: Filter by user_id
            ->where(function($q) {
                $q->where('ju.debit', '!=', 0)
                  ->orWhere('ju.kredit', '!=', 0);
            })

            ->orderBy('ju.tanggal','asc')
            ->orderBy('ju.created_at','asc')
            ->orderBy('ju.id','asc');
if ($from) { $query->whereDate('ju.tanggal','>=',$from); }
        if ($to)   { $query->whereDate('ju.tanggal','<=',$to); }
        if ($mappedRefType) { $query->where('ju.tipe_referensi', $mappedRefType); }
        if ($mappedRefId)   { $query->where('ju.referensi', $mappedRefId); }
        if ($accountCode) { 
            $query->where('coas.kode_akun', $accountCode);
        }
        
        $results = $query->get();
        
        // Group results by date and reference for display
        $entries = collect();
        $groupedResults = $results->groupBy(function($item) {
            return $item->tanggal . '_' . ($item->tipe_referensi ?? 'manual') . '_' . ($item->referensi ?? $item->id);
        });
        
        foreach ($groupedResults as $groupKey => $lines) {
            $firstLine = $lines->first();
            
            // Skip if no valid lines
            if ($lines->isEmpty()) continue;
            
            $entry = (object) [
                'id' => $firstLine->id,
                'tanggal' => $firstLine->tanggal,
                'created_at' => $firstLine->created_at,
                'ref_type' => $firstLine->tipe_referensi,
                'ref_id' => $firstLine->referensi,
                'memo' => $firstLine->keterangan,
                'lines' => $lines->map(function($line) {
                    return (object) [
                        'id' => $line->id,
                        'debit' => $line->debit,
                        'credit' => $line->kredit,
                        'memo' => $line->keterangan,
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
        

// Sort all entries by date and created_at
        $entries = $entries->sortBy(function($entry) {
            return $entry->tanggal . ' ' . $entry->created_at;
        });
        
        return view('akuntansi.jurnal-umum', compact('entries','from','to','refType','refId','accountCode'));
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
            ->where('je.user_id', auth()->id()) // MULTI-TENANT: Filter by user_id
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

    public function bukuBesarExportExcel(Request $request)
    {
        $from = $request->get('from');
        $to = $request->get('to');
        $accountCode = $request->get('account_code');
        
        $export = new \App\Exports\BukuBesarExport($from, $to, $accountCode);
        return $export->download('buku-besar-'.date('Y-m-d').'.csv');
    }

    public function bukuBesar(Request $request)
    {
        $month = $request->get('month');
        $year = $request->get('year');
        $accountCode = $request->get('account_code');

        // MULTI-TENANT: Filter COA by user_id
        $coas = \App\Models\Coa::select('kode_akun', 'nama_akun', 'tipe_akun')
            ->where('user_id', auth()->id())
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
            // MULTI-TENANT: Filter COA by user_id
            $coa = \App\Models\Coa::where('kode_akun', $accountCode)
                    ->where('user_id', auth()->id())
                    ->first();

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


            // MULTI-TENANT: Query jurnal_umum untuk konsistensi dengan sistem lain
            $query = \DB::table('jurnal_umum as ju')
                ->leftJoin('coas', 'coas.id', '=', 'ju.coa_id')
                ->where('ju.user_id', auth()->id()) // MULTI-TENANT: Filter by user_id
                ->where('coas.kode_akun', $accountCode) // Filter akun yang dipilih
->select([
                    'ju.*',
                    'coas.kode_akun',
                    'coas.nama_akun',
                    'coas.tipe_akun'
                ])
                ->where(function($q) {
                    $q->where('ju.debit', '>', 0)
                      ->orWhere('ju.kredit', '>', 0);
                })

->orderBy('ju.tanggal','asc')
                ->orderBy('ju.id','asc');
            
            if ($month && $year) {
                $query->whereMonth('ju.tanggal', $month)
                       ->whereYear('ju.tanggal', $year);
            }

            $journalLines = $query->get();


            // DEBUG: Log query results untuk verifikasi multi-tenant
            \Log::info('Buku Besar Query Results', [
                'user_id' => auth()->id(),
                'account_code' => $accountCode,
                'total_lines' => $journalLines->count(),
                'sample_data' => $journalLines->take(3)->toArray()
            ]);

            // Create lines structure untuk view buku besar
            foreach ($journalLines as $line) {
                $lines->push((object) [
                    'id' => $line->id,
                    'tanggal' => $line->tanggal,
                    'keterangan' => $line->keterangan,
                    'debit' => $line->debit,
                    'kredit' => $line->kredit,
                    'kode_akun' => $line->kode_akun,
                    'nama_akun' => $line->nama_akun,
                    'tipe_akun' => $line->tipe_akun
]);
            }

            $totalDebit = $journalLines->sum('debit');
            $totalKredit = $journalLines->sum('kredit');
            $saldoAkhir = $saldoAwal + $totalDebit - $totalKredit;
        }

        return view('akuntansi.buku-besar', compact('coas','accountCode','lines','from','to','saldoAwal','month','year','totalDebit','totalKredit','saldoAkhir'));
    }

    public function bukuBesarExportPdf(Request $request)
    {
        $month = $request->get('month');
        $year = $request->get('year');
        $accountCode = $request->get('account_code');

        if (!$accountCode) {
            return redirect()->back()->with('error', 'Silakan pilih akun terlebih dahulu');
        }

        $coa = \App\Models\Coa::where('kode_akun', $accountCode)
                ->where('user_id', auth()->id())
                ->first();

        if (!$coa) {
            return redirect()->back()->with('error', 'Akun tidak ditemukan');
        }

        $bahanBakuCoas = ['1101', '114', '1141', '1142', '1143'];
        $bahanPendukungCoas = ['1150', '1151', '1152', '1153', '1154', '1155', '1156', '1157', '115'];
        
        if (in_array($accountCode, $bahanBakuCoas) || in_array($accountCode, $bahanPendukungCoas)) {
            $saldoAwal = $this->getInventorySaldoAwal($accountCode);
        } else {
            $saldoAwal = (float)($coa->saldo_awal ?? 0);
        }

        $query = \DB::table('jurnal_umum as ju')
            ->leftJoin('coas', 'coas.id', '=', 'ju.coa_id')
            ->where('ju.user_id', auth()->id())
            ->where('coas.kode_akun', $accountCode)
            ->select([
                'ju.*',
                'coas.kode_akun',
                'coas.nama_akun',
                'coas.tipe_akun'
            ])
            ->where(function($q) {
                $q->where('ju.debit', '>', 0)
                  ->orWhere('ju.kredit', '>', 0);
            })
            ->orderBy('ju.tanggal','asc')
            ->orderBy('ju.id','asc');
        
        if ($month && $year) {
            $query->whereMonth('ju.tanggal', $month)
                   ->whereYear('ju.tanggal', $year);
        }

        $journalLines = $query->get();

        $lines = collect();
        foreach ($journalLines as $line) {
            $lines->push((object) [
                'id' => $line->id,
                'tanggal' => $line->tanggal,
                'keterangan' => $line->keterangan,
                'debit' => $line->debit,
                'kredit' => $line->kredit,
                'kode_akun' => $line->kode_akun,
                'nama_akun' => $line->nama_akun,
                'tipe_akun' => $line->tipe_akun
            ]);
        }

        $totalDebit = $journalLines->sum('debit');
        $totalKredit = $journalLines->sum('kredit');
        $saldoAkhir = $saldoAwal + $totalDebit - $totalKredit;

        $perusahaan = \App\Models\Perusahaan::where('user_id', auth()->id())->first();

        $pdf = Pdf::loadView('akuntansi.buku-besar-pdf', compact(
            'coa', 'lines', 'saldoAwal', 'month', 'year', 'totalDebit', 'totalKredit', 'saldoAkhir', 'perusahaan'
        ))->setPaper('a4', 'portrait');

        return $pdf->download('buku-besar-'.$accountCode.'-'.date('YmdHis').'.pdf');
    }

    private function getInventorySaldoAwal($kodeAkun)
    {
        // DISABLED - Logika ini dinonaktifkan untuk mencegah perhitungan saldo awal dari bahan
        // Bahan baku dan bahan pendukung tidak lagi berkontribusi ke saldo awal COA
        
        \Log::info("Skipping inventory saldo awal calculation in AkuntansiController", [
            'kode_akun' => $kodeAkun,
            'reason' => 'Inventory saldo awal calculation disabled for bahan baku/pendukung'
        ]);
        
        return 0; // Selalu return 0 agar tidak ada kontribusi dari bahan
        
        // COMMENTED OUT - Logika lama yang menghitung dari bahan
        /*
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
                // MULTI-TENANT: Filter by user_id
                $saldoAwal = \DB::table('bahan_bakus')
                    ->where('user_id', auth()->id())
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
                // MULTI-TENANT: Filter by user_id
                $saldoAwal = \DB::table('bahan_pendukungs')
                    ->where('user_id', auth()->id())
                    ->where('coa_persediaan_id', $kodeAkun)
                    ->where('saldo_awal', '>', 0)
                    ->sum(\DB::raw('saldo_awal * harga_satuan'));
            }
        }
        
        return (float)$saldoAwal;
        */
    }

    public function neracaSaldo(Request $request)
    {
        $bulan = $request->get('bulan', date('m'));
        $tahun = $request->get('tahun', date('Y'));

        $from = \Carbon\Carbon::create($tahun, $bulan, 1)->format('Y-m-d');
        $to   = \Carbon\Carbon::create($tahun, $bulan, 1)->endOfMonth()->format('Y-m-d');

        // Ambil semua COA distinct by kode_akun — sama seperti buku besar
        $coas = \App\Models\Coa::select('kode_akun', 'nama_akun', 'tipe_akun', 'saldo_normal', 'saldo_awal', 'kategori_akun')
            ->where('user_id', auth()->id()) // MULTI-TENANT: Filter by user_id
            ->groupBy('kode_akun', 'nama_akun', 'tipe_akun', 'saldo_normal', 'saldo_awal', 'kategori_akun')
            ->orderBy('kode_akun')
            ->get();

        // Ambil mutasi periode menggunakan helper yang sama dengan buku besar
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

            // PENTING: Gunakan logika yang SAMA dengan buku besar
            // Saldo Akhir = Saldo Awal + Total Debit - Total Kredit
            // Ini adalah saldo akhir buku besar yang sebenarnya
            $saldoAkhirBukuBesar = $saldoAwal + $totalDebit - $totalKredit;

            // Tentukan posisi di neraca saldo berdasarkan saldo akhir buku besar
            $posisi = $this->posisiNeracaSaldo($saldoAkhirBukuBesar, $coa->tipe_akun);

            $totals[$coa->kode_akun] = [
                'saldo_awal'      => $saldoAwal,
                'debit'           => $totalDebit,
                'kredit'          => $totalKredit,
                'saldo_debit'     => $posisi['debit'],
                'saldo_kredit'    => $posisi['kredit'],
                'saldo_akhir'     => $saldoAkhirBukuBesar, // Saldo akhir dari buku besar
            ];
        }

        // Hanya tampilkan akun yang punya aktivitas atau saldo
        $coas = $coas->filter(function ($coa) use ($totals) {
            $t = $totals[$coa->kode_akun] ?? null;
            if (!$t) return false;
            return $t['saldo_awal'] != 0 || $t['debit'] != 0 || $t['kredit'] != 0 || $t['saldo_akhir'] != 0;
        });

        // Calculate balance check totals
        $totalSaldoDebit = 0;
        $totalSaldoKredit = 0;
        $totalMutasiDebit = 0;
        $totalMutasiKredit = 0;
        
        foreach ($coas as $coa) {
            $data = $totals[$coa->kode_akun] ?? [];
            $totalSaldoDebit += $data['saldo_debit'] ?? 0;
            $totalSaldoKredit += $data['saldo_kredit'] ?? 0;
            $totalMutasiDebit += $data['debit'] ?? 0;
            $totalMutasiKredit += $data['kredit'] ?? 0;
        }
        
        // Balance check
        $balanceDiff = abs($totalSaldoDebit - $totalSaldoKredit);
        $isBalanced = $balanceDiff < 0.01; // Toleransi 1 sen untuk pembulatan
        
        // Log balance check for debugging
        \Log::info('Neraca Saldo Balance Check', [
            'user_id' => auth()->id(),
            'periode' => "$bulan/$tahun",
            'total_saldo_debit' => $totalSaldoDebit,
            'total_saldo_kredit' => $totalSaldoKredit,
            'total_mutasi_debit' => $totalMutasiDebit,
            'total_mutasi_kredit' => $totalMutasiKredit,
            'balance_diff' => $balanceDiff,
            'is_balanced' => $isBalanced,
            'total_accounts' => $coas->count()
        ]);

        return view('akuntansi.neraca-saldo', compact(
            'coas', 'totals', 'bulan', 'tahun',
            'totalSaldoDebit', 'totalSaldoKredit', 
            'totalMutasiDebit', 'totalMutasiKredit',
            'balanceDiff', 'isBalanced'
        ));
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
        
        // ✅ PERBAIKAN: Hitung Laba/Rugi Bersih dari Laporan Laba Rugi untuk periode yang sama
        $labaRugiData = $this->calculateLabaRugiBersih($tanggalAwal, $tanggalAkhir);
        $labaRugiBersih = $labaRugiData['laba_bersih'];
        
        // Tambahkan data laba/rugi ke neraca
        $neraca['laba_rugi_berjalan'] = $labaRugiBersih;
        $neraca['laba_rugi_akun_nama'] = $labaRugiBersih >= 0 ? 'Laba Berjalan' : 'Rugi Berjalan';
        $neraca['total_ekuitas_with_laba_rugi'] = $neraca['ekuitas']['total'] + $labaRugiBersih;
        $neraca['total_kewajiban_ekuitas'] = $neraca['kewajiban']['total'] + $neraca['total_ekuitas_with_laba_rugi'];
        
        // Update status keseimbangan
        $neraca['neraca_seimbang'] = abs($neraca['aset']['total_aset'] - $neraca['total_kewajiban_ekuitas']) < 0.01;
        $neraca['selisih'] = $neraca['aset']['total_aset'] - $neraca['total_kewajiban_ekuitas'];

        return view('akuntansi.laporan_posisi_keuangan', compact('neraca', 'bulan', 'tahun'));
    }

    /**
     * Hitung Laba/Rugi Bersih untuk periode tertentu
     * Digunakan untuk Laporan Posisi Keuangan
     */
    private function calculateLabaRugiBersih($from, $to)
    {
        $coas = \App\Models\Coa::where('user_id', auth()->id())
            ->orderBy('kode_akun')
            ->get();

        // Hitung mutasi per COA untuk periode ini
        $mutasi = \DB::table('jurnal_umum')
            ->select('coa_id', 
                \DB::raw('SUM(debit) as total_debit'), 
                \DB::raw('SUM(kredit) as total_kredit'))
            ->where('user_id', auth()->id())
            ->whereDate('tanggal', '>=', $from)
            ->whereDate('tanggal', '<=', $to)
            ->groupBy('coa_id')
            ->get()
            ->keyBy('coa_id');

        // Build account data
        $accountData = [];
        foreach ($coas as $coa) {
            $m = $mutasi[$coa->id] ?? null;
            $debit = $m ? (float)$m->total_debit : 0;
            $kredit = $m ? (float)$m->total_kredit : 0;
            
            $first = substr($coa->kode_akun, 0, 1);
            $saldoAkhir = $first === '4' ? ($kredit - $debit) : ($debit - $kredit);
            
            $accountData[$coa->kode_akun] = [
                'coa' => $coa,
                'saldo_akhir' => $saldoAkhir
            ];
        }
        
        // Hitung Total Pendapatan (4xxx)
        $totalPendapatan = 0;
        foreach ($coas as $coa) {
            if (substr($coa->kode_akun, 0, 1) === '4') {
                $saldo = $accountData[$coa->kode_akun]['saldo_akhir'] ?? 0;
                if ($saldo > 0) {
                    $totalPendapatan += $saldo;
                }
            }
        }
        
        // Hitung HPP (5xx - Harga Pokok Penjualan)
        $hppAmount = 0;
        foreach ($coas as $coa) {
            if (substr($coa->kode_akun, 0, 1) === '5') {
                if (stripos($coa->nama_akun, 'harga pokok') !== false ||
                    stripos($coa->nama_akun, 'hpp') !== false ||
                    $coa->kode_akun === '56' ||
                    $coa->kode_akun === '560') {
                    $saldo = $accountData[$coa->kode_akun]['saldo_akhir'] ?? 0;
                    if ($saldo > 0) {
                        $hppAmount += $saldo;
                    }
                }
            }
        }
        
        // Hitung Laba Kotor
        $labaKotor = $totalPendapatan - $hppAmount;
        
        // Hitung Total Beban (5xx dan 6xx, excluding HPP)
        $totalBeban = 0;
        foreach ($coas as $coa) {
            $first = substr($coa->kode_akun, 0, 1);
            if (in_array($first, ['5', '6'])) {
                // Skip HPP accounts
                if (stripos($coa->nama_akun, 'harga pokok') !== false ||
                    stripos($coa->nama_akun, 'hpp') !== false ||
                    $coa->kode_akun === '56' ||
                    $coa->kode_akun === '560') {
                    continue;
                }
                
                $saldo = $accountData[$coa->kode_akun]['saldo_akhir'] ?? 0;
                if ($saldo > 0) {
                    $totalBeban += $saldo;
                }
            }
        }
        
        // Hitung Laba/Rugi Bersih
        $labaBersih = $labaKotor - $totalBeban;
        
        return [
            'total_pendapatan' => $totalPendapatan,
            'hpp' => $hppAmount,
            'laba_kotor' => $labaKotor,
            'total_beban' => $totalBeban,
            'laba_bersih' => $labaBersih
        ];
    }

    private function getLaporanPosisiKeuanganData($bulan, $tahun)
    {
        // Hitung tanggal awal dan akhir bulan (sama seperti neraca saldo)
        $from = \Carbon\Carbon::create($tahun, $bulan, 1)->format('Y-m-d');
        $to = \Carbon\Carbon::create($tahun, $bulan, 1)->endOfMonth()->format('Y-m-d');

        // Ambil semua COA
        $coas = \App\Models\Coa::select('kode_akun', 'nama_akun', 'tipe_akun', 'saldo_normal', 'saldo_awal', 'kategori_akun')
            ->where('user_id', auth()->id()) // MULTI-TENANT: Filter by user_id
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
        
        // ✅ PERBAIKAN: Hitung Laba/Rugi Bersih dari Laporan Laba Rugi untuk periode yang sama
        $labaRugiBersih = $this->calculateLabaRugiBersih($from, $to)['laba_bersih'];
        
        // Tentukan nama akun berdasarkan nilai laba/rugi
        $labaRugiAkunNama = $labaRugiBersih >= 0 ? 'Laba Berjalan' : 'Rugi Berjalan';
        
        $totalAset = $totalAsetLancar + $totalAsetTidakLancar;
        $totalKewajiban = $totalKewajibanPendek + $totalKewajibanPanjang;
        
        // ✅ PERBAIKAN: Total Ekuitas = Modal + Laba/Rugi Bersih
        $totalEkuitasWithProfitLoss = $totalEkuitas + $labaRugiBersih;
        $totalKewajibanEkuitas = $totalKewajiban + $totalEkuitasWithProfitLoss;
        
        return compact(
            'asetLancar', 'asetTidakLancar', 
            'kewajibanPendek', 'kewajibanPanjang', 'ekuitas',
            'totalAsetLancar', 'totalAsetTidakLancar',
            'totalKewajibanPendek', 'totalKewajibanPanjang', 'totalEkuitas',
            'totalAset', 'totalKewajiban', 'totalKewajibanEkuitas',
            'getFinalBalance', 'labaRugiBersih', 'labaRugiAkunNama',
            'totalRevenue', 'totalExpense'
        );
    }

    /**
     * Laporan Laba Rugi
     */
    private function prepareLabaRugiData(Request $request)
    {
        $periode = $request->get('periode', now()->format('Y-m'));

        if ($request->has('from') && $request->has('to')) {
            $from = $request->get('from');
            $to = $request->get('to');
            $periode = substr($from, 0, 7);
        } else {
            $tahun = substr($periode, 0, 4);
            $bulan = substr($periode, 5, 2);
            $from  = \Carbon\Carbon::create($tahun, $bulan, 1)->format('Y-m-d');
            $to    = \Carbon\Carbon::create($tahun, $bulan, 1)->endOfMonth()->format('Y-m-d');
        }

        // Ambil semua COA
        $coas = \App\Models\Coa::where('user_id', auth()->id())
            ->orderBy('kode_akun')
            ->get();

        // Debug: Log periode dan COA count
        \Log::info('Laba Rugi Report', [
            'periode' => $periode,
            'from' => $from,
            'to' => $to,
            'user_id' => auth()->id(),
            'coa_count' => $coas->count()
        ]);

        // Hitung mutasi per COA untuk periode ini
        $mutasi = \DB::table('jurnal_umum')
            ->select('coa_id', 
                \DB::raw('SUM(debit) as total_debit'), 
                \DB::raw('SUM(kredit) as total_kredit'))
            ->where('user_id', auth()->id())
            ->whereDate('tanggal', '>=', $from)
            ->whereDate('tanggal', '<=', $to)
            ->groupBy('coa_id')
            ->get()
            ->keyBy('coa_id');

        // Debug: Log mutasi count
        \Log::info('Mutasi Count', ['count' => $mutasi->count()]);

        // Build account data with final balance
        $accountData = [];
        foreach ($coas as $coa) {
            $m = $mutasi[$coa->id] ?? null;
            $debit = $m ? (float)$m->total_debit : 0;
            $kredit = $m ? (float)$m->total_kredit : 0;
            
            // Calculate saldo akhir based on account type
            // Pendapatan (4xxx): saldo normal kredit → saldo = kredit - debit
            // HPP & Beban (5xxx, 6xxx): saldo normal debit → saldo = debit - kredit
            $first = substr($coa->kode_akun, 0, 1);
            $saldoAkhir = $first === '4' ? ($kredit - $debit) : ($debit - $kredit);
            
            $accountData[$coa->kode_akun] = [
                'coa' => $coa,
                'saldo_akhir' => $saldoAkhir
            ];
        }
        
        // Filter akun pendapatan (4xxx)
        $pendapatan = $coas->filter(function($coa) use ($accountData) {
            $first = substr($coa->kode_akun, 0, 1);
            if ($first !== '4') return false;
            $saldo = $accountData[$coa->kode_akun]['saldo_akhir'] ?? 0;
            return $saldo > 0;
        })->sortBy('kode_akun');

        // Debug: Log pendapatan
        $allPendapatanCoas = $coas->filter(function($coa) {
            $first = substr($coa->kode_akun, 0, 1);
            return $first === '4';
        });
        \Log::info('Pendapatan COAs', [
            'count' => $allPendapatanCoas->count(),
            'filtered_count' => $pendapatan->count(),
            'coas' => $allPendapatanCoas->map(function($coa) use ($accountData) {
                return [
                    'kode' => $coa->kode_akun,
                    'nama' => $coa->nama_akun,
                    'saldo' => $accountData[$coa->kode_akun]['saldo_akhir'] ?? 0
                ];
            })->toArray()
        ]);
        
        // Get HPP - cari akun dengan nama "Harga Pokok Penjualan" atau kode 56/560
        $hppCoa = $coas->first(function($coa) {
            $first = substr($coa->kode_akun, 0, 1);
            if ($first !== '5') return false;
            
            // Cari berdasarkan nama atau kode
            $isHpp = stripos($coa->nama_akun, 'harga pokok') !== false ||
                     stripos($coa->nama_akun, 'hpp') !== false ||
                     $coa->kode_akun === '56' ||
                     $coa->kode_akun === '560';
            
            return $isHpp;
        });
        
        $hppAmount = $hppCoa ? ($accountData[$hppCoa->kode_akun]['saldo_akhir'] ?? 0) : 0;
        
        // Filter akun beban (5xxx, 6xxx) excluding HPP
        $beban = $coas->filter(function($coa) use ($accountData, $hppCoa) {
            $first = substr($coa->kode_akun, 0, 1);
            if (!in_array($first, ['5', '6'])) return false;
            
            // Exclude HPP account
            if ($hppCoa && $coa->id === $hppCoa->id) return false;
            
            // Also exclude if nama contains "harga pokok" or "hpp"
            if (stripos($coa->nama_akun, 'harga pokok') !== false ||
                stripos($coa->nama_akun, 'hpp') !== false) {
                return false;
            }
            
            $saldo = $accountData[$coa->kode_akun]['saldo_akhir'] ?? 0;
            return $saldo > 0;
        })->sortBy('kode_akun');

        // Debug: Log beban
        $allBebanCoas = $coas->filter(function($coa) {
            $first = substr($coa->kode_akun, 0, 1);
            return in_array($first, ['5', '6']);
        });
        \Log::info('Beban COAs', [
            'count' => $allBebanCoas->count(),
            'filtered_count' => $beban->count(),
            'hpp_kode' => $hppCoa ? $hppCoa->kode_akun : 'NOT FOUND',
            'hpp_nama' => $hppCoa ? $hppCoa->nama_akun : 'NOT FOUND',
            'coas' => $allBebanCoas->map(function($coa) use ($accountData, $hppCoa) {
                $isHpp = $hppCoa && $coa->id === $hppCoa->id;
                return [
                    'kode' => $coa->kode_akun,
                    'nama' => $coa->nama_akun,
                    'saldo' => $accountData[$coa->kode_akun]['saldo_akhir'] ?? 0,
                    'is_hpp' => $isHpp
                ];
            })->toArray()
        ]);
        
        // Hitung total
        $totalPendapatan = $pendapatan->sum(function($coa) use ($accountData) {
            return $accountData[$coa->kode_akun]['saldo_akhir'] ?? 0;
        });
        
        $totalBeban = $beban->sum(function($coa) use ($accountData) {
            return $accountData[$coa->kode_akun]['saldo_akhir'] ?? 0;
        });
        
        // Calculate diskon penjualan (account 412 or similar)
        $diskonPenjualanCoa = $coas->where('kode_akun', '412')->first();
        $totalDiskonPenjualan = $diskonPenjualanCoa ? ($accountData['412']['saldo_akhir'] ?? 0) : 0;
        
        // Calculate laba kotor dan laba bersih
        $labaKotor = $totalPendapatan - $hppAmount;
        $labaBersih = $labaKotor - $totalBeban;
        
        // ── DETAIL PENJUALAN PER PRODUK ────────────────────────────
        // Breakdown penjualan per produk untuk ditampilkan di bawah COA Penjualan
        $detailPenjualan = \DB::table('penjualan_details as pd')
            ->join('penjualans as p', 'p.id', '=', 'pd.penjualan_id')
            ->join('produks as pr', 'pr.id', '=', 'pd.produk_id')
            ->whereBetween('p.tanggal', [$from, $to])
            ->selectRaw('pr.nama_produk,
                         SUM(pd.jumlah) as total_qty,
                         SUM(pd.jumlah * pd.harga_satuan) as total_pendapatan')
            ->groupBy('pr.id', 'pr.nama_produk')
            ->orderBy('total_pendapatan', 'desc')
            ->get();

        // ── DETAIL HPP PER PRODUK ──────────────────────────────────
        // Breakdown HPP per produk untuk ditampilkan di bawah COA HPP
        $detailHpp = \DB::table('penjualan_details as pd')
            ->join('penjualans as p', 'p.id', '=', 'pd.penjualan_id')
            ->join('produks as pr', 'pr.id', '=', 'pd.produk_id')
            ->whereBetween('p.tanggal', [$from, $to])
            ->selectRaw('pr.nama_produk,
                         SUM(pd.jumlah) as total_qty,
                         SUM(pd.jumlah * pr.harga_jual) as total_hpp')
            ->groupBy('pr.id', 'pr.nama_produk')
            ->having('total_hpp', '>', 0)
            ->orderBy('total_hpp', 'desc')
            ->get();

        // Debug: Log totals
        \Log::info('Laba Rugi Totals', [
            'totalPendapatan' => $totalPendapatan,
            'totalBeban' => $totalBeban,
            'hppAmount' => $hppAmount,
            'labaKotor' => $labaKotor,
            'labaBersih' => $labaBersih,
            'detailPenjualan_count' => $detailPenjualan->count(),
            'detailHpp_count' => $detailHpp->count()
        ]);

        return compact(
            'periode', 'from', 'to',
            'pendapatan', 'beban',
            'totalPendapatan', 'totalBeban',
            'labaKotor', 'labaBersih',
            'hppAmount', 'totalDiskonPenjualan',
            'detailPenjualan', 'detailHpp',
            'accountData'
        ) + [
            'totalHpp' => $hppAmount,
            'getSaldo' => function($coa) use ($accountData) {
                return $accountData[$coa->kode_akun]['saldo_akhir'] ?? 0;
            }
        ];
    }

    public function labaRugi(Request $request)
    {
        $data = $this->prepareLabaRugiData($request);
        return view('akuntansi.laba_rugi', $data);
    }

    /**
     * Export Laporan Laba Rugi ke PDF
     */
    public function labaRugiExportPdf(Request $request)
    {
        $from = $request->get('from');
        $to = $request->get('to');
        $periode = $request->get('periode');

        if ($periode) {
            $tahun = substr($periode, 0, 4);
            $bulan = substr($periode, 5, 2);
            $from  = \Carbon\Carbon::create($tahun, $bulan, 1)->format('Y-m-d');
            $to    = \Carbon\Carbon::create($tahun, $bulan, 1)->endOfMonth()->format('Y-m-d');
        } elseif (!$from || !$to) {
            // If no dates provided, use current month
            $from = now()->startOfMonth()->format('Y-m-d');
            $to = now()->format('Y-m-d');
        }

        // Get all COAs
        $coas = Coa::where('user_id', auth()->id())
            ->orderBy('kode_akun')
            ->get();

        // Calculate mutation for the period
        $mutasi = DB::table('jurnal_umum')
            ->select('coa_id',
                DB::raw('SUM(debit) as total_debit'),
                DB::raw('SUM(kredit) as total_kredit'))
            ->where('user_id', auth()->id())
            ->whereDate('tanggal', '>=', $from)
            ->whereDate('tanggal', '<=', $to)
            ->groupBy('coa_id')
            ->get()
            ->keyBy('coa_id');

        // Build account data
        $accountData = [];
        foreach ($coas as $coa) {
            $m = $mutasi[$coa->id] ?? null;
            $debit = $m ? (float)$m->total_debit : 0;
            $kredit = $m ? (float)$m->total_kredit : 0;

            $first = substr($coa->kode_akun, 0, 1);
            $saldoAkhir = $first === '4' ? ($kredit - $debit) : ($debit - $kredit);

            $accountData[$coa->kode_akun] = [
                'coa' => $coa,
                'saldo_akhir' => $saldoAkhir
            ];
        }

        // Filter revenue accounts (4xxx)
        $revenue = $coas->filter(function($coa) use ($accountData) {
            $first = substr($coa->kode_akun, 0, 1);
            if ($first !== '4') return false;
            $saldo = $accountData[$coa->kode_akun]['saldo_akhir'] ?? 0;
            return $saldo > 0;
        })->sortBy('kode_akun');

        // Get HPP account
        $hppCoa = $coas->first(function($coa) {
            $first = substr($coa->kode_akun, 0, 1);
            if ($first !== '5') return false;

            $isHpp = stripos($coa->nama_akun, 'harga pokok') !== false ||
                     stripos($coa->nama_akun, 'hpp') !== false ||
                     $coa->kode_akun === '56' ||
                     $coa->kode_akun === '560';

            return $isHpp;
        });

        $hppAmount = $hppCoa ? ($accountData[$hppCoa->kode_akun]['saldo_akhir'] ?? 0) : 0;
        $hppAccounts = $hppCoa ? collect([$hppCoa]) : collect([]);

        // Filter expense accounts (5xxx, 6xxx) excluding HPP
        $expense = $coas->filter(function($coa) use ($accountData, $hppCoa) {
            $first = substr($coa->kode_akun, 0, 1);
            if (!in_array($first, ['5', '6'])) return false;

            if ($hppCoa && $coa->id === $hppCoa->id) return false;

            if (stripos($coa->nama_akun, 'harga pokok') !== false ||
                stripos($coa->nama_akun, 'hpp') !== false) {
                return false;
            }

            $saldo = $accountData[$coa->kode_akun]['saldo_akhir'] ?? 0;
            return $saldo > 0;
        })->sortBy('kode_akun');

        // Generate PDF
        $pdf = PDF::loadView('akuntansi.laba-rugi-pdf', compact(
            'from', 'to',
            'revenue', 'hppAmount', 'hppAccounts', 'expense',
            'coas', 'accountData'
        ));

        $pdf->setPaper('A4', 'portrait');

        // Return PDF download
        return $pdf->download('Laporan-Laba-Rugi-' . date('Y-m-d-His') . '.pdf');
    }
}
