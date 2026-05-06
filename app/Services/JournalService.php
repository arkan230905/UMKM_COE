<?php

namespace App\Services;

use App\Models\JurnalUmum;
use App\Models\Coa;
use Illuminate\Support\Facades\DB;

class JournalService
{
    protected function coaId(string $code): int
    {
        $coa = Coa::where('kode_akun', $code)->first();
        if ($coa) {
            return (int)$coa->getAttribute('id');
        }

        throw new \RuntimeException("COA dengan kode {$code} tidak ditemukan. Silakan buat COA terlebih dahulu di master data.");
    }

    /**
     * Post a balanced journal entry with given lines. Each line element: ['code'=>account_code, 'debit'=>float, 'credit'=>float, 'memo'=>string (optional)]
     */
    public function post(string $tanggal, string $refType, int $refId, string $memo, array $lines)
    {
        return $this->postWithUser($tanggal, $refType, $refId, $memo, $lines, auth()->id());
    }

    /**
     * Post a balanced journal entry with specific user_id
     */
    public function postWithUser(string $tanggal, string $refType, int $refId, string $memo, array $lines, $userId)
    {
        return DB::transaction(function () use ($tanggal, $refType, $refId, $memo, $lines, $userId) {
            $totalDebit = 0.0; 
            $totalCredit = 0.0;
            
            foreach ($lines as $ln) {
                $aid = $this->coaId($ln['code']);
                $debit = (float)($ln['debit'] ?? 0); 
                $credit = (float)($ln['credit'] ?? 0);
                $lineMemo = $ln['memo'] ?? $memo;
                
                // Create journal entry using JurnalUmum
                JurnalUmum::create([
                    'user_id' => $userId,
                    'coa_id' => $aid,
                    'tanggal' => $tanggal,
                    'keterangan' => $lineMemo,
                    'debit' => $debit,
                    'kredit' => $credit,
                    'referensi' => $refId,
                    'tipe_referensi' => $refType,
                    'created_by' => $userId,
                ]);
                
                $totalDebit += $debit;
                $totalCredit += $credit;
            }

            // Validate balance
            if (abs($totalDebit - $totalCredit) > 0.01) {
                throw new \RuntimeException("Journal entry must balance. Total debit: $totalDebit, Total credit: $totalCredit");
            }

            return (object)['id' => $refId];
        });
    }

    /**
     * Delete journal entries by reference
     */
    public function deleteByRef(string $refType, int $refId)
    {
        return JurnalUmum::where('tipe_referensi', $refType)->where('referensi', $refId)->delete();
    }

    /**
     * Get journal entries by reference
     */
    public function getJournalEntries(string $refType, int $refId)
    {
        return JurnalUmum::where('tipe_referensi', $refType)->where('referensi', $refId)->with('coa')->get();
    }

    /**
     * Get journal entries by date range for user
     */
    public function getJournalEntriesByDateRange($userId, $startDate, $endDate)
    {
        return JurnalUmum::where('user_id', $userId)
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->with('coa')
            ->orderBy('tanggal')
            ->orderBy('created_at')
            ->get();
    }

