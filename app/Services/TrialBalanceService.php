<?php

namespace App\Services;

use App\Models\Coa;
use App\Models\JurnalUmum;
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
        // Cek apakah rantai posting terputus DULU sebelum iterasi
        $periodeStr = Carbon::parse($startDate)->format('Y-m');
        $hasPostedBefore = DB::table('coa_period_balances')->where('user_id', auth()->id())->exists();
        $isChainBroken = false;
        
        if ($hasPostedBefore) {
            $hasCurrentPeriodBalances = DB::table('coa_period_balances as cpb')
                ->join('coa_periods as cp', 'cpb.period_id', '=', 'cp.id')
                ->where('cpb.user_id', auth()->id())
                ->where('cp.periode', $periodeStr)
                ->exists();
                
            if (!$hasCurrentPeriodBalances) {
                $isChainBroken = true;
            }
        }

        // Cek apakah periode ini sudah diposting ke bulan berikutnya
        $nextMonthStr = Carbon::parse($startDate)->addMonth()->format('Y-m');
        $isPosted = DB::table('coa_period_balances as cpb')
            ->join('coa_periods as cp', 'cpb.period_id', '=', 'cp.id')
            ->where('cpb.user_id', auth()->id())
            ->where('cp.periode', $nextMonthStr)
            ->exists();

        // Ambil semua COA yang aktif, diurutkan berdasarkan kode akun
        $coas = Coa::select('id', 'kode_akun', 'nama_akun', 'tipe_akun', 'saldo_normal', 'saldo_awal')
            ->where('user_id', auth()->id())
            ->orderBy('kode_akun')
            ->get()
            ->groupBy('kode_akun')
            ->map(function ($group) {
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
                $saldoAkhirBukuBesar = $this->getSaldoAkhirFromBukuBesar($coa->id, $endDate);
                $displayBalance = $this->mapSaldoBukuBesarToTrialBalance($saldoAkhirBukuBesar);
                
                $accountData = [
                    'kode_akun' => $coa->kode_akun,
                    'nama_akun' => $coa->nama_akun,
                    'tipe_akun' => $coa->tipe_akun,
                    'saldo_awal' => $this->getSaldoAwal($coa, $startDate),
                    'mutasi_debit' => 0,
                    'mutasi_kredit' => 0,
                    'saldo_akhir' => $saldoAkhirBukuBesar,
                    'debit' => $displayBalance['debit'],
                    'kredit' => $displayBalance['kredit'],
                    'is_debit_normal' => $this->isDebitNormalAccount($coa),
                    'source' => 'buku_besar'
                ];
                
            } else {
                $saldoAwal = $this->getSaldoAwal($coa, $startDate);
                $mutasiPeriode = $this->getMutasiPeriode($coa->id, $startDate, $endDate);
                $totalDebitPeriode = $mutasiPeriode['total_debit'];
                $totalKreditPeriode = $mutasiPeriode['total_kredit'];
                $saldoAkhir = $this->calculateSaldoAkhir($coa, $saldoAwal, $totalDebitPeriode, $totalKreditPeriode);
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
                    'source' => 'periode'
                ];
            }

            // Skip akun yang tidak memiliki aktivitas atau saldo
            if ($this->shouldSkipAccount($accountData['saldo_awal'], $accountData['mutasi_debit'], $accountData['mutasi_kredit'], $accountData['saldo_akhir'], $isChainBroken)) {
                continue;
            }

            $trialBalanceData[] = $accountData;

            $totalDebit += $displayBalance['debit'];
            $totalKredit += $displayBalance['kredit'];

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
            'imbalance_warning' => $imbalanceWarning,
            'is_chain_broken' => $isChainBroken,
            'is_posted' => $isPosted
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

        // 2. Ambil total debit dan kredit sampai tanggal tertentu menggunakan jurnal_umum table
        $journalLines = DB::table('jurnal_umum as ju')
            ->leftJoin('coas', 'coas.id', '=', 'ju.coa_id')
            ->where('ju.user_id', auth()->id()) // MULTI-TENANT: Filter by user_id
            ->where(function($q) use ($coa) {
                $q->where('coas.kode_akun', $coa->kode_akun)
                  ->orWhere('coas.id', $coa->id);
            })
            ->where('ju.tanggal', '<=', $endDate)
            ->select([
                'ju.debit',
                'ju.kredit',
                'coas.kode_akun',
                'coas.id as coa_id'
            ])
            ->get();

        // 3. Hitung saldo akhir menggunakan rumus yang SAMA dengan AkuntansiController
        $totalDebit = $journalLines->sum('debit');
        $totalKredit = $journalLines->sum('kredit');
        $saldoAkhir = $saldoAwal + $totalDebit - $totalKredit;

        return $saldoAkhir;
    }

    /**
     * Helper method untuk mendapatkan saldo awal persediaan
     * (copy dari AkuntansiController::getInventorySaldoAwal)
     */
    private function getInventorySaldoAwal($kodeAkun)
    {
        // DISABLED - Logika ini dinonaktifkan untuk mencegah perhitungan saldo awal dari bahan
        // Bahan baku dan bahan pendukung tidak lagi berkontribusi ke saldo awal COA
        
        \Log::info("Skipping inventory saldo awal calculation in TrialBalanceService", [
            'kode_akun' => $kodeAkun,
            'reason' => 'Inventory saldo awal calculation disabled for bahan baku/pendukung'
        ]);
        
        return 0; // Selalu return 0 agar tidak ada kontribusi dari bahan
        
        // COMMENTED OUT - Logika lama yang menghitung dari bahan
        /*
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
        */
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

    private function getSaldoAwal($coa, $startDate)
    {
        $kodeAkun = $coa->kode_akun;
        
        $bahanBakuCoas = ['1101', '114', '1141', '1142', '1143'];
        $bahanPendukungCoas = ['1150', '1151', '1152', '1153', '1154', '1155', '1156', '1157', '115'];
        
        if (in_array($kodeAkun, $bahanBakuCoas) || in_array($kodeAkun, $bahanPendukungCoas)) {
            return $this->getInventorySaldoAwal($kodeAkun);
        }
        
        $periodeStr = Carbon::parse($startDate)->format('Y-m');

        // Cek saldo awal spesifik untuk periode ini dari posting bulan sebelumnya
        $periodBalance = DB::table('coa_period_balances as cpb')
            ->join('coa_periods as cp', 'cpb.period_id', '=', 'cp.id')
            ->where('cpb.user_id', auth()->id())
            ->where('cp.periode', $periodeStr)
            ->where('cpb.kode_akun', $kodeAkun)
            ->first();

        if ($periodBalance) {
            return (float) $periodBalance->saldo_awal;
        }

        // Jika tidak ada di coa_period_balances, cek apakah user PERNAH melakukan posting
        $hasPostedBefore = DB::table('coa_period_balances')
            ->where('user_id', auth()->id())
            ->exists();
            
        if ($hasPostedBefore) {
            // Jika user pernah posting (misal Juni), tapi periode ini (misal Agustus) tidak ada saldonya,
            // berarti bulan sebelumnya (Juli) BELUM diposting. Saldo awal = 0 karena rantai terputus.
            return 0; 
        }

        // Jika belum pernah ada posting sama sekali di sistem untuk user ini,
        // gunakan saldo awal default dari tabel coas (seeder awal).
        return (float) ($coa->saldo_awal ?? 0);
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
        
        $mutasi = DB::table('jurnal_umum as ju')
            ->join('coas', 'ju.coa_id', '=', 'coas.id')
            ->where('ju.user_id', auth()->id()) // MULTI-TENANT: Filter by user_id
            ->where('coas.kode_akun', $coa->kode_akun) // Gunakan kode_akun, bukan coa_id
            ->whereBetween('ju.tanggal', [$startDate, $endDate])
            ->selectRaw('
                COALESCE(SUM(ju.debit), 0) as total_debit,
                COALESCE(SUM(ju.kredit), 0) as total_kredit
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
     * PERBAIKAN FINAL: Gunakan formula UNIVERSAL yang SAMA PERSIS dengan Buku Besar
     * untuk memastikan konsistensi 100% antara Buku Besar dan Neraca Saldo
     * 
     * Formula UNIVERSAL untuk SEMUA akun (sama dengan AkuntansiController::bukuBesar):
     * Saldo Akhir = Saldo Awal + Total Debit - Total Kredit
     * 
     * Tidak ada perbedaan formula antara akun debit normal dan kredit normal.
     * Semua akun menggunakan formula yang sama seperti di Buku Besar.
     */
    private function calculateSaldoAkhir($coa, $saldoAwal, $totalDebit, $totalKredit)
    {
        // PERBAIKAN FINAL: Gunakan formula universal yang SAMA PERSIS dengan Buku Besar
        // Saldo Akhir = Saldo Awal + Total Debit - Total Kredit
        // Ini memastikan angka Neraca Saldo SAMA PERSIS dengan Buku Besar
        return $saldoAwal + $totalDebit - $totalKredit;
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
     * LOGIKA PERBAIKAN untuk Neraca Saldo:
     * 
     * Karena kita menggunakan formula UNIVERSAL untuk semua akun:
     * Saldo Akhir = Saldo Awal + Total Debit - Total Kredit
     * 
     * Maka interpretasi saldo akhir untuk penempatan di Neraca Saldo:
     * 
     * AKUN NORMAL DEBIT (Aset, Beban):
     * - Saldo Akhir > 0 → Tampil di kolom DEBIT (normal)
     * - Saldo Akhir < 0 → Tampil di kolom KREDIT dengan nilai absolut (abnormal)
     * 
     * AKUN NORMAL KREDIT (Kewajiban, Modal, Pendapatan):
     * - Saldo Akhir > 0 → Tampil di kolom KREDIT (abnormal - lebih bayar utang)
     * - Saldo Akhir < 0 → Tampil di kolom KREDIT dengan nilai absolut (normal - ada utang)
     * 
     * Contoh Utang Usaha (Normal Kredit):
     * - Total Debit = 1.490.000, Total Kredit = 1.589.000
     * - Saldo Akhir = 0 + 1.490.000 - 1.589.000 = -99.000
     * - Karena negatif dan akun normal kredit → Tampil di KREDIT = 99.000 ✓
     * 
     * Neraca Saldo TIDAK BOLEH menampilkan nilai negatif.
     * Semua nilai harus positif dan ditempatkan di kolom yang tepat.
     */
    private function mapToTrialBalanceColumns($saldoAkhir, $coa)
    {
        $debit = 0;
        $kredit = 0;

        // Jika saldo = 0, tidak perlu ditampilkan
        if (abs($saldoAkhir) < 0.01) {
            return ['debit' => 0, 'kredit' => 0];
        }

        $isDebitNormal = $this->isDebitNormalAccount($coa);

        if ($saldoAkhir > 0) {
            // Saldo positif
            if ($isDebitNormal) {
                // Akun normal debit dengan saldo positif → tampil di DEBIT (normal)
                $debit = $saldoAkhir;
            } else {
                // Akun normal kredit dengan saldo positif → tampil di KREDIT (abnormal tapi tetap di kredit)
                // Ini terjadi jika pembayaran utang melebihi utang yang ada
                $kredit = $saldoAkhir;
            }
        } else {
            // Saldo negatif - ambil nilai absolut
            $nilaiAbsolut = abs($saldoAkhir);
            
            if ($isDebitNormal) {
                // Akun normal debit dengan saldo negatif → tampil di KREDIT (abnormal)
                // Contoh: Kas minus (overdraft)
                $kredit = $nilaiAbsolut;
            } else {
                // Akun normal kredit dengan saldo negatif → tampil di KREDIT (normal)
                // Contoh: Utang Usaha dengan saldo -99.000 → tampil di Kredit 99.000 ✓
                $kredit = $nilaiAbsolut;
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
     * - Memiliki saldo akhir tidak nol, ATAU
     * - Rantai posting terputus (untuk menampilkan semua akun dengan saldo 0)
     */
    private function shouldSkipAccount($saldoAwal, $totalDebit, $totalKredit, $saldoAkhir, $isChainBroken = false)
    {
        if ($isChainBroken) {
            return false; // Jangan skip akun apapun jika rantai terputus
        }
        
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