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
        $coas = Coa::select('id', 'kode_akun', 'nama_akun', 'tipe_akun', 'saldo_normal', 'saldo_awal')
            ->orderBy('kode_akun')
            ->get();

        $trialBalanceData = [];
        $totalDebit = 0;
        $totalKredit = 0;

        foreach ($coas as $coa) {
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

            // Skip akun yang tidak memiliki aktivitas atau saldo
            if ($this->shouldSkipAccount($saldoAwal, $totalDebitPeriode, $totalKreditPeriode, $saldoAkhir)) {
                continue;
            }

            $trialBalanceData[] = [
                'kode_akun' => $coa->kode_akun,
                'nama_akun' => $coa->nama_akun,
                'tipe_akun' => $coa->tipe_akun,
                'saldo_awal' => $saldoAwal,
                'mutasi_debit' => $totalDebitPeriode,
                'mutasi_kredit' => $totalKreditPeriode,
                'saldo_akhir' => $saldoAkhir,
                'debit' => $displayBalance['debit'],   // Untuk tampilan neraca saldo
                'kredit' => $displayBalance['kredit'], // Untuk tampilan neraca saldo
                'is_debit_normal' => $this->isDebitNormalAccount($coa),
            ];

            // Akumulasi total untuk balance check
            $totalDebit += $displayBalance['debit'];
            $totalKredit += $displayBalance['kredit'];
        }

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
            ]
        ];
    }

    /**
     * Ambil saldo awal akun
     * 
     * Untuk implementasi sederhana, gunakan saldo_awal dari COA.
     * Bisa dikembangkan untuk menghitung dari transaksi sebelum periode.
     */
    private function getSaldoAwal($coa, $startDate)
    {
        // TODO: Implementasi untuk menghitung saldo awal dari transaksi sebelum periode
        // Untuk sekarang, gunakan saldo_awal dari COA
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
        $mutasi = JournalLine::join('journal_entries', 'journal_lines.journal_entry_id', '=', 'journal_entries.id')
            ->where('journal_lines.coa_id', $coaId)
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
     * - Aset (1xx): Normal Debit
     * - Kewajiban (2xx): Normal Kredit
     * - Modal/Ekuitas (3xx): Normal Kredit
     * - Pendapatan (4xx): Normal Kredit
     * - Beban (5xx, 6xx): Normal Debit
     */
    private function isDebitNormalAccount($coa)
    {
        // Prioritas 1: Berdasarkan kode akun (digit pertama)
        $firstDigit = substr($coa->kode_akun, 0, 1);
        
        // Akun dengan normal balance debit
        $debitNormalDigits = ['1', '5', '6']; // Aset, Beban
        
        if (in_array($firstDigit, $debitNormalDigits)) {
            return true;
        }

        // Prioritas 2: Fallback ke tipe_akun jika ada
        if (!empty($coa->tipe_akun)) {
            $tipeAkun = strtoupper($coa->tipe_akun);
            $debitNormalTypes = ['ASET', 'ASSET', 'BEBAN', 'EXPENSE'];
            
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
}