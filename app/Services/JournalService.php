<?php

namespace App\Services;

use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use Illuminate\Support\Facades\DB;

class JournalService
{
    protected function accountId(string $code): int
    {
        $acc = Account::where('code', $code)->first();
        if (!$acc) {
            throw new \RuntimeException("Account code {$code} not found. Seed accounts first.");
        }
        return (int)$acc->id;
    }

    /**
     * Post a balanced journal entry with given lines. Each line element: ['code'=>account_code, 'debit'=>float, 'credit'=>float]
     */
    public function post(string $tanggal, string $refType, int $refId, string $memo, array $lines): JournalEntry
    {
        return DB::transaction(function () use ($tanggal, $refType, $refId, $memo, $lines) {
            $entry = JournalEntry::create([
                'tanggal' => $tanggal,
                'ref_type' => $refType,
                'ref_id' => $refId,
                'memo' => $memo,
            ]);

            $totalDebit = 0.0; $totalCredit = 0.0;
            foreach ($lines as $ln) {
                $aid = $this->accountId($ln['code']);
                $debit = (float)($ln['debit'] ?? 0); $credit = (float)($ln['credit'] ?? 0);
                JournalLine::create([
                    'journal_entry_id' => $entry->id,
                    'account_id' => $aid,
                    'debit' => $debit,
                    'credit' => $credit,
                ]);
                $totalDebit += $debit; $totalCredit += $credit;
            }

            // Optional: basic balance check
            if (round($totalDebit - $totalCredit, 2) !== 0.0) {
                throw new \RuntimeException('Journal not balanced: debit != credit');
            }

            return $entry;
        });
    }

    /**
     * Delete all journal entries for a given reference.
     */
    public function deleteByRef(string $refType, int $refId): void
    {
        DB::transaction(function () use ($refType, $refId) {
            $entries = JournalEntry::where('ref_type', $refType)->where('ref_id', $refId)->get();
            foreach ($entries as $e) {
                $e->delete(); // journal_lines will cascade
            }
        });
    }
}
