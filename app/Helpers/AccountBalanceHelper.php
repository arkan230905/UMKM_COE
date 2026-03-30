<?php

if (!function_exists('calculateAccountBalance')) {
    /**
     * Calculate account balance with proper logic based on account type
     */
    function calculateAccountBalance($coa, $periode = null) {
        $saldo = 0;
        
        // Get journal lines for this account up to selected period
        $journalLines = \App\Models\JournalLine::where('coa_id', $coa->id)
            ->whereHas('entry', function($q) use ($periode) {
                if ($periode) {
                    $q->whereDate('tanggal', '<=', $periode . '-31');
                }
            })->get();
        
        foreach ($journalLines as $line) {
            if ($coa->saldo_normal === 'debit') {
                $saldo += $line->debit - $line->credit;
            } else {
                $saldo += $line->credit - $line->debit;
            }
        }
        
        // Add initial balance
        $saldo += $coa->saldo_awal ?? 0;
        
        return $saldo;
    }
    
    /**
     * Get debit and credit totals for an account
     */
    function getAccountDebitCredit($coa, $periode = null) {
        $journalLines = \App\Models\JournalLine::where('coa_id', $coa->id)
            ->whereHas('entry', function($q) use ($periode) {
                if ($periode) {
                    $q->whereDate('tanggal', '<=', $periode . '-31');
                }
            })->get();
        
        return [
            'debit' => $journalLines->sum('debit'),
            'credit' => $journalLines->sum('credit')
        ];
    }
}