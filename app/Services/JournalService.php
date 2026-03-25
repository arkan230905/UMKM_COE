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
            
            // Log the auto-creation for debugging
            \Log::info("Auto-created Account from COA: {$code} - {$coa->nama_akun}");
            return (int)$acc->id;
        }

        // Final fallback: auto-create a minimal account with inferred type from code.
        // Mapping umum: 1=asset, 2=liability, 3=equity, 4=revenue, 5/6/7=expense
        $inferred = $this->inferTypeFromCode((string)$code);
        $acc = Account::create([
            'code' => (string)$code,
            'name' => 'Akun ' . (string)$code, // More descriptive name
            'type' => $inferred,
        ]);
        
        // Log the auto-creation for debugging
        \Log::info("Auto-created Account from code inference: {$code} - Akun {$code}");
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
     * Post a balanced journal entry with given lines. Each line element: ['code'=>account_code, 'debit'=>float, 'credit'=>float, 'memo'=>string (optional)]
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
                $lineMemo = $ln['memo'] ?? null; // Support per-line memo
                
                JournalLine::create([
                    'journal_entry_id' => $entry->id,
                    'account_id' => $aid,
                    'debit' => $debit,
                    'credit' => $credit,
                    'memo' => $lineMemo, // Add memo to journal line if supported
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

    /**
     * Sync all COA accounts to Account table
     */
    public function syncCoaToAccounts(): array
    {
        $stats = ['created' => 0, 'updated' => 0, 'skipped' => 0];
        
        $coas = Coa::all();
        foreach ($coas as $coa) {
            $account = Account::where('code', $coa->kode_akun)->first();
            
            if (!$account) {
                // Create new account from COA
                $type = $this->mapCoaTypeToAccountType((string)($coa->tipe_akun ?? ''));
                Account::create([
                    'code' => (string)$coa->kode_akun,
                    'name' => (string)($coa->nama_akun ?? $coa->kode_akun),
                    'type' => $type,
                ]);
                $stats['created']++;
            } elseif ($account->name === (string)$coa->kode_akun || $account->name === $coa->kode_akun) {
                // Update account with proper name from COA
                $account->update([
                    'name' => (string)($coa->nama_akun ?? $coa->kode_akun),
                    'type' => $this->mapCoaTypeToAccountType((string)($coa->tipe_akun ?? '')),
                ]);
                $stats['updated']++;
            } else {
                $stats['skipped']++;
            }
        }
        
        return $stats;
    }

    /**
     * Ensure all accounts used in journal lines have proper names
     */
    public function ensureAccountNames(): array
    {
        $stats = ['updated' => 0];
        
        // Get all accounts used in journal lines that might have generic names
        $accounts = Account::where(function($query) {
            $query->where('name', 'like', 'Akun %')
                  ->orWhere('name', '=', 'code')
                  ->orWhereRaw('name = code');
        })->get();
        
        foreach ($accounts as $account) {
            // Try to get name from COA
            $coa = Coa::where('kode_akun', $account->code)->first();
            if ($coa && !empty($coa->nama_akun)) {
                $account->update(['name' => $coa->nama_akun]);
                $stats['updated']++;
            }
        }
        
        return $stats;
    }

    /**
     * Create journal entries from Pembelian (Purchase)
     * Dr. Persediaan Bahan Baku/Pendukung | Cr. Kas/Bank/Utang Usaha
     */
    public static function createJournalFromPembelian($pembelian): void
    {
        $service = new static();
        
        // Delete existing journal entries for this pembelian
        $service->deleteByRef('purchase', $pembelian->id);
        
        // Skip if no details
        if (!$pembelian->details || $pembelian->details->isEmpty()) {
            return;
        }
        
        $lines = [];
        $totalAmount = 0;
        
        // Create debit entries for each purchased item (Persediaan)
        foreach ($pembelian->details as $detail) {
            $amount = ($detail->jumlah ?? 0) * ($detail->harga_satuan ?? 0);
            $totalAmount += $amount;
            
            // Determine COA account based on item type
            $coaAccount = null;
            
            if ($detail->bahan_baku_id && $detail->bahanBaku) {
                // Use specific COA from bahan baku if available
                $coaAccount = $detail->bahanBaku->coa_persediaan_id;
                
                // Fallback to general bahan baku account
                if (!$coaAccount) {
                    $coaAccount = '1104'; // Persediaan Bahan Baku
                }
            } elseif ($detail->bahan_pendukung_id && $detail->bahanPendukung) {
                // Use specific COA from bahan pendukung if available
                $coaAccount = $detail->bahanPendukung->coa_persediaan_id ?? null;
                
                // Fallback to general bahan pendukung account
                if (!$coaAccount) {
                    $coaAccount = '1107'; // Persediaan Bahan Pendukung
                }
            }
            
            // Default fallback
            if (!$coaAccount) {
                $coaAccount = '1104'; // Persediaan Bahan Baku
            }
            
            $lines[] = [
                'code' => $coaAccount,
                'debit' => $amount,
                'credit' => 0,
                'memo' => 'Pembelian ' . ($detail->nama_bahan ?? 'Item')
            ];
        }
        
        // Create credit entry based on payment method
        $creditAccount = null;
        $creditMemo = '';
        
        switch ($pembelian->payment_method) {
            case 'cash':
                $creditAccount = '101'; // Kas
                $creditMemo = 'Pembayaran tunai pembelian';
                break;
                
            case 'transfer':
                // Use specific bank account if available
                $creditAccount = $pembelian->bank_id ?? '102'; // Bank
                $creditMemo = 'Pembayaran transfer pembelian';
                break;
                
            case 'credit':
                $creditAccount = '201'; // Utang Usaha
                $creditMemo = 'Pembelian kredit';
                break;
                
            default:
                $creditAccount = '201'; // Default to Utang Usaha
                $creditMemo = 'Pembelian';
        }
        
        $lines[] = [
            'code' => $creditAccount,
            'debit' => 0,
            'credit' => $totalAmount,
            'memo' => $creditMemo
        ];
        
        // Create journal entry
        $memo = 'Pembelian #' . $pembelian->nomor_pembelian . ' - ' . ($pembelian->vendor->nama_vendor ?? 'Vendor');
        $tanggal = $pembelian->tanggal instanceof \Carbon\Carbon ? 
                   $pembelian->tanggal->format('Y-m-d') : 
                   $pembelian->tanggal;
        
        $service->post($tanggal, 'purchase', $pembelian->id, $memo, $lines);
    }

    /**
     * Create journal entries from Penjualan (Sales)
     * Dr. Kas/Bank/Piutang | Cr. Pendapatan Penjualan
     * Dr. HPP | Cr. Persediaan Barang Jadi
     */
    public static function createJournalFromPenjualan($penjualan): void
    {
        $service = new static();
        
        // Delete existing journal entries for this penjualan
        $service->deleteByRef('sale', $penjualan->id);
        
        $lines = [];
        $totalAmount = $penjualan->total_harga ?? 0;
        
        // Create debit entry based on payment method (Kas/Bank/Piutang)
        $debitAccount = null;
        $debitMemo = '';
        
        switch ($penjualan->payment_method) {
            case 'cash':
                $debitAccount = '101'; // Kas
                $debitMemo = 'Penerimaan tunai penjualan';
                break;
                
            case 'transfer':
                $debitAccount = '102'; // Bank
                $debitMemo = 'Penerimaan transfer penjualan';
                break;
                
            case 'credit':
                $debitAccount = '103'; // Piutang Usaha
                $debitMemo = 'Penjualan kredit';
                break;
                
            default:
                $debitAccount = '101'; // Default to Kas
                $debitMemo = 'Penerimaan penjualan';
        }
        
        $lines[] = [
            'code' => $debitAccount,
            'debit' => $totalAmount,
            'credit' => 0,
            'memo' => $debitMemo
        ];
        
        // Create credit entry for sales revenue
        $lines[] = [
            'code' => '401', // Pendapatan Penjualan
            'debit' => 0,
            'credit' => $totalAmount,
            'memo' => 'Pendapatan penjualan produk'
        ];
        
        // Create journal entry
        $memo = 'Penjualan #' . ($penjualan->nomor_penjualan ?? $penjualan->id);
        $tanggal = $penjualan->tanggal instanceof \Carbon\Carbon ? 
                   $penjualan->tanggal->format('Y-m-d') : 
                   $penjualan->tanggal;
        
        $service->post($tanggal, 'sale', $penjualan->id, $memo, $lines);
    }

    /**
     * Create journal entries from ExpensePayment
     * Dr. Beban | Cr. Kas/Bank
     */
    public static function createJournalFromExpensePayment($expensePayment): void
    {
        $service = new static();
        
        // Delete existing journal entries for this expense payment
        $service->deleteByRef('expense_payment', $expensePayment->id);
        
        $lines = [];
        $amount = $expensePayment->jumlah ?? 0;
        
        // Create debit entry for expense
        $expenseAccount = $expensePayment->coa_beban ?? '501'; // Default to Beban Operasional
        
        $lines[] = [
            'code' => $expenseAccount,
            'debit' => $amount,
            'credit' => 0,
            'memo' => 'Pembayaran beban'
        ];
        
        // Create credit entry based on payment method
        $creditAccount = ($expensePayment->coa_kasbank == '102') ? '102' : '101'; // Bank or Kas
        
        $lines[] = [
            'code' => $creditAccount,
            'debit' => 0,
            'credit' => $amount,
            'memo' => 'Pembayaran beban operasional'
        ];
        
        // Create journal entry
        $memo = 'Pembayaran Beban #' . $expensePayment->id;
        $tanggal = $expensePayment->tanggal instanceof \Carbon\Carbon ? 
                   $expensePayment->tanggal->format('Y-m-d') : 
                   $expensePayment->tanggal;
        
        $service->post($tanggal, 'expense_payment', $expensePayment->id, $memo, $lines);
    }
}
