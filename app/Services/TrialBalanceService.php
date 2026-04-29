<?php

namespace App\Services;

use App\Models\Coa;
use App\Models\JournalLine;
use App\Models\JournalEntry;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Service untuk menghitung Neraca Saldo berdasarkan Buku Besar
 * 
 * Logika: Neraca saldo adalah ringkasan saldo akhir semua akun dari buku besar.
 * Setiap transaksi sudah diposting ke journal_lines (buku besar).
 * 
 * Formula:
 * - Akun Normal Debit: Saldo Akhir = Saldo Awal + Total Debit - Total Kredit
 * - Akun Normal Kredit: Saldo Akhir = Saldo Awal - Total Debit + Total Kredit
 */
class TrialBalanceService
{
    /**
     * Hitung Neraca Saldo untuk periode tertentu
     * 
     * @param string $startDate Format Y-m-d
     * @param string $endDate Format Y-m-d
     * @return array
     */
    public function calculateTrialBalance($startDate, $endDate)
    {
        // Ambil semua COA yang aktif, diurutkan berdasarkan kode akun
        // PERBAIKAN: Group by kode_akun untuk menghindari duplikasi
        $coas = Coa::select('id', 'kode_akun', 'nama_akun', 'tipe_akun', 'saldo_normal', 'saldo_awal')
            ->orderBy('kode_akun')
            ->get()
            ->groupBy('kode_akun')
            ->map(function ($group) {
                // Ambil COA pertama dari setiap grup kode_akun
                return $group->first();
            });

        $trialBalanceData = [];
        $totalDebit = 0;
        $totalKredit = 0;
        $debugInfo = [];

        foreach ($coas as $coa) {
            // Cek apakah ini akun persediaan yang perlu menggunakan saldo Buku Besar
            $isPersediaan = $this->isPersediaanAccount($coa);
            
            if ($isPersediaan) {
                // UNTUK AKUN PERSEDIAAN: Ambil saldo akhir langsung dari Buku Besar
                $saldoAkhirBukuBesar = $this->getSaldoAkhirFromBukuBesar($coa->id, $endDate);
                $displayBalance = $this->mapSaldoBukuBesarToTrialBalance($saldoAkhirBukuBesar);
                
                // Data untuk akun persediaan
                $accountData = [
                    'kode_akun' => $coa->kode_akun,
                    'nama_akun' => $coa->nama_akun,
                    'tipe_akun' => $coa->tipe_akun,
                    'saldo_awal' => $this->getSaldoAwal($coa, $startDate),
                    'mutasi_debit' => 0, // Tidak relevan untuk persediaan
                    'mutasi_kredit' => 0, // Tidak relevan untuk persediaan
                    'saldo_akhir' => $saldoAkhirBukuBesar,
                    'debit' => $displayBalance['debit'],
                    'kredit' => $displayBalance['kredit'],
                    'is_debit_normal' => $this->isDebitNormalAccount($coa),
                    'source' => 'buku_besar' // Penanda bahwa ini dari Buku Besar
                ];
                
            } else {
                // UNTUK AKUN LAINNYA: Gunakan logika lama (perhitungan periode)
                
                // 1. Ambil saldo awal akun
                $saldoAwal = $this->getSaldoAwal($coa, $startDate);

                // 2. Hitung mutasi periode dari buku besar (journal_lines)
                $mutasiPeriode = $this->getMutasiPeriode($coa->id, $startDate, $endDate);
                $totalDebitPeriode = $mutasiPeriode['total_debit'];
                $totalKreditPeriode = $mutasiPeriode['total_kredit'];

                // 3. Hitung saldo akhir berdasarkan normal balance akun
                $saldoAkhir = $this->calculateSaldoAkhir(
                    $coa, 
                    $saldoAwal, 
                    $totalDebitPeriode, 
                    $totalKreditPeriode
                );

                // 4. Map saldo akhir ke kolom debit/kredit untuk tampilan neraca saldo
                $displayBalance = $this->mapToTrialBalanceColumns($saldoAkhir, $coa);
                
                $accountData = [
                    'kode_akun' => $coa->kode_akun,
                    'nama_akun' => $coa->nama_akun,
                    'tipe_akun' => $coa->tipe_akun,
                    'saldo_awal' => $saldoAwal,
                    'mutasi_debit' => $totalDebitPeriode,
                    'mutasi_kredit' => $totalKreditPeriode,
                    'saldo_akhir' => $saldoAkhir,
                    'debit' => $displayBalance['debit'],
                    'kredit' => $displayBalance['kredit'],
                    'is_debit_normal' => $this->isDebitNormalAccount($coa),
                    'source' => 'periode' // Penanda bahwa ini dari perhitungan periode
                ];
            }

            // Skip akun yang tidak memiliki aktivitas atau saldo
            if ($this->shouldSkipAccount($accountData['saldo_awal'], $accountData['mutasi_debit'], $accountData['mutasi_kredit'], $accountData['saldo_akhir'])) {
                continue;
            }

            $trialBalanceData[] = $accountData;

            // Akumulasi total untuk balance check
            $totalDebit += $displayBalance['debit'];
            $totalKredit += $displayBalance['kredit'];

            // Debug info untuk akun dengan saldo besar
            if (abs($accountData['saldo_akhir']) > 100000 || $displayBalance['debit'] > 100000 || $displayBalance['kredit'] > 100000) {
                $debugInfo[] = [
                    'kode' => $coa->kode_akun,
                    'nama' => $coa->nama_akun,
                    'saldo_awal' => $accountData['saldo_awal'],
                    'saldo_akhir' => $accountData['saldo_akhir'],
                    'debit_display' => $displayBalance['debit'],
                    'kredit_display' => $displayBalance['kredit'],
                    'is_debit_normal' => $this->isDebitNormalAccount($coa),
                    'source' => $accountData['source']
                ];
            }
        }

        // REMOVED: Jurnal penyeimbang otomatis dihapus sesuai permintaan user
        // User ingin neraca saldo seimbang murni dari jurnal yang benar
        $imbalanceWarning = null;

        return [
            'accounts' => $trialBalanceData,
            'total_debit' => $totalDebit,
            'total_kredit' => $totalKredit,
            'is_balanced' => abs($totalDebit - $totalKredit) < 0.01,
            'difference' => $totalDebit - $totalKredit,
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'formatted_period' => Carbon::parse($startDate)->format('d/m/Y') . ' - ' . Carbon::parse($endDate)->format('d/m/Y')
            ],
            'debug_info' => $debugInfo,
            'imbalance_warning' => $imbalanceWarning
        ];
    }

    /**
     * Ambil saldo akhir akun langsung dari Buku Besar (running balance)
     * 
     * Menggunakan logika yang SAMA PERSIS dengan AkuntansiController::bukuBesar()
     * dan view buku-besar.blade.php untuk memastikan konsistensi 100%
     * 
     * @param int $coaId
     * @param string $endDate Format Y-m-d (sampai tanggal berapa)
     * @return float
     */
    private function getSaldoAkhirFromBukuBesar($coaId, $endDate)
    {
        $coa = Coa::find($coaId);
        if (!$coa) {
            return 0;
        }

        // 1. Ambil saldo awal menggunakan logika yang SAMA dengan AkuntansiController
        $kodeAkun = $coa->kode_akun;
        $bahanBakuCoas = ['1101', '114', '1141', '1142', '1143'];
        $bahanPendukungCoas = ['1150', '1151', '1152', '1153', '1154', '1155', '1156', '1157', '115'];
        
        // Untuk akun persediaan, gunakan saldo awal dari inventory
        if (in_array($kodeAkun, $bahanBakuCoas) || in_array($kodeAkun, $bahanPendukungCoas)) {
            $saldoAwal = $this->getInventorySaldoAwal($kodeAkun);
        } else {
            $saldoAwal = (float)($coa->saldo_awal ?? 0);
        }

        // 2. Ambil total debit dan kredit sampai tanggal tertentu menggunakan query yang SAMA
        $journalLines = DB::table('journal_entries as je')
            ->leftJoin('journal_lines as jl', 'jl.journal_entry_id', '=', 'je.id')
            ->leftJoin('coas', 'coas.id', '=', 'jl.coa_id')
            ->where(function($q) {
                $q->where('jl.debit', '>', 0)
                  ->orWhere('jl.credit', '>', 0);
            })
            ->where('coas.kode_akun', $kodeAkun)
            ->where('je.tanggal', '<=', $endDate)
            ->orderBy('je.tanggal','asc')
            ->orderBy('je.id','asc')
            ->orderBy('jl.id','asc')
            ->get();

        // 3. Hitung saldo akhir menggunakan rumus yang SAMA dengan AkuntansiController
        $totalDebit = $journalLines->sum('debit');
        $totalKredit = $journalLines->sum('credit');
        $saldoAkhir = $saldoAwal + $totalDebit - $totalKredit;

        return $saldoAkhir;
    }

    /**
     * Helper method untuk mendapatkan saldo awal persediaan
     * (copy dari AkuntansiController::getInventorySaldoAwal)
     */
    private function getInventorySaldoAwal($kodeAkun)
    {
        $bahanBakuCoas = ['1101', '114', '1141', '1142', '1143'];
        $bahanPendukungCoas = ['1150', '1151', '1152', '1153', '1154', '1155', '1156', '1157', '115'];
        
        $saldoAwal = 0;
        
        // Untuk akun bahan baku
        if (in_array($kodeAkun, $bahanBakuCoas)) {
            if (in_array($kodeAkun, ['1101', '114'])) {
                // Parent accounts - return 0 (not used directly)
                $saldoAwal = 0;
            } else {
                // Specific child account
                $saldoAwal = DB::table('bahan_bakus')
                    ->where('coa_persediaan_id', $kodeAkun)
                    ->where('saldo_awal', '>', 0)
                    ->sum(DB::raw('saldo_awal * harga_satuan'));
            }
        }
        
        // Untuk akun bahan pendukung
        if (in_array($kodeAkun, $bahanPendukungCoas)) {
            if ($kodeAkun === '115') {
                // Parent account - return 0 (not used directly)
                $saldoAwal = 0;
            } else {
                // Specific child account
                $saldoAwal = DB::table('bahan_pendukungs')
                    ->where('coa_persediaan_id', $kodeAkun)
                    ->where('saldo_awal', '>', 0)
                    ->sum(DB::raw('saldo_awal * harga_satuan'));
            }
        }
        
        return (float)$saldoAwal;
    }

    /**
     * Map saldo akhir dari Buku Besar ke kolom debit/kredit Neraca Saldo
     * 
     * Logika sesuai permintaan user:
     * - Jika saldo akhir >= 0 → tampil di kolom DEBIT
     * - Jika saldo akhir < 0 → tampil di kolom KREDIT (nilai absolut)
     */
    private function mapSaldoBukuBesarToTrialBalance($saldoAkhirBukuBesar)
    {
        if ($saldoAkhirBukuBesar >= 0) {
            // Saldo positif → tampil di DEBIT
            return [
                'debit' => $saldoAkhirBukuBesar,
                'kredit' => 0
            ];
        } else {
            // Saldo negatif → tampil di KREDIT (nilai absolut)
            return [
                'debit' => 0,
                'kredit' => abs($saldoAkhirBukuBesar)
            ];
        }
    }

    /**
     * Cek apakah akun adalah akun persediaan yang perlu menggunakan saldo Buku Besar
     * DISABLED: Untuk mengatasi ketidakseimbangan neraca saldo
     */
    /**
     * Cek apakah akun adalah akun persediaan yang perlu menggunakan saldo Buku Besar
     * DISABLED: Untuk mengatasi ketidakseimbangan neraca saldo, semua akun menggunakan logika yang sama
     */
    private function isPersediaanAccount($coa)
    {
        // DISABLED: Semua akun menggunakan logika periode yang konsisten
        return false;
        
        /*
        $kodeAkun = $coa->kode_akun;
        
        // Daftar akun persediaan yang perlu menggunakan saldo akhir Buku Besar
        $persediaanCodes = [
            '1141', // Pers. Bahan Baku ayam potong
            '1142', // Pers. Bahan Baku ayam kampung
            '1143', // Pers. Bahan Baku bebek
            '1152', // Pers. Bahan Pendukung Tepung Terigu
            '1153', // Pers. Bahan Pendukung Tepung Maizena
            '1154', // Pers. Bahan Pendukung Lada
            '1155', // Pers. Bahan Pendukung Bubuk Kaldu
            '1156', // Pers. Bahan Pendukung Bubuk Bawang Putih
            '1157'  // Pers. Bahan Pendukung Kemasan
        ];
        
        return in_array($kodeAkun, $persediaanCodes);
        */
    }

    /**
     * Ambil saldo awal akun
     * 
     * Untuk akun persediaan, gunakan saldo dari inventory (sama dengan AkuntansiController)
     * Untuk akun lainnya, gunakan saldo_awal dari COA
     */
    private function getSaldoAwal($coa, $startDate)
    {
        $kodeAkun = $coa->kode_akun;
        
        // Untuk akun persediaan, gunakan logika yang sama dengan AkuntansiController
        $bahanBakuCoas = ['1101', '114', '1141', '1142', '1143'];
        $bahanPendukungCoas = ['1150', '1151', '1152', '1153', '1154', '1155', '1156', '1157', '115'];
        
        if (in_array($kodeAkun, $bahanBakuCoas) || in_array($kodeAkun, $bahanPendukungCoas)) {
            return $this->getInventorySaldoAwal($kodeAkun);
        } else {
            return (float) ($coa->saldo_awal ?? 0);
        }
    }

    /**
     * Ambil mutasi periode dari buku besar (journal_lines)
     * 
     * @param int $coaId
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    private function getMutasiPeriode($coaId, $startDate, $endDate)
    {
        // PERBAIKAN: Gunakan kode_akun untuk menghindari masalah duplikasi COA
        $coa = Coa::find($coaId);
        if (!$coa) {
            return ['total_debit' => 0, 'total_kredit' => 0];
        }
        
        $mutasi = JournalLine::join('journal_entries', 'journal_lines.journal_entry_id', '=', 'journal_entries.id')
            ->join('coas', 'journal_lines.coa_id', '=', 'coas.id')
            ->where('coas.kode_akun', $coa->kode_akun) // Gunakan kode_akun, bukan coa_id
            ->whereBetween('journal_entries.tanggal', [$startDate, $endDate])
            ->selectRaw('
                COALESCE(SUM(journal_lines.debit), 0) as total_debit,
                COALESCE(SUM(journal_lines.credit), 0) as total_kredit
            ')
            ->first();

        return [
            'total_debit' => (float) ($mutasi->total_debit ?? 0),
            'total_kredit' => (float) ($mutasi->total_kredit ?? 0)
        ];
    }

    /**
     * Hitung saldo akhir berdasarkan normal balance akun
     * 
     * Formula akuntansi:
     * - Akun Normal Debit (Aset, Beban): Saldo Akhir = Saldo Awal + Debit - Kredit
     * - Akun Normal Kredit (Kewajiban, Modal, Pendapatan): Saldo Akhir = Saldo Awal - Debit + Kredit
     */
    private function calculateSaldoAkhir($coa, $saldoAwal, $totalDebit, $totalKredit)
    {
        $isDebitNormal = $this->isDebitNormalAccount($coa);

        if ($isDebitNormal) {
            // Akun normal debit: Saldo Akhir = Saldo Awal + Debit - Kredit
            return $saldoAwal + $totalDebit - $totalKredit;
        } else {
            // Akun normal kredit: Saldo Akhir = Saldo Awal - Debit + Kredit
            return $saldoAwal - $totalDebit + $totalKredit;
        }
    }

    /**
     * Tentukan apakah akun memiliki normal balance debit
     * 
     * Berdasarkan standar akuntansi:
     * - Aset (1xx): Normal Debit, KECUALI Akumulasi Penyusutan (12x) yang Normal Kredit
     * - Kewajiban (2xx): Normal Kredit
     * - Modal/Ekuitas (3xx): Normal Kredit
     * - Pendapatan (4xx): Normal Kredit
     * - Beban (5xx, 6xx): Normal Debit
     */
    private function isDebitNormalAccount($coa)
    {
        // Prioritas 1: Berdasarkan kode akun (digit pertama)
        $firstDigit = substr($coa->kode_akun, 0, 1);
        $firstTwoDigits = substr($coa->kode_akun, 0, 2);
        
        // Akun Akumulasi Penyusutan spesifik (120, 124, 126) adalah KREDIT normal meskipun aset
        if (in_array($coa->kode_akun, ['120', '124', '126'])) {
            return false; // Akumulasi Penyusutan = Kredit normal
        }
        
        // Akun dengan normal balance debit
        $debitNormalDigits = ['1', '5', '6']; // Aset (kecuali akumulasi), Beban
        
        if (in_array($firstDigit, $debitNormalDigits)) {
            return true;
        }

        // Prioritas 2: Fallback ke tipe_akun jika ada
        if (!empty($coa->tipe_akun)) {
            $tipeAkun = strtoupper($coa->tipe_akun);
            $debitNormalTypes = ['ASET', 'ASSET', 'BEBAN', 'EXPENSE'];
            
            // Kecuali jika nama akun mengandung "akumulasi" atau "penyusutan"
            $namaAkun = strtoupper($coa->nama_akun);
            if (strpos($namaAkun, 'AKUMULASI') !== false || strpos($namaAkun, 'PENYUSUTAN') !== false) {
                return false; // Akumulasi Penyusutan = Kredit normal
            }
            
            return in_array($tipeAkun, $debitNormalTypes);
        }

        // Default: Kredit normal jika tidak bisa ditentukan
        return false;
    }

    /**
     * Map saldo akhir ke kolom debit/kredit untuk tampilan neraca saldo
     * 
     * Aturan tampilan neraca saldo:
     * - Jika saldo akhir positif dan akun normal debit → tampil di kolom debit
     * - Jika saldo akhir positif dan akun normal kredit → tampil di kolom kredit
     * - Jika saldo akhir negatif → tampil di sisi berlawanan (saldo abnormal)
     * - Jika saldo akhir = 0 → tidak tampil di kedua kolom
     */
    private function mapToTrialBalanceColumns($saldoAkhir, $coa)
    {
        $debit = 0;
        $kredit = 0;

        // Jika saldo = 0, tidak perlu ditampilkan
        if ($saldoAkhir == 0) {
            return ['debit' => 0, 'kredit' => 0];
        }

        $isDebitNormal = $this->isDebitNormalAccount($coa);

        if ($saldoAkhir > 0) {
            // Saldo positif - tampil sesuai normal balance
            if ($isDebitNormal) {
                $debit = $saldoAkhir;
            } else {
                $kredit = $saldoAkhir;
            }
        } else {
            // Saldo negatif (abnormal) - tampil di sisi berlawanan
            $nilaiAbsolut = abs($saldoAkhir);
            if ($isDebitNormal) {
                $kredit = $nilaiAbsolut; // Akun debit normal tapi saldo negatif → tampil di kredit
            } else {
                $debit = $nilaiAbsolut;  // Akun kredit normal tapi saldo negatif → tampil di debit
            }
        }

        return [
            'debit' => $debit,
            'kredit' => $kredit
        ];
    }

    /**
     * Tentukan apakah akun harus di-skip dari tampilan neraca saldo
     * 
     * Tampilkan akun jika:
     * - Memiliki saldo awal tidak nol, ATAU
     * - Memiliki mutasi debit/kredit di periode ini, ATAU  
     * - Memiliki saldo akhir tidak nol
     */
    private function shouldSkipAccount($saldoAwal, $totalDebit, $totalKredit, $saldoAkhir)
    {
        // Tampilkan jika ada aktivitas atau saldo
        return $saldoAwal == 0 && $totalDebit == 0 && $totalKredit == 0 && abs($saldoAkhir) < 0.01;
    }

    /**
     * Validasi keseimbangan neraca saldo
     * 
     * @param array $trialBalanceData
     * @return array
     */
    public function validateBalance($trialBalanceData)
    {
        $totalDebit = $trialBalanceData['total_debit'];
        $totalKredit = $trialBalanceData['total_kredit'];
        $difference = $totalDebit - $totalKredit;
        $isBalanced = abs($difference) < 0.01;

        return [
            'is_balanced' => $isBalanced,
            'total_debit' => $totalDebit,
            'total_kredit' => $totalKredit,
            'difference' => $difference,
            'status_message' => $isBalanced 
                ? 'Neraca saldo seimbang - Total debit sama dengan total kredit'
                : 'Neraca saldo tidak seimbang - Terdapat selisih sebesar Rp ' . number_format(abs($difference), 0, ',', '.')
        ];
    }

    // REMOVED: createOpeningBalanceJournal method dihapus sesuai permintaan user
    // User tidak ingin jurnal penyeimbang otomatis
}