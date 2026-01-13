<?php

namespace App\Observers;

use App\Models\Pembelian;
use App\Models\JournalEntry;
use App\Models\JournalLine;

class PembelianObserver
{
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
     * Delete journal entries for a specific pembelian
     */
    private function deletePembelianJournals($pembelianId)
    {
        $journals = JournalEntry::where('ref_type', 'purchase')
                               ->where('ref_id', $pembelianId)
                               ->get();
        
        foreach ($journals as $journal) {
            // Delete journal lines first
            JournalLine::where('journal_entry_id', $journal->id)->delete();
            
            // Delete journal entry
            $journal->delete();
        }
    }
}
