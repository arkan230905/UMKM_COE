<?php

namespace App\Services;

use App\Models\Coa;
use App\Models\JournalLine;
use App\Models\JournalEntry;
use App\Models\JurnalUmum;
use App\Helpers\AccountHelper;
use Illuminate\Support\Facades\Log;

/**
 * Service untuk memastikan konsistensi data laporan Kas dan Bank
 * Mencegah duplikasi transaksi antara sistem lama dan baru
 */
class KasBankConsistencyService
{
    /**
     * Validasi konsistensi data untuk periode tertentu
     */
    public static function validateConsistency($startDate, $endDate)
    {
        $issues = [];
        $akunKasBank = AccountHelper::getKasBankAccounts();
        
        foreach ($akunKasBank as $akun) {
            $accountIssues = self::validateAccountConsistency($akun, $startDate, $endDate);
            if (!empty($accountIssues)) {
                $issues[$akun->kode_akun] = $accountIssues;
            }
        }
        
        return $issues;
    }
    
    /**
     * Validasi konsistensi untuk satu akun
     */
    private static function validateAccountConsistency($akun, $startDate, $endDate)
    {
        $issues = [];
        
        // 1. Cek duplikasi antara sistem lama dan baru
        $duplicates = self::detectDuplicates($akun, $startDate, $endDate);
        if (!empty($duplicates)) {
            $issues['duplicates'] = $duplicates;
        }
        
        // 2. Cek transaksi tanpa referensi yang jelas
        $unreferenced = self::findUnreferencedTransactions($akun, $startDate, $endDate);
        if (!empty($unreferenced)) {
            $issues['unreferenced'] = $unreferenced;
        }
        
        // 3. Cek nominal yang tidak konsisten
        $inconsistent = self::findInconsistentAmounts($akun, $startDate, $endDate);
        if (!empty($inconsistent)) {
            $issues['inconsistent_amounts'] = $inconsistent;
        }
        
        return $issues;
    }
    
    /**
     * Deteksi duplikasi transaksi
     */
    private static function detectDuplicates($akun, $startDate, $endDate)
    {
        $duplicates = [];
        
        // Get transactions from both systems
        $journalLines = JournalLine::join('journal_entries', 'journal_lines.journal_entry_id', '=', 'journal_entries.id')
            ->where('journal_lines.coa_id', $akun->id)
            ->whereBetween('journal_entries.tanggal', [$startDate, $endDate])
            ->select('journal_entries.tanggal', 'journal_entries.ref_type', 'journal_entries.ref_id', 'journal_lines.debit', 'journal_lines.credit')
            ->get();
            
        $jurnalUmum = JurnalUmum::where('coa_id', $akun->id)
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->select('tanggal', 'tipe_referensi', 'referensi', 'debit', 'kredit')
            ->get();
        
        // Check for duplicates
        foreach ($journalLines as $jl) {
            foreach ($jurnalUmum as $ju) {
                if (self::isDuplicateTransaction($jl, $ju)) {
                    $duplicates[] = [
                        'tanggal' => $jl->tanggal,
                        'nominal' => $jl->debit ?: $jl->credit,
                        'new_system' => [
                            'ref_type' => $jl->ref_type,
                            'ref_id' => $jl->ref_id
                        ],
                        'old_system' => [
                            'tipe_referensi' => $ju->tipe_referensi,
                            'referensi' => $ju->referensi
                        ]
                    ];
                }
            }
        }
        
        return $duplicates;
    }
    
    /**
     * Cek apakah dua transaksi adalah duplikat
     */
    private static function isDuplicateTransaction($jl, $ju)
    {
        $jlDate = date('Y-m-d', strtotime($jl->tanggal));
        $juDate = date('Y-m-d', strtotime($ju->tanggal));
        
        // Check same date and amount
        $jlAmount = $jl->debit ?: $jl->credit;
        $juAmount = $ju->debit ?: $ju->kredit;
        
        if ($jlDate !== $juDate || abs($jlAmount - $juAmount) > 0.01) {
            return false;
        }
        
        // Check ref type match
        $refTypeMatch = (
            ($jl->ref_type === 'purchase' && $ju->tipe_referensi === 'pembelian') ||
            ($jl->ref_type === 'sale' && $ju->tipe_referensi === 'sale') ||
            ($jl->ref_type === 'sale' && $ju->tipe_referensi === 'penjualan') ||
            ($jl->ref_type === 'expense_payment' && $ju->tipe_referensi === 'pembayaran_beban') ||
            ($jl->ref_type === 'penggajian' && $ju->tipe_referensi === 'penggajian')
        );
        
        // Check penjualan match
        $penjualanMatch = false;
        if ($jl->ref_type === 'sale' && ($ju->tipe_referensi === 'sale' || $ju->tipe_referensi === 'penjualan')) {
            if (preg_match('/sale#(\d+)/', $ju->referensi, $matches)) {
                $penjualanId = (int)$matches[1];
                if ($jl->ref_id == $penjualanId) {
                    $penjualanMatch = true;
                }
            } elseif (preg_match('/SJ-\d+-(\d+)/', $ju->referensi, $matches)) {
                $penjualanId = (int)$matches[1];
                if ($jl->ref_id == $penjualanId) {
                    $penjualanMatch = true;
                }
            }
        }
        
        return $refTypeMatch || $penjualanMatch;
    }
    
