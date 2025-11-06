<?php

namespace App\Services;

use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\Coa;
use Illuminate\Support\Facades\DB;

class JournalService
{
    protected function accountId(string $code): int
    {
        $acc = Account::where('code', $code)->first();
        if ($acc) {
            return (int)$acc->id;
        }

        // Fallback: auto-provision from COA if available
        $coa = Coa::where('kode_akun', $code)->first();
        if ($coa) {
            $type = $this->mapCoaTypeToAccountType((string)($coa->tipe_akun ?? ''));
            $acc = Account::create([
                'code' => (string)$code,
                'name' => (string)($coa->nama_akun ?? $code),
                'type' => $type,
            ]);
            return (int)$acc->id;
        }

        // Final fallback: auto-create a minimal account with inferred type from code.
        // Mapping umum: 1=asset, 2=liability, 3=equity, 4=revenue, 5/6/7=expense
        $inferred = $this->inferTypeFromCode((string)$code);
        $acc = Account::create([
            'code' => (string)$code,
            'name' => (string)$code,
            'type' => $inferred,
        ]);
        return (int)$acc->id;
    }

    protected function mapCoaTypeToAccountType(string $tipe): string
    {
        $t = strtolower(trim($tipe));
        return match ($t) {
            'asset', 'assets', 'aktiva' => 'asset',
            'liability', 'liabilities', 'utang', 'kewajiban' => 'liability',
            'equity', 'modal' => 'equity',
            'revenue', 'pendapatan' => 'revenue',
            'expense', 'beban' => 'expense',
            default => 'unknown',
        };
    }

    protected function inferTypeFromCode(string $code): string
    {
        $first = substr(preg_replace('/\D+/', '', $code), 0, 1);
        return match ($first) {
            '1' => 'asset',
            '2' => 'liability',
            '3' => 'equity',
            '4' => 'revenue',
            '5', '6', '7' => 'expense',
            default => 'asset', // safe default
        };
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
