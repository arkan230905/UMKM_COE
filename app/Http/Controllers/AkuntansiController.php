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

        // Map ref_type to tipe_referensi for backward compatibility
        $mappedRefType = $refType;
        if ($refType === 'purchase') {
            $mappedRefType = 'pembelian';
        } elseif ($refType === 'sale') {
            $mappedRefType = 'penjualan';
        }

        // Convert ref_id to referensi format if needed
        $mappedRefId = $refId;
        if ($refId && is_numeric($refId)) {
            // If ref_id is numeric, try to find the actual reference number
            if ($refType === 'purchase') {
                $pembelian = \App\Models\Pembelian::find($refId);
                if ($pembelian) {
                    $mappedRefId = $pembelian->nomor_pembelian;
                }
            } elseif ($refType === 'sale') {
                $penjualan = \App\Models\Penjualan::find($refId);
                if ($penjualan) {
                    $mappedRefId = $penjualan->nomor_penjualan;
                }
            }
        }

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
->where(function($q) {
                $q->where('coas.user_id', auth()->id())
                  ->orWhereNull('coas.user_id');
            })
            ->orderBy('je.tanggal','asc')
            ->orderBy('je.created_at','asc')
            ->orderBy('je.id','asc')
            ->orderBy('jl.id','asc');
            
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
        
        // TAMBAHAN: Ambil data dari tabel jurnal_umum (untuk penyusutan dan transaksi lain)
        // Hanya ambil yang tidak ada di journal_entries untuk menghindari duplikasi
        $jurnalUmumQuery = \DB::table('jurnal_umum as ju')
            ->leftJoin('coas', 'coas.id', '=', 'ju.coa_id')
            ->select([
                'ju.id',
                'ju.tanggal',
                'ju.coa_id',
                'ju.debit',
                'ju.kredit',
                'ju.keterangan',
                'ju.tipe_referensi',
                'ju.referensi',
                'coas.kode_akun',
                'coas.nama_akun',
                'coas.tipe_akun',
                'ju.created_at',
                \DB::raw("'ju_' as ref_type"),
                \DB::raw('NULL as ref_id')
            ])
            ->where(function($q) {
                $q->where('ju.debit', '>', 0)
                  ->orWhere('ju.kredit', '>', 0);
            })
// Include all relevant transaction types including purchase
            ->whereIn('ju.tipe_referensi', [
                'penyusutan', 'adjustment', 'manual', 'pembelian' // Include purchase journals
            ])
            ->where(function($q) {
                $q->where('coas.user_id', auth()->id())
                  ->orWhereNull('coas.user_id');
            })
            // Exclude production entries that are already in journal_entries
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
        
        // Handle ref_type filtering for purchase transactions
        if ($refType) { 
            if ($refType === 'purchase') {
                $jurnalUmumQuery->where('ju.tipe_referensi', 'pembelian');
            } else {
                $jurnalUmumQuery->where('ju.tipe_referensi', $refType);
            }
        }
        
        // Handle ref_id filtering for purchase transactions
        if ($refId && $refType === 'purchase') {
            // Get the pembelian nomor_pembelian for filtering
            $pembelian = \App\Models\Pembelian::find($refId);
            if ($pembelian) {
                $jurnalUmumQuery->where('ju.referensi', $pembelian->nomor_pembelian);
            }
        }
        
        if ($accountCode) { 
            $jurnalUmumQuery->where('coas.kode_akun', $accountCode);
        }
        
        $jurnalUmumResults = $jurnalUmumQuery->get();
        
        // Group jurnal_umum results by date and keterangan untuk menggabungkan debit/kredit
        $jurnalUmumGrouped = $jurnalUmumResults->groupBy(function($item) {
            return $item->tanggal . '|' . $item->keterangan;
        });
        
        foreach ($jurnalUmumGrouped as $key => $group) {
            $firstItem = $group->first();
            
            $entry = (object) [
                'id' => 'ju_' . $firstItem->id, // Prefix untuk membedakan dengan journal_entries
                'tanggal' => $firstItem->tanggal,
                'created_at' => $firstItem->created_at,
                'ref_type' => $firstItem->ref_type,
                'ref_id' => null,
                'memo' => $firstItem->keterangan,
                'lines' => $group->map(function($item) {
                    return (object) [
                        'id' => $item->id,
                        'debit' => $item->debit,
                        'credit' => $item->kredit,
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
                ->where('coas.kode_akun', $accountCode)
                ]);
            }

            $totalDebit = $journalLines->sum('debit');
            $totalKredit = $journalLines->sum('kredit');
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
            ->where('user_id', auth()->id()) // MULTI-TENANT: Filter by user_id
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

        $tahun = substr($periode, 0, 4);
        $bulan = substr($periode, 5, 2);
        $from  = \Carbon\Carbon::create($tahun, $bulan, 1)->format('Y-m-d');
        $to    = \Carbon\Carbon::create($tahun, $bulan, 1)->endOfMonth()->format('Y-m-d');

        $userId = auth()->id();

        // Ambil mutasi periode dari journal_lines (debit & kredit per coa_id)
        $mutasi = \DB::table('journal_lines as jl')
            ->join('journal_entries as je', 'je.id', '=', 'jl.journal_entry_id')
            ->join('coas as c', 'c.id', '=', 'jl.coa_id')
            ->whereBetween('je.tanggal', [$from, $to])
            ->where(function($q) use ($userId) {
                $q->where('c.user_id', $userId)->orWhereNull('c.user_id');
            })
            ->selectRaw('jl.coa_id, SUM(jl.debit) as total_debit, SUM(jl.credit) as total_kredit')
            ->groupBy('jl.coa_id')
            ->get()
            ->keyBy('coa_id');

        // Ambil COA yang punya mutasi di periode ini
        $coaIds = $mutasi->keys()->toArray();
        $coas = \App\Models\Coa::withoutGlobalScopes()
            ->whereIn('id', $coaIds)
            ->orderBy('kode_akun')
            ->get();

        // Hitung net balance per COA
        // Pendapatan (4xxx): saldo normal kredit → net = kredit - debit
        // HPP & Beban (5xxx): saldo normal debit → net = debit - kredit
        $getSaldo = function($coa) use ($mutasi) {
            $m      = $mutasi[$coa->id] ?? null;
            $debit  = $m ? (float)$m->total_debit  : 0;
            $kredit = $m ? (float)$m->total_kredit : 0;
            $first  = substr($coa->kode_akun, 0, 1);
            return $first === '4' ? ($kredit - $debit) : ($debit - $kredit);
        };

        // ── PENDAPATAN: kode 4xxx, saldo normal kredit ────────────────
        $pendapatan = $coas->filter(function($coa) use ($getSaldo) {
            return substr($coa->kode_akun, 0, 1) === '4' && $getSaldo($coa) > 0;
        })->sortBy('kode_akun')->values();

        $totalPendapatan = $pendapatan->sum(fn($c) => $getSaldo($c));

        // ── DISKON PENJUALAN: kode 4xxx, saldo normal debit (kontra-revenue) ──
        // Akun seperti "Diskon Penjualan" punya saldo debit → pengurang pendapatan
        $diskonPenjualan = $coas->filter(function($coa) use ($mutasi) {
            if (substr($coa->kode_akun, 0, 1) !== '4') return false;
            $m     = $mutasi[$coa->id] ?? null;
            $debit = $m ? (float)$m->total_debit  : 0;
            $kredit = $m ? (float)$m->total_kredit : 0;
            // Kontra-revenue: debit > kredit (saldo debit)
            return $debit > $kredit;
        })->sortBy('kode_akun')->values();

        $totalDiskonPenjualan = $diskonPenjualan->sum(function($coa) use ($mutasi) {
            $m = $mutasi[$coa->id] ?? null;
            return $m ? ((float)$m->total_debit - (float)$m->total_kredit) : 0;
        });

        // Pendapatan bersih = pendapatan bruto - diskon penjualan
        $totalPendapatanBersih = $totalPendapatan - $totalDiskonPenjualan;

        // ── HPP: kode 5xxx DAN nama mengandung "HPP" atau "Harga Pokok" ──
        $hpp = $coas->filter(function($coa) use ($getSaldo) {
            if (substr($coa->kode_akun, 0, 1) !== '5') return false;
            $nama = strtolower($coa->nama_akun);
            $isHpp = str_contains($nama, 'hpp') || str_contains($nama, 'harga pokok');
            return $isHpp && $getSaldo($coa) > 0;
        })->sortBy('kode_akun')->values();

        $totalHpp = $hpp->sum(fn($c) => $getSaldo($c));

        // ── LABA KOTOR ─────────────────────────────────────────────
        $labaKotor = $totalPendapatanBersih - $totalHpp;

        // ── BEBAN OPERASIONAL: kode 5xxx tapi BUKAN HPP ───────────
        // (BBB, BTKL, BOP, dll — semua yang bukan HPP produk)
        $beban = $coas->filter(function($coa) use ($getSaldo) {
            if (substr($coa->kode_akun, 0, 1) !== '5') return false;
            $nama = strtolower($coa->nama_akun);
            $isHpp = str_contains($nama, 'hpp') || str_contains($nama, 'harga pokok');
            return !$isHpp && $getSaldo($coa) > 0;
        })->sortBy('kode_akun')->values();

        $totalBeban = $beban->sum(fn($c) => $getSaldo($c));

        // ── LABA BERSIH ────────────────────────────────────────────
        $labaBersih = $labaKotor - $totalBeban;

        // ── DETAIL PENJUALAN PER PRODUK ────────────────────────────
        // Breakdown penjualan per produk untuk ditampilkan di bawah COA Penjualan
        $detailPenjualan = \DB::table('penjualan_details as pd')
            ->join('penjualans as p', 'p.id', '=', 'pd.penjualan_id')
            ->join('produks as pr', 'pr.id', '=', 'pd.produk_id')
            ->whereBetween('p.tanggal', [$from, $to])
            ->selectRaw('pr.nama_produk,
                         SUM(pd.jumlah) as total_qty,
                         SUM(pd.subtotal) as total_pendapatan')
            ->groupBy('pr.id', 'pr.nama_produk')
            ->orderBy('total_pendapatan', 'desc')
            ->get();

        // ── DETAIL HPP PER PRODUK ──────────────────────────────────
        // Sudah ada di $hpp (per COA HPP produk), tapi tambahkan qty dari penjualan
        $detailHpp = \DB::table('penjualan_details as pd')
            ->join('penjualans as p', 'p.id', '=', 'pd.penjualan_id')
            ->join('produks as pr', 'pr.id', '=', 'pd.produk_id')
            ->whereBetween('p.tanggal', [$from, $to])
            ->selectRaw('pr.nama_produk,
                         SUM(pd.jumlah) as total_qty,
                         SUM(pd.jumlah * COALESCE(pr.hpp, pr.harga_bom, 0)) as total_hpp')
            ->groupBy('pr.id', 'pr.nama_produk')
            ->having('total_hpp', '>', 0)
            ->orderBy('total_hpp', 'desc')
            ->get();

        return view('akuntansi.laba_rugi', compact(
            'periode', 'from', 'to',
            'pendapatan', 'hpp', 'beban',
            'diskonPenjualan', 'totalDiskonPenjualan',
            'totalPendapatan', 'totalPendapatanBersih', 'totalHpp', 'totalBeban',
            'labaKotor', 'labaBersih',
            'getSaldo',
            'detailPenjualan', 'detailHpp'
        ));
    }
}