    /**
     * Cari transaksi tanpa referensi yang jelas
     */
    private static function findUnreferencedTransactions($akun, $startDate, $endDate)
    {
        $unreferenced = [];
        
        // Check new system
        $journalLines = JournalLine::join('journal_entries', 'journal_lines.journal_entry_id', '=', 'journal_entries.id')
            ->where('journal_lines.coa_id', $akun->id)
            ->whereBetween('journal_entries.tanggal', [$startDate, $endDate])
            ->where(function($query) {
                $query->whereNull('journal_entries.ref_type')
                      ->orWhereNull('journal_entries.ref_id')
                      ->orWhere('journal_entries.ref_id', '=', 0);
            })
            ->select('journal_entries.tanggal', 'journal_entries.memo', 'journal_lines.debit', 'journal_lines.credit')
            ->get();
            
        foreach ($journalLines as $jl) {
            $unreferenced[] = [
                'tanggal' => $jl->tanggal,
                'nominal' => $jl->debit ?: $jl->credit,
                'system' => 'new',
                'memo' => $jl->memo,
                'issue' => 'Missing ref_type or ref_id'
            ];
        }
        
        // Check old system
        $jurnalUmum = JurnalUmum::where('coa_id', $akun->id)
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->where(function($query) {
                $query->whereNull('tipe_referensi')
                      ->orWhereNull('referensi');
            })
            ->select('tanggal', 'keterangan', 'debit', 'kredit')
            ->get();
            
        foreach ($jurnalUmum as $ju) {
            $unreferenced[] = [
                'tanggal' => $ju->tanggal,
                'nominal' => $ju->debit ?: $ju->kredit,
                'system' => 'old',
                'keterangan' => $ju->keterangan,
                'issue' => 'Missing tipe_referensi or referensi'
            ];
        }
        
        return $unreferenced;
    }
    
    /**
     * Cari nominal yang tidak konsisten
     */
    private static function findInconsistentAmounts($akun, $startDate, $endDate)
    {
        $inconsistent = [];
        
        // Check for zero amounts
        $zeroAmounts = JournalLine::join('journal_entries', 'journal_lines.journal_entry_id', '=', 'journal_entries.id')
            ->where('journal_lines.coa_id', $akun->id)
            ->whereBetween('journal_entries.tanggal', [$startDate, $endDate])
            ->where('journal_lines.debit', '=', 0)
            ->where('journal_lines.credit', '=', 0)
            ->select('journal_entries.tanggal', 'journal_entries.memo')
            ->get();
            
        foreach ($zeroAmounts as $za) {
            $inconsistent[] = [
                'tanggal' => $za->tanggal,
                'system' => 'new',
                'issue' => 'Zero amount transaction',
                'memo' => $za->memo
            ];
        }
        
        // Check old system for zero amounts
        $zeroAmountsOld = JurnalUmum::where('coa_id', $akun->id)
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->where('debit', '=', 0)
            ->where('kredit', '=', 0)
            ->select('tanggal', 'keterangan')
            ->get();
            
        foreach ($zeroAmountsOld as $za) {
            $inconsistent[] = [
                'tanggal' => $za->tanggal,
                'system' => 'old',
                'issue' => 'Zero amount transaction',
                'keterangan' => $za->keterangan
            ];
        }
        
        return $inconsistent;
    }
    
    /**
     * Log konsistensi data untuk monitoring
     */
    public static function logConsistencyCheck($startDate, $endDate)
    {
        $issues = self::validateConsistency($startDate, $endDate);
        
        if (empty($issues)) {
            Log::info('Kas Bank consistency check passed', [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => 'OK'
            ]);
        } else {
            Log::warning('Kas Bank consistency issues found', [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'issues_count' => count($issues),
                'issues' => $issues
            ]);
        }
        
        return $issues;
    }
    
    /**
     * Generate standard key untuk deduplication
     */
    public static function generateDuplicateKey($tanggal, $referensi, $nominal)
    {
        // Standard format: Y-m-d 00:00:00_referensi_nominal.00
        $date = date('Y-m-d 00:00:00', strtotime($tanggal));
        $formattedNominal = number_format($nominal, 2, '.', '');
        return $date . '_' . $referensi . '_' . $formattedNominal;
    }
}
