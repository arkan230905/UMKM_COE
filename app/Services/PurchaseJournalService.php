<?php

namespace App\Services;

use App\Models\Pembelian;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\Coa;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PurchaseJournalService
{
    /**
     * Create journal entries for purchase transaction
     */
    public function createJournalFromPurchase(Pembelian $pembelian)
    {
        try {
            DB::beginTransaction();
            
            // Check if journal already exists
            $existingJournal = JournalEntry::where('ref_type', 'purchase')
                ->where('ref_id', $pembelian->id)
                ->first();
            
            if ($existingJournal) {
                Log::info('Journal already exists for purchase', [
                    'pembelian_id' => $pembelian->id,
                    'journal_id' => $existingJournal->id
                ]);
                return $existingJournal;
            }
            
            // Create main journal entry
            $journalEntry = JournalEntry::create([
                'tanggal' => $pembelian->tanggal,
                'memo' => 'Pembelian #' . $pembelian->nomor_pembelian . ' - ' . $pembelian->vendor->nama,
                'ref_type' => 'purchase',
                'ref_id' => $pembelian->id
            ]);
            
            // Process each purchase detail
            foreach ($pembelian->details as $detail) {
                $this->createInventoryJournalLine($journalEntry, $detail);
            }
            
            // Add PPN if exists
            if ($pembelian->ppn_nominal > 0) {
                $this->createPPNJournalLine($journalEntry, $pembelian);
            }
            
            // Add payment journal line (Cash/Bank or Accounts Payable)
            $this->createPaymentJournalLine($journalEntry, $pembelian);
            
            DB::commit();
            
            Log::info('Purchase journal created successfully', [
                'pembelian_id' => $pembelian->id,
                'journal_id' => $journalEntry->id,
                'total_amount' => $pembelian->total_harga
            ]);
            
            return $journalEntry;
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating purchase journal', [
                'pembelian_id' => $pembelian->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
    
    /**
     * Create inventory journal line for purchase detail
     */
    private function createInventoryJournalLine(JournalEntry $journalEntry, $detail)
    {
        $coaAccount = $this->getInventoryAccount($detail);
        
        if (!$coaAccount) {
            Log::warning('No COA account found for purchase detail', [
                'detail_id' => $detail->id,
                'tipe_item' => $detail->tipe_item,
                'bahan_baku_id' => $detail->bahan_baku_id,
                'bahan_pendukung_id' => $detail->bahan_pendukung_id
            ]);
            return;
        }
        
        // Debit: Inventory Account (Asset increases)
        JournalLine::create([
            'journal_entry_id' => $journalEntry->id,
            'coa_id' => $coaAccount->id,
            'debit' => $detail->subtotal,
            'credit' => 0,
            'memo' => $detail->nama_bahan . ' - ' . $detail->jumlah . ' ' . $detail->satuan_nama
        ]);
    }
    
    /**
     * Create PPN journal line
     */
    private function createPPNJournalLine(JournalEntry $journalEntry, Pembelian $pembelian)
    {
        // Find PPN Masukan account
        $ppnAccount = Coa::where('kode_akun', '127')->first();
        
        if (!$ppnAccount) {
            Log::warning('PPN Masukan account not found');
            return;
        }
        
        // Debit: PPN Masukan (Asset increases)
        JournalLine::create([
            'journal_entry_id' => $journalEntry->id,
            'coa_id' => $ppnAccount->id,
            'debit' => $pembelian->ppn_nominal,
            'credit' => 0,
            'memo' => 'PPN Masukan (' . $pembelian->ppn_persen . '%)'
        ]);
    }
    
    /**
     * Create payment journal line (Cash/Bank or Accounts Payable)
     */
    private function createPaymentJournalLine(JournalEntry $journalEntry, Pembelian $pembelian)
    {
        if ($pembelian->payment_method === 'cash') {
            // Cash payment
            $kasAccount = Coa::where('kode_akun', '112')->first(); // Kas
            
            if ($kasAccount) {
                JournalLine::create([
                    'journal_entry_id' => $journalEntry->id,
                    'coa_id' => $kasAccount->id,
                    'debit' => 0,
                    'credit' => $pembelian->total_harga,
                    'memo' => 'Pembayaran tunai pembelian'
                ]);
            }
            
        } elseif ($pembelian->payment_method === 'transfer' && $pembelian->bank_id) {
            // Bank transfer
            $bankAccount = Coa::find($pembelian->bank_id);
            
            if ($bankAccount) {
                JournalLine::create([
                    'journal_entry_id' => $journalEntry->id,
                    'coa_id' => $bankAccount->id,
                    'debit' => 0,
                    'credit' => $pembelian->total_harga,
                    'memo' => 'Pembayaran transfer - ' . $bankAccount->nama_akun
                ]);
            }
            
        } else {
            // Credit purchase (Accounts Payable)
            $apAccount = Coa::where('kode_akun', '210')->first(); // Hutang Usaha
            
            if ($apAccount) {
                JournalLine::create([
                    'journal_entry_id' => $journalEntry->id,
                    'coa_id' => $apAccount->id,
                    'debit' => 0,
                    'credit' => $pembelian->total_harga,
                    'memo' => 'Hutang pembelian - ' . $pembelian->vendor->nama
                ]);
            }
        }
    }
    
    /**
     * Get appropriate inventory account based on purchase detail
     */
    private function getInventoryAccount($detail)
    {
        if ($detail->bahan_baku_id) {
            // Bahan Baku - find specific account or use general
            $bahanBaku = $detail->bahanBaku;
            
            if ($bahanBaku) {
                // Try to find specific account based on bahan baku name
                $accountCode = $this->getBahanBakuAccountCode($bahanBaku->nama_bahan);
                $account = Coa::where('kode_akun', $accountCode)->first();
                
                if ($account) {
                    return $account;
                }
            }
            
            // Fallback to general bahan baku account
            return Coa::where('kode_akun', '114')->first(); // Pers. Bahan Baku
            
        } elseif ($detail->bahan_pendukung_id) {
            // Bahan Pendukung - find specific account
            $bahanPendukung = $detail->bahanPendukung;
            
            if ($bahanPendukung) {
                $accountCode = $this->getBahanPendukungAccountCode($bahanPendukung->nama_bahan);
                $account = Coa::where('kode_akun', $accountCode)->first();
                
                if ($account) {
                    return $account;
                }
            }
            
            // Fallback to general bahan pendukung account
            return Coa::where('kode_akun', '115')->first(); // Pers. Bahan Pendukung
        }
        
        return null;
    }
    
    /**
     * Get specific account code for bahan baku
     */
    private function getBahanBakuAccountCode($namaBahan)
    {
        $namaBahan = strtolower($namaBahan);
        
        if (strpos($namaBahan, 'ayam potong') !== false) {
            return '1141';
        } elseif (strpos($namaBahan, 'ayam kampung') !== false) {
            return '1142';
        } elseif (strpos($namaBahan, 'bebek') !== false) {
            return '1143';
        } elseif (strpos($namaBahan, 'ayam') !== false) {
            return '1144'; // Ayam lainnya
        }
        
        return '114'; // Default bahan baku
    }
    
    /**
     * Get specific account code for bahan pendukung
     */
    private function getBahanPendukungAccountCode($namaBahan)
    {
        $namaBahan = strtolower($namaBahan);
        
        if (strpos($namaBahan, 'air') !== false) {
            return '1150';
        } elseif (strpos($namaBahan, 'minyak goreng') !== false) {
            return '1151';
        } elseif (strpos($namaBahan, 'cabe merah') !== false) {
            return '11510';
        } elseif (strpos($namaBahan, 'gas') !== false) {
            return '1152';
        } elseif (strpos($namaBahan, 'tepung terigu') !== false) {
            return '1153';
        } elseif (strpos($namaBahan, 'tepung maizena') !== false) {
            return '1154';
        } elseif (strpos($namaBahan, 'lada') !== false) {
            return '1155';
        } elseif (strpos($namaBahan, 'kaldu') !== false) {
            return '1156';
        } elseif (strpos($namaBahan, 'listrik') !== false) {
            return '1157';
        } elseif (strpos($namaBahan, 'bawang putih') !== false) {
            return '1158';
        } elseif (strpos($namaBahan, 'kemasan') !== false) {
            return '1159';
        }
        
        return '115'; // Default bahan pendukung
    }
}
