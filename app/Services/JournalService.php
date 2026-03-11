<?php

namespace App\Services;

use App\Models\JurnalUmum;
use App\Models\Coa;
use App\Models\Pembelian;
use App\Models\Penjualan;
use App\Models\ExpensePayment;
use Illuminate\Support\Facades\Log;

class JournalService
{
    /**
     * Create journal entries for Pembelian transaction
     */
    public function createJournalFromPembelian(Pembelian $pembelian)
    {
        try {
            $tanggal = $pembelian->tanggal;
            $keterangan = "Pembelian - No. {$pembelian->nomor_pembelian}";
            $refId = $pembelian->id;
            $refType = 'pembelian';
            
            // Get COA accounts
            $persediaanCoa = Coa::where('kode_akun', '1511')->first(); // Persediaan Barang Dagang
            $ppnMasukanCoa = Coa::where('kode_akun', '2111')->first(); // PPN Masukan
            $utangUsahaCoa = Coa::where('kode_akun', '2101')->first(); // Utang Usaha
            $kasCoa = Coa::where('kode_akun', '1101')->first(); // Kas
            
            if (!$persediaanCoa || !$utangUsahaCoa) {
                Log::error('Required COA accounts not found for pembelian journal');
                return false;
            }
            
            // Calculate amounts (assuming total includes PPN)
            $dpp = $pembelian->total_harga / 1.11; // DPP (sebelum PPN 11%)
            $ppn = $pembelian->total_harga - $dpp; // PPN 11%
            
            // Delete existing journals for this transaction to avoid duplicates
            $this->deleteExistingJournals($refType, $refId);
            
            // Journal Entry 1: Debit Persediaan, Debit PPN Masukan, Kredit Utang Usaha (if credit)
            if ($pembelian->payment_method === 'credit') {
                // Debit Persediaan
                $this->createJournalEntry([
                    'coa_id' => $persediaanCoa->id,
                    'tanggal' => $tanggal,
                    'keterangan' => $keterangan,
                    'debit' => $dpp,
                    'kredit' => 0,
                    'referensi' => $refId,
                    'tipe_referensi' => $refType,
                ]);
                
                // Debit PPN Masukan
                if ($ppn > 0 && $ppnMasukanCoa) {
                    $this->createJournalEntry([
                        'coa_id' => $ppnMasukanCoa->id,
                        'tanggal' => $tanggal,
                        'keterangan' => $keterangan,
                        'debit' => $ppn,
                        'kredit' => 0,
                        'referensi' => $refId,
                        'tipe_referensi' => $refType,
                    ]);
                }
                
                // Kredit Utang Usaha
                $this->createJournalEntry([
                    'coa_id' => $utangUsahaCoa->id,
                    'tanggal' => $tanggal,
                    'keterangan' => $keterangan,
                    'debit' => 0,
                    'kredit' => $pembelian->total_harga,
                    'referensi' => $refId,
                    'tipe_referensi' => $refType,
                ]);
            } 
            // Cash purchase
            else {
                // Debit Persediaan
                $this->createJournalEntry([
                    'coa_id' => $persediaanCoa->id,
                    'tanggal' => $tanggal,
                    'keterangan' => $keterangan,
                    'debit' => $dpp,
                    'kredit' => 0,
                    'referensi' => $refId,
                    'tipe_referensi' => $refType,
                ]);
                
                // Debit PPN Masukan
                if ($ppn > 0 && $ppnMasukanCoa) {
                    $this->createJournalEntry([
                        'coa_id' => $ppnMasukanCoa->id,
                        'tanggal' => $tanggal,
                        'keterangan' => $keterangan,
                        'debit' => $ppn,
                        'kredit' => 0,
                        'referensi' => $refId,
                        'tipe_referensi' => $refType,
                    ]);
                }
                
                // Kredit Kas
                if ($kasCoa) {
                    $this->createJournalEntry([
                        'coa_id' => $kasCoa->id,
                        'tanggal' => $tanggal,
                        'keterangan' => $keterangan,
                        'debit' => 0,
                        'kredit' => $pembelian->total_harga,
                        'referensi' => $refId,
                        'tipe_referensi' => $refType,
                    ]);
                }
            }
            
            Log::info('Journal entries created for pembelian', ['pembelian_id' => $pembelian->id]);
            return true;
            
        } catch (\Exception $e) {
            Log::error('Error creating journal for pembelian: ' . $e->getMessage(), [
                'pembelian_id' => $pembelian->id
            ]);
            return false;
        }
    }
    
    /**
     * Create journal entries for Penjualan transaction
     */
    public function createJournalFromPenjualan(Penjualan $penjualan)
    {
        try {
            $tanggal = $penjualan->tanggal;
            $keterangan = "Penjualan - No. {$penjualan->nomor_penjualan}";
            $refId = $penjualan->id;
            $refType = 'penjualan';
            
            // Get COA accounts
            $kasCoa = Coa::where('kode_akun', '1101')->first(); // Kas
            $piutangUsahaCoa = Coa::where('kode_akun', '1301')->first(); // Piutang Usaha
            $penjualanCoa = Coa::where('kode_akun', '4101')->first(); // Penjualan
            $ppnKeluaranCoa = Coa::where('kode_akun', '3101')->first(); // PPN Keluaran
            
            if (!$penjualanCoa || (!$kasCoa && !$piutangUsahaCoa)) {
                Log::error('Required COA accounts not found for penjualan journal');
                return false;
            }
            
            // Calculate amounts
            $dpp = $penjualan->total / 1.11; // DPP (sebelum PPN 11%)
            $ppn = $penjualan->total - $dpp; // PPN 11%
            
            // Delete existing journals for this transaction to avoid duplicates
            $this->deleteExistingJournals($refType, $refId);
            
            // Cash sale
            if ($penjualan->payment_method === 'cash') {
                // Debit Kas
                if ($kasCoa) {
                    $this->createJournalEntry([
                        'coa_id' => $kasCoa->id,
                        'tanggal' => $tanggal,
                        'keterangan' => $keterangan,
                        'debit' => $penjualan->total,
                        'kredit' => 0,
                        'referensi' => $refId,
                        'tipe_referensi' => $refType,
                    ]);
                }
            }
            // Credit sale
            else {
                // Debit Piutang Usaha
                if ($piutangUsahaCoa) {
                    $this->createJournalEntry([
                        'coa_id' => $piutangUsahaCoa->id,
                        'tanggal' => $tanggal,
                        'keterangan' => $keterangan,
                        'debit' => $penjualan->total,
                        'kredit' => 0,
                        'referensi' => $refId,
                        'tipe_referensi' => $refType,
                    ]);
                }
            }
            
            // Kredit Penjualan (DPP)
            $this->createJournalEntry([
                'coa_id' => $penjualanCoa->id,
                'tanggal' => $tanggal,
                'keterangan' => $keterangan,
                'debit' => 0,
                'kredit' => $dpp,
                'referensi' => $refId,
                'tipe_referensi' => $refType,
            ]);
            
            // Kredit PPN Keluaran
            if ($ppn > 0 && $ppnKeluaranCoa) {
                $this->createJournalEntry([
                    'coa_id' => $ppnKeluaranCoa->id,
                    'tanggal' => $tanggal,
                    'keterangan' => $keterangan,
                    'debit' => 0,
                    'kredit' => $ppn,
                    'referensi' => $refId,
                    'tipe_referensi' => $refType,
                ]);
            }
            
            Log::info('Journal entries created for penjualan', ['penjualan_id' => $penjualan->id]);
            return true;
            
        } catch (\Exception $e) {
            Log::error('Error creating journal for penjualan: ' . $e->getMessage(), [
                'penjualan_id' => $penjualan->id
            ]);
            return false;
        }
    }
    
    /**
     * Create journal entries for Pembayaran Beban transaction
     */
    public function createJournalFromExpensePayment(ExpensePayment $expensePayment)
    {
        try {
            $tanggal = $expensePayment->tanggal;
            $keterangan = "Pembayaran Beban - {$expensePayment->keterangan}";
            $refId = $expensePayment->id;
            $refType = 'expense_payment';
            
            // Get COA accounts
            $bebanCoa = $expensePayment->coa; // Get related COA from the payment
            $kasCoa = Coa::where('kode_akun', '1101')->first(); // Kas
            $bankCoa = null;
            
            // Determine cash/bank account based on payment method
            if ($expensePayment->metode_bayar === 'bank') {
                $bankCoa = Coa::where('kode_akun', $expensePayment->coa_kasbank)->first();
            }
            
            if (!$bebanCoa || (!$kasCoa && !$bankCoa)) {
                Log::error('Required COA accounts not found for expense payment journal');
                return false;
            }
            
            // Delete existing journals for this transaction to avoid duplicates
            $this->deleteExistingJournals($refType, $refId);
            
            // Debit Beban Account
            $this->createJournalEntry([
                'coa_id' => $bebanCoa->id,
                'tanggal' => $tanggal,
                'keterangan' => $keterangan,
                'debit' => $expensePayment->nominal_pembayaran,
                'kredit' => 0,
                'referensi' => $refId,
                'tipe_referensi' => $refType,
            ]);
            
            // Kredit Kas or Bank Account
            if ($expensePayment->metode_bayar === 'bank' && $bankCoa) {
                $this->createJournalEntry([
                    'coa_id' => $bankCoa->id,
                    'tanggal' => $tanggal,
                    'keterangan' => $keterangan,
                    'debit' => 0,
                    'kredit' => $expensePayment->nominal_pembayaran,
                    'referensi' => $refId,
                    'tipe_referensi' => $refType,
                ]);
            } else {
                // Default to cash
                if ($kasCoa) {
                    $this->createJournalEntry([
                        'coa_id' => $kasCoa->id,
                        'tanggal' => $tanggal,
                        'keterangan' => $keterangan,
                        'debit' => 0,
                        'kredit' => $expensePayment->nominal_pembayaran,
                        'referensi' => $refId,
                        'tipe_referensi' => $refType,
                    ]);
                }
            }
            
            Log::info('Journal entries created for expense payment', ['expense_payment_id' => $expensePayment->id]);
            return true;
            
        } catch (\Exception $e) {
            Log::error('Error creating journal for expense payment: ' . $e->getMessage(), [
                'expense_payment_id' => $expensePayment->id
            ]);
            return false;
        }
    }
    
    /**
     * Create a single journal entry
     */
    private function createJournalEntry(array $data)
    {
        $data['created_by'] = auth()->id();
        return JurnalUmum::create($data);
    }
    
    /**
     * Delete existing journals for a transaction
     */
    private function deleteExistingJournals($refType, $refId)
    {
        return JurnalUmum::where('tipe_referensi', $refType)
            ->where('referensi', $refId)
            ->delete();
    }
}
