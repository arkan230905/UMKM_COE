<?php

namespace App\Observers;

use App\Models\Pembelian;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Services\JournalService;
use Illuminate\Support\Facades\Log;

class PembelianObserver
{
    protected $journalService;

    public function __construct()
    {
        $this->journalService = new JournalService();
    }

    /**
     * Handle the Pembelian "created" event.
     *
     * @param  \App\Models\Pembelian  $pembelian
     * @return void
     */
    public function created(Pembelian $pembelian)
    {
        $this->createPembelianJournal($pembelian);
    }

    /**
     * Handle the Pembelian "updated" event.
     *
     * @param  \App\Models\Pembelian  $pembelian
     * @return void
     */
    public function updated(Pembelian $pembelian)
    {
        // Delete old journal entries and create new ones
        $this->deletePembelianJournals($pembelian->id);
        $this->createPembelianJournal($pembelian);
    }

    /**
     * Handle the Pembelian "deleted" event.
     *
     * @param  \App\Models\Pembelian  $pembelian
     * @return void
     */
    public function deleted(Pembelian $pembelian)
    {
        // Delete associated journal entries when pembelian is deleted
        $this->deletePembelianJournals($pembelian->id);
    }
    
    /**
     * Create journal entries for pembelian
     */
    private function createPembelianJournal(Pembelian $pembelian)
    {
        try {
            // Calculate total from details if total_harga is 0
            $total = $pembelian->total_harga ?? 0;
            if ($total == 0 && $pembelian->details && $pembelian->details->count() > 0) {
                $total = $pembelian->details->sum(function($detail) {
                    return ($detail->jumlah ?? 0) * ($detail->harga_satuan ?? 0);
                });
            }

            // Skip journal creation if total is 0
            if ($total <= 0) {
                Log::info('Skipping journal creation for pembelian with zero total', [
                    'pembelian_id' => $pembelian->id,
                    'total' => $total
                ]);
                return;
            }

            // Jurnal Pembelian dengan persediaan spesifik
            // Dr Persediaan Bahan Baku (102) = Total Bahan Baku
            // Dr Persediaan Bahan Pendukung (1105) = Total Bahan Pendukung
            // Cr Kas/Bank = Terbayar (sesuai bank yang digunakan)
            // Cr Hutang Usaha (2101) = Sisa Pembayaran (jika kredit)
            
            $entries = [];
            $totalBahanBaku = 0;
            $totalBahanPendukung = 0;
            
            // Hitung total per tipe item
            foreach($pembelian->details as $detail) {
                $subtotal = ($detail->jumlah ?? 0) * ($detail->harga_satuan ?? 0);
                
                if ($detail->tipe_item === 'bahan_baku') {
                    $totalBahanBaku += $subtotal;
                } elseif ($detail->tipe_item === 'bahan_pendukung') {
                    $totalBahanPendukung += $subtotal;
                }
            }
            
            // Debit Persediaan Bahan Baku
            if ($totalBahanBaku > 0) {
                $entries[] = ['code' => '102', 'debit' => $totalBahanBaku, 'credit' => 0];
            }
            
            // Debit Persediaan Bahan Pendukung
            if ($totalBahanPendukung > 0) {
                $entries[] = ['code' => '1105', 'debit' => $totalBahanPendukung, 'credit' => 0];
            }
            
            // Credit Kas/Bank atau Hutang Usaha
            if ($pembelian->payment_method === 'cash') {
                // Credit Kas (1101)
                $entries[] = ['code' => '1101', 'debit' => 0, 'credit' => $pembelian->terbayar ?? $total];
            } elseif ($pembelian->payment_method === 'transfer') {
                // Credit Bank BCA (1102) untuk transfer
                $entries[] = ['code' => '1102', 'debit' => 0, 'credit' => $pembelian->terbayar ?? $total];
            } else {
                // Credit Hutang Usaha
                $entries[] = ['code' => '2101', 'debit' => 0, 'credit' => $pembelian->sisa_pembayaran ?? $total];
            }
            
            // Post journal entry
            $this->journalService->post(
                $pembelian->tanggal->format('Y-m-d'),
                'purchase',
                $pembelian->id,
                'Pembelian ' . ($pembelian->vendor->nama_vendor ?? '') . ' - ' . $pembelian->nomor_pembelian,
                $entries
            );
            
            Log::info('Journal created for pembelian', [
                'pembelian_id' => $pembelian->id,
                'nomor_pembelian' => $pembelian->nomor_pembelian,
                'total' => $total,
                'total_bahan_baku' => $totalBahanBaku,
                'total_bahan_pendukung' => $totalBahanPendukung,
                'payment_method' => $pembelian->payment_method,
                'bank_id' => $pembelian->bank_id,
                'entries' => $entries
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to create journal for pembelian', [
                'pembelian_id' => $pembelian->id,
                'error' => $e->getMessage()
            ]);
            
            // Don't throw exception to avoid breaking pembelian creation
            // Just log the error for manual investigation
        }
    }
    
    /**
     * Delete journal entries for a specific pembelian
     */
    private function deletePembelianJournals($pembelianId)
    {
        try {
            $this->journalService->deleteByRef('purchase', $pembelianId);
            
            Log::info('Journal deleted for pembelian', [
                'pembelian_id' => $pembelianId
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to delete journal for pembelian', [
                'pembelian_id' => $pembelianId,
                'error' => $e->getMessage()
            ]);
        }
    }
}
