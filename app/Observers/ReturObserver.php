<?php

namespace App\Observers;

use App\Models\Retur;
use App\Models\JournalEntry;
use App\Models\JournalLine;

class ReturObserver
{
    /**
     * Handle the Retur "deleted" event.
     *
     * @param  \App\Models\Retur  $retur
     * @return void
     */
    public function deleted(Retur $retur)
    {
        // Delete associated journal entries when retur is deleted
        $this->deleteReturJournals($retur->id);
    }
    
    /**
     * Handle the Retur "updated" event.
     *
     * @param  \App\Models\Retur  $retur
     * @return void
     */
    public function updated(Retur $retur)
    {
        // If status changes from 'posted' to something else, delete journals
        if ($retur->isDirty('status') && $retur->status !== 'posted') {
            $this->deleteReturJournals($retur->id);
        }
    }
    
    /**
     * Delete journal entries for a specific retur
     */
    private function deleteReturJournals($returId)
    {
        $journals = JournalEntry::where('ref_type', 'purchase_return')
                               ->where('ref_id', $returId)
                               ->get();
        
        foreach ($journals as $journal) {
            // Delete journal lines first
            JournalLine::where('journal_entry_id', $journal->id)->delete();
            
            // Delete journal entry
            $journal->delete();
        }
    }
}
