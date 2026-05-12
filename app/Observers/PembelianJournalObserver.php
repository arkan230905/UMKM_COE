<?php

namespace App\Observers;

use App\Models\Pembelian;
use App\Services\PembelianJournalService;
use Illuminate\Support\Facades\Log;

class PembelianJournalObserver
{
    protected $journalService;
    
    public function __construct(PembelianJournalService $journalService)
    {
        $this->journalService = $journalService;
    }
    
    /**
     * Handle the Pembelian "created" event.
     */
    public function created(Pembelian $pembelian): void
    {
        // Tunggu sampai semua detail tersimpan
        // Gunakan dispatch untuk menjalankan setelah transaksi selesai
        dispatch(function () use ($pembelian) {
            $this->createJournal($pembelian);
        })->afterResponse();
    }
    
    /**
     * Handle the Pembelian "updated" event.
     */
    public function updated(Pembelian $pembelian): void
    {
        // Hanya buat ulang jurnal jika ada perubahan yang mempengaruhi jurnal
        if ($this->shouldRecreateJournal($pembelian)) {
            dispatch(function () use ($pembelian) {
                $this->createJournal($pembelian);
            })->afterResponse();
        }
    }
    
    /**
     * Handle the Pembelian "deleted" event.
     */
    public function deleted(Pembelian $pembelian): void
    {
        // Hapus jurnal terkait
        $this->journalService->deleteExistingJournal($pembelian->id);
        
        Log::info("Jurnal pembelian dihapus", [
            'pembelian_id' => $pembelian->id,
            'nomor_pembelian' => $pembelian->nomor_pembelian
        ]);
    }
    
    /**
     * Buat jurnal untuk pembelian
     */
    private function createJournal(Pembelian $pembelian): void
    {
        try {
            // Reload pembelian dengan semua relasi
            $pembelian = Pembelian::with([
                'details.bahanBaku.coaPersediaan',
                'details.bahanPendukung.coaPersediaan', 
                'vendor',
                'kasBank'
            ])->find($pembelian->id);
            
            if (!$pembelian) {
                Log::warning("Pembelian tidak ditemukan saat membuat jurnal");
                return;
            }
            
            // Skip jika belum ada detail
            if (!$pembelian->details || $pembelian->details->isEmpty()) {
                Log::info("Pembelian {$pembelian->id} belum memiliki detail, skip jurnal");
                return;
            }
            
            $journal = $this->journalService->createJournalFromPembelian($pembelian);
            
            if ($journal) {
                Log::info("Jurnal pembelian berhasil dibuat via observer", [
                    'pembelian_id' => $pembelian->id,
                    'journal_id' => $journal->id
                ]);
            }
            
        } catch (\Exception $e) {
            Log::error("Error creating journal via observer for pembelian {$pembelian->id}: " . $e->getMessage());
        }
    }
    
    /**
     * Cek apakah perlu membuat ulang jurnal
     */
    private function shouldRecreateJournal(Pembelian $pembelian): bool
    {
        // Field yang mempengaruhi jurnal
        $journalAffectingFields = [
            'subtotal',
            'ppn_persen',
            'ppn_nominal', 
            'biaya_kirim',
            'total_harga',
            'payment_method',
            'bank_id',
            'vendor_id'
        ];
        
        foreach ($journalAffectingFields as $field) {
            if ($pembelian->isDirty($field)) {
                return true;
            }
        }
        
        return false;
    }
}