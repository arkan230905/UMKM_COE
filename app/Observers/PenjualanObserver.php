<?php

namespace App\Observers;

use App\Models\Penjualan;
use App\Models\JournalEntry;
use App\Models\JournalLine;

class PenjualanObserver
{
    /**
     * Handle the Penjualan "deleted" event.
     *
     * @param  \App\Models\Penjualan  $penjualan
     * @return void
     */
    public function deleted(Penjualan $penjualan)
    {
        // Delete associated journal entries when penjualan is deleted
        $this->deletePenjualanJournals($penjualan->id);
    }
    
    /**
     * Delete journal entries for a specific penjualan
     */
    private function deletePenjualanJournals($penjualanId)
    {
        $journals = JournalEntry::where('ref_type', 'sale')
                               ->where('ref_id', $penjualanId)
                               ->get();
        
        foreach ($journals as $journal) {
            // Delete journal lines first
            JournalLine::where('journal_entry_id', $journal->id)->delete();
            
            // Delete journal entry
            $journal->delete();
        }
    }
}