    /**
     * Create journal entries from Penjualan with HPP
     * Dr. Kas/Bank/Piutang | Cr. Pendapatan Penjualan
     * Dr. HPP | Cr. Persediaan Barang Jadi
     */
    public static function createJournalFromPenjualan($penjualan, $userId = null): void
    {
        $service = new static();
        
        // Refresh and load relationships to ensure we have latest data
        $penjualan = $penjualan->fresh(['details.produk', 'produk']);
        
        if (!$penjualan) {
            \Log::error('Penjualan not found when creating journal');
            return;
        }
        
        // Delete existing journal entries for this penjualan
        $service->deleteByRef('sale', $penjualan->id);
        
        $lines = [];
        $totalAmount = $penjualan->total ?? 0;
        
        // Create debit entry based on payment method (Kas/Bank/Piutang)
        $debitAccount = null;
        $debitMemo = '';
        
        switch ($penjualan->payment_method) {
            case 'cash':
                // Cari COA Kas yang ada di database
                $kasCoa = Coa::where('tipe_akun', 'Asset')
                    ->where(function($query) {
                        $query->where('nama_akun', 'like', '%kas%')
                              ->where('nama_akun', 'not like', '%bank%');
                    })
                    ->orWhere('kode_akun', '112') // Kas
                    ->orWhere('kode_akun', '101')
                    ->first();
                
                $debitAccount = $kasCoa ? $kasCoa->kode_akun : '112';
                $debitMemo = 'Penerimaan tunai penjualan';
                break;
                
            case 'transfer':
                // Cari COA Bank yang ada di database
                $bankCoa = Coa::where('tipe_akun', 'Asset')
                    ->where(function($query) {
                        $query->where('nama_akun', 'like', '%bank%')
                              ->orWhere('nama_akun', 'like', '%kas%bank%');
                    })
                    ->orWhere('kode_akun', '1102')
                    ->orWhere('kode_akun', '102')
                    ->first();
                
                $debitAccount = $bankCoa ? $bankCoa->kode_akun : '111'; // Kas Bank
                $debitMemo = 'Penerimaan transfer penjualan';
                break;
                
            case 'credit':
                // Cari COA Piutang yang ada di database
                $piutangCoa = Coa::where('tipe_akun', 'Asset')
                    ->where(function($query) {
                        $query->where('nama_akun', 'like', '%piutang%')
                              ->orWhere('nama_akun', 'like', '%piutang%usaha%');
                    })
                    ->orWhere('kode_akun', '113') // Piutang Usaha
                    ->orWhere('kode_akun', '103')
                    ->first();
                
                $debitAccount = $piutangCoa ? $piutangCoa->kode_akun : '113';
                $debitMemo = 'Penerimaan kredit penjualan';
                break;
                
            default:
                // Default to Kas
                $debitAccount = '112';
                $debitMemo = 'Penerimaan penjualan';
        }
        
        $lines[] = [
            'code' => $debitAccount,
            'debit' => $totalAmount,
            'credit' => 0,
            'memo' => $debitMemo
        ];
        
        // Create credit entry for sales revenue - gunakan COA 41 (PENDAPATAN)
        $penjualanCoa = Coa::where('kode_akun', '41')->first();
        
        $creditAccount = $penjualanCoa ? $penjualanCoa->kode_akun : '41';
        
        $lines[] = [
            'code' => $creditAccount,
            'debit' => 0,
            'credit' => $totalAmount,
            'memo' => 'Pendapatan penjualan produk'
        ];
        
        // Add HPP journal entries with detailed breakdown
        $hppLines = $service->createHPPLinesFromPenjualan($penjualan);
        $lines = array_merge($lines, $hppLines);
        
        // Create journal entry
        $memo = 'Penjualan #' . ($penjualan->nomor_penjualan ?? $penjualan->id);
        $tanggal = $penjualan->tanggal instanceof \Carbon\Carbon ? 
                   $penjualan->tanggal->format('Y-m-d') : 
                   $penjualan->tanggal;
        
        // Use provided userId or fallback to penjualan's user_id
        $finalUserId = $userId ?? $penjualan->user_id ?? auth()->id();
        
        $service->postWithUser($tanggal, 'sale', $penjualan->id, $memo, $lines, $finalUserId);
    }

    /**
     * Create HPP journal lines from Penjualan with simplified calculation
     * Dr. HPP | Cr. Persediaan Barang Jadi
     */
    private function createHPPLinesFromPenjualan($penjualan): array
    {
        $lines = [];
        
        // Log for debugging
        \Log::info('Creating HPP lines for penjualan', [
            'penjualan_id' => $penjualan->id,
            'has_details' => $penjualan->details ? $penjualan->details->count() : 0,
            'has_produk' => $penjualan->produk ? true : false
        ]);
        
        // PRIORITY 1: Use details if available (modern multi-item penjualan)
        if ($penjualan->details && $penjualan->details->count() > 0) {
            // Multi-item penjualan
            foreach ($penjualan->details as $detail) {
                $detailLines = $this->createHPPLinesForDetail($detail, $penjualan);
                \Log::info('HPP lines for detail', [
                    'detail_id' => $detail->id,
                    'lines_count' => count($detailLines)
                ]);
                $lines = array_merge($lines, $detailLines);
            }
        } 
        // PRIORITY 2: Use single produk_id (legacy single-item penjualan)
        elseif ($penjualan->produk) {
            $singleLines = $this->createHPPLinesForSingleItem($penjualan);
            \Log::info('HPP lines for single item', [
                'lines_count' => count($singleLines)
            ]);
            $lines = $singleLines;
        }
        else {
            \Log::warning('No details or produk found for penjualan', [
                'penjualan_id' => $penjualan->id
            ]);
        }
        
        \Log::info('Total HPP lines created', ['count' => count($lines)]);
        
        return $lines;
    }
    
    /**
     * Create HPP lines for penjualan detail (simplified version)
     */
    private function createHPPLinesForDetail($detail, $penjualan): array
    {
        $lines = [];
        $qty = $detail->jumlah ?? 0;
        $product = $detail->produk;
        
        if (!$product || $qty <= 0) {
            return $lines;
        }
        
        // Get HPP using product's built-in method
        $hppPerUnit = $product->getActualHPP($penjualan->tanggal);
        $totalHPP = $hppPerUnit * $qty;
        
        if ($totalHPP > 0) {
            // Debit HPP account
            $lines[] = [
                'code' => '554', // HARGA POKOK PENJUALAN (HPP) - Updated from 560 to 554
                'debit' => $totalHPP,
                'credit' => 0,
                'memo' => "HPP untuk {$product->nama_produk} ({$qty} pcs @ Rp " . number_format($hppPerUnit, 2) . ")"
            ];
            
            // Credit persediaan barang jadi
            $persediaanCOA = $this->getPersediaanBarangJadiCOA($product);
            $lines[] = [
                'code' => $persediaanCOA,
                'debit' => 0,
                'credit' => $totalHPP,
                'memo' => "Keluar persediaan - {$product->nama_produk} ({$qty} pcs)"
            ];
        }
        
        return $lines;
    }
    
    /**
     * Create HPP lines for single item penjualan (simplified version)
     */
    private function createHPPLinesForSingleItem($penjualan): array
    {
        $lines = [];
        $qty = $penjualan->jumlah ?? 0;
        $product = $penjualan->produk;
        
        \Log::info('createHPPLinesForSingleItem debug', [
            'qty' => $qty,
            'has_product' => $product ? true : false,
            'product_id' => $product ? $product->id : null
        ]);
        
        if (!$product || $qty <= 0) {
            \Log::warning('Skipping HPP for single item', [
                'reason' => !$product ? 'no product' : 'qty <= 0',
                'qty' => $qty
            ]);
            return $lines;
        }
        
        // Get HPP using product's built-in method
        $hppPerUnit = $product->getActualHPP($penjualan->tanggal);
        $totalHPP = $hppPerUnit * $qty;
        
        \Log::info('HPP calculation for single item', [
            'hpp_per_unit' => $hppPerUnit,
            'qty' => $qty,
            'total_hpp' => $totalHPP
        ]);
        
        if ($totalHPP > 0) {
            // Debit HPP account
            $lines[] = [
                'code' => '554', // HARGA POKOK PENJUALAN (HPP) - Updated from 560 to 554
                'debit' => $totalHPP,
                'credit' => 0,
                'memo' => "HPP untuk {$product->nama_produk} ({$qty} pcs @ Rp " . number_format($hppPerUnit, 2) . ")"
            ];
            
            // Credit persediaan barang jadi
            $persediaanCOA = $this->getPersediaanBarangJadiCOA($product);
            $lines[] = [
                'code' => $persediaanCOA,
                'debit' => 0,
                'credit' => $totalHPP,
                'memo' => "Keluar persediaan - {$product->nama_produk} ({$qty} pcs)"
            ];
            
            \Log::info('HPP lines created for single item', ['lines_count' => count($lines)]);
        } else {
            \Log::warning('HPP is zero, no lines created');
        }
        
        return $lines;
    }
    
    /**
     * Get appropriate persediaan barang jadi COA for product
     */
    private function getPersediaanBarangJadiCOA($product): string
    {
        // Try to find specific COA for product
        if ($product->coa_persediaan_id) {
            return $product->coa_persediaan_id;
        }
        
        // Default to standard persediaan barang jadi account
        // Cari COA Persediaan Barang Jadi yang ada di database
        $persediaanCoa = Coa::where('tipe_akun', 'Asset')
            ->where(function($query) {
                $query->where('nama_akun', 'like', '%persediaan%barang%jadi%')
                      ->orWhere('nama_akun', 'like', '%persediaan%produk%jadi%');
            })
            ->orWhere('kode_akun', '116') // Persediaan Barang Jadi
            ->orWhere('kode_akun', '1160')
            ->first();
        
        return $persediaanCoa ? $persediaanCoa->kode_akun : '116';
    }
}