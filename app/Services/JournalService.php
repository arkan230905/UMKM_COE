<?php

namespace App\Services;

use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\Coa;
use Illuminate\Support\Facades\DB;

class JournalService
{
    protected function coaId(string $code): int
    {
        // Langsung gunakan COA saja, tidak perlu tabel accounts
        $coa = Coa::where('kode_akun', $code)->first();
        if ($coa) {
            // Return the actual ID column, not the primary key
            return (int)$coa->getAttribute('id');
        }

        // Jika COA tidak ditemukan, buat error yang informatif
        throw new \RuntimeException("COA dengan kode {$code} tidak ditemukan. Silakan buat COA terlebih dahulu di master data.");
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
                $aid = $this->coaId($ln['code']);
                $debit = (float)($ln['debit'] ?? 0); $credit = (float)($ln['credit'] ?? 0);
                $lineMemo = $ln['memo'] ?? null; // Support per-line memo
                
                JournalLine::create([
                    'journal_entry_id' => $entry->id,
                    'coa_id' => $aid,
                    'debit' => $debit,
                    'credit' => $credit,
                    'memo' => $lineMemo, // Add memo to journal line if supported
                ]);
                $totalDebit += $debit; $totalCredit += $credit;
                
                // AUTOMATIC POSTING TO JURNAL UMUM (GENERAL LEDGER)
                // This ensures all journal entries automatically appear in Buku Besar
                // Check if entry already exists to prevent duplicates
                $existingJurnalUmum = \App\Models\JurnalUmum::where('coa_id', $aid)
                    ->where('tanggal', $tanggal)
                    ->where('referensi', $refType . '#' . $refId)
                    ->where('tipe_referensi', $refType)
                    ->where('debit', $debit)
                    ->where('kredit', $credit)
                    ->first();
                
                if (!$existingJurnalUmum) {
                    \App\Models\JurnalUmum::create([
                        'coa_id' => $aid,
                        'tanggal' => $tanggal,
                        'keterangan' => $lineMemo ?? $memo,
                        'debit' => $debit,
                        'kredit' => $credit,
                        'referensi' => $refType . '#' . $refId,
                        'tipe_referensi' => $refType,
                        'created_by' => auth()->id(),
                    ]);
                }
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
            // Delete from JournalEntry system
            $entries = JournalEntry::where('ref_type', $refType)->where('ref_id', $refId)->get();
            foreach ($entries as $e) {
                $e->delete(); // journal_lines will cascade
            }
            
            // Delete from JurnalUmum system (General Ledger)
            \App\Models\JurnalUmum::where('tipe_referensi', $refType)
                ->where('referensi', $refType . '#' . $refId)
                ->delete();
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
        $subtotalAmount = 0;
        
        // Create debit entries for each purchased item (Persediaan)
        foreach ($pembelian->details as $detail) {
            $amount = ($detail->jumlah ?? 0) * ($detail->harga_satuan ?? 0);
            $subtotalAmount += $amount;
            
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
        
        // Add PPN Masukan entry if there's PPN
        $ppnNominal = (float) ($pembelian->ppn_nominal ?? 0);
        if ($ppnNominal > 0) {
            $lines[] = [
                'code' => '1130', // PPN Masukan
                'debit' => $ppnNominal,
                'credit' => 0,
                'memo' => 'PPN Masukan ' . ($pembelian->ppn_persen ?? 0) . '%'
            ];
        }
        
        // Add Biaya Kirim entry if there's shipping cost
        $biayaKirim = (float) ($pembelian->biaya_kirim ?? 0);
        if ($biayaKirim > 0) {
            $lines[] = [
                'code' => '511', // Biaya Kirim/Angkut
                'debit' => $biayaKirim,
                'credit' => 0,
                'memo' => 'Biaya kirim pembelian'
            ];
        }
        
        // Calculate total amount (subtotal + PPN + biaya kirim)
        $totalAmount = $subtotalAmount + $ppnNominal + $biayaKirim;
        
        // Create credit entry based on payment method
        $creditAccount = null;
        $creditMemo = '';
        
        switch ($pembelian->payment_method) {
            case 'cash':
                // Use specific bank account if provided, otherwise default to Kas
                if ($pembelian->bank_id) {
                    // bank_id is stored as COA ID, not code
                    $bankCoa = \App\Models\Coa::find($pembelian->bank_id);
                    if ($bankCoa) {
                        $creditAccount = $bankCoa->kode_akun;
                        $creditMemo = 'Pembayaran tunai pembelian via ' . $bankCoa->nama_akun;
                    } else {
                        $creditAccount = '112'; // Kas
                        $creditMemo = 'Pembayaran tunai pembelian';
                    }
                } else {
                    $creditAccount = '112'; // Kas
                    $creditMemo = 'Pembayaran tunai pembelian';
                }
                break;
                
            case 'transfer':
                // Use specific bank account if provided, otherwise default to Kas di Bank
                if ($pembelian->bank_id) {
                    // bank_id is stored as COA ID, not code
                    $bankCoa = \App\Models\Coa::find($pembelian->bank_id);
                    if ($bankCoa) {
                        $creditAccount = $bankCoa->kode_akun;
                        $creditMemo = 'Pembayaran transfer pembelian via ' . $bankCoa->nama_akun;
                    } else {
                        $creditAccount = '1102'; // Kas di Bank
                        $creditMemo = 'Pembayaran transfer pembelian';
                    }
                } else {
                    $creditAccount = '1102'; // Kas di Bank
                    $creditMemo = 'Pembayaran transfer pembelian';
                }
                break;
                
            case 'credit':
                $creditAccount = '210'; // Utang Usaha
                $creditMemo = 'Pembelian kredit';
                break;
                
            default:
                $creditAccount = '210'; // Default to Utang Usaha
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
        $totalAmount = $penjualan->total ?? 0;
        
        // Create debit entry based on payment method (Kas/Bank/Piutang)
        $debitAccount = null;
        $debitMemo = '';
        
        switch ($penjualan->payment_method) {
            case 'cash':
                // Cari COA Kas yang ada di database
                $kasCoa = Coa::where('tipe_akun', 'Asset')
                    ->where(function($query) {
                        $query->where('nama_akun', 'like', '%kas%')
                              ->where('nama_akun', 'not like', '%bank%');
                    })
                    ->orWhere('kode_akun', '112') // Kas
                    ->orWhere('kode_akun', '101')
                    ->first();
                
                $debitAccount = $kasCoa ? $kasCoa->kode_akun : '112';
                $debitMemo = 'Penerimaan tunai penjualan';
                break;
                
            case 'transfer':
                // Cari COA Bank yang ada di database
                $bankCoa = Coa::where('tipe_akun', 'Asset')
                    ->where(function($query) {
                        $query->where('nama_akun', 'like', '%bank%')
                              ->orWhere('nama_akun', 'like', '%kas%bank%');
                    })
                    ->orWhere('kode_akun', '1102')
                    ->orWhere('kode_akun', '102')
                    ->first();
                
                $debitAccount = $bankCoa ? $bankCoa->kode_akun : '111'; // Kas Bank
                $debitMemo = 'Penerimaan transfer penjualan';
                break;
                
            case 'credit':
                // Cari COA Piutang yang ada di database
                $piutangCoa = Coa::where('tipe_akun', 'Asset')
                    ->where(function($query) {
                        $query->where('nama_akun', 'like', '%piutang%')
                              ->orWhere('nama_akun', 'like', '%receivable%');
                    })
                    ->orWhere('kode_akun', '1103')
                    ->orWhere('kode_akun', '103')
                    ->first();
                
                $debitAccount = $piutangCoa ? $piutangCoa->kode_akun : '113'; // Kas Kecil (default untuk piutang)
                $debitMemo = 'Penjualan kredit';
                break;
                
            default:
                // Default: gunakan COA Kas pertama yang ditemukan
                $defaultCoa = Coa::where('tipe_akun', 'Asset')
                    ->where(function($query) {
                        $query->where('nama_akun', 'like', '%kas%')
                              ->orWhere('kode_akun', '1101')
                              ->orWhere('kode_akun', '101');
                    })
                    ->first();
                
                $debitAccount = $defaultCoa ? $defaultCoa->kode_akun : '112'; // Default Kas
                $debitMemo = 'Penerimaan penjualan';
        }
        
        $lines[] = [
            'code' => $debitAccount,
            'debit' => $totalAmount,
            'credit' => 0,
            'memo' => $debitMemo
        ];
        
        // Create credit entry for sales revenue - gunakan COA 41 (PENDAPATAN)
        $penjualanCoa = Coa::where('kode_akun', '41')->first();
        
        $creditAccount = $penjualanCoa ? $penjualanCoa->kode_akun : '41';
        
        $lines[] = [
            'code' => $creditAccount,
            'debit' => 0,
            'credit' => $totalAmount,
            'memo' => 'Pendapatan penjualan produk'
        ];
        
        // Add HPP journal entries with detailed breakdown
        $hppLines = $service->createHPPLinesFromPenjualan($penjualan);
        $lines = array_merge($lines, $hppLines);
        
        // Create journal entry
        $memo = 'Penjualan #' . ($penjualan->nomor_penjualan ?? $penjualan->id);
        $tanggal = $penjualan->tanggal instanceof \Carbon\Carbon ? 
                   $penjualan->tanggal->format('Y-m-d') : 
                   $penjualan->tanggal;
        
        $service->post($tanggal, 'sale', $penjualan->id, $memo, $lines);
    }

    /**
     * Create HPP journal lines from Penjualan with detailed breakdown
     * Dr. HPP (Material, BTKL, BOP) | Cr. Persediaan Barang Jadi
     */
    private function createHPPLinesFromPenjualan($penjualan): array
    {
        $lines = [];
        
        // Get penjualan details
        if ($penjualan->details && $penjualan->details->count() > 0) {
            // Multi-item penjualan
            foreach ($penjualan->details as $detail) {
                $lines = array_merge($lines, $this->createHPPLinesForDetail($detail, $penjualan));
            }
        } else {
            // Single-item penjualan
            $lines = $this->createHPPLinesForSingleItem($penjualan);
        }
        
        return $lines;
    }
    
    /**
     * Create HPP lines for penjualan detail
     */
    private function createHPPLinesForDetail($detail, $penjualan): array
    {
        $lines = [];
        $qty = $detail->jumlah ?? 0;
        $product = $detail->produk;
        
        if (!$product || $qty <= 0) {
            return $lines;
        }
        
        // Get BOM components for this product
        $bomComponents = \App\Models\Bom::with(['details.bahanBaku', 'details.bahanPendukung'])
            ->where('produk_id', $product->id)
            ->get();
        
        $totalMaterialCost = 0;
        $totalBTKLCost = 0;
        $totalBOPCost = 0;
        
        // Material costs
        foreach ($bomComponents as $bom) {
            foreach ($bom->details as $detail) {
                if ($detail->bahanBaku) {
                    $materialCost = $detail->total_biaya * $qty;
                    $totalMaterialCost += $materialCost;
                    
                    $lines[] = [
                        'code' => $detail->bahanBaku->coa_persediaan_id ?? '1141',
                        'debit' => $materialCost,
                        'credit' => 0,
                        'memo' => "HPP Material - {$detail->bahanBaku->nama_bahan} untuk {$product->nama_produk} ({$qty} pcs)"
                    ];
                }
                
                if ($detail->bahanPendukung) {
                    $materialCost = $detail->total_biaya * $qty;
                    $totalMaterialCost += $materialCost;
                    
                    $lines[] = [
                        'code' => $detail->bahanPendukung->coa_persediaan_id ?? '1152',
                        'debit' => $materialCost,
                        'credit' => 0,
                        'memo' => "HPP Material - {$detail->bahanPendukung->nama_bahan} untuk {$product->nama_produk} ({$qty} pcs)"
                    ];
                }
            }
        }
        
        // BTKL costs
        $btklCost = ($product->btkl_default ?? 0) * $qty;
        $totalBTKLCost += $btklCost;
        
        if ($btklCost > 0) {
            $lines[] = [
                'code' => '52', // BIAYA TENAGA KERJA LANGSUNG (BTKL)
                'debit' => $btklCost,
                'credit' => 0,
                'memo' => "HPP BTKL untuk {$product->nama_produk} ({$qty} pcs)"
            ];
        }
        
        // BOP costs
        $bopCost = ($product->bop_default ?? 0) * $qty;
        $totalBOPCost += $bopCost;
        
        if ($bopCost > 0) {
            $lines[] = [
                'code' => '53', // BIAYA OVERHEAD PABRIK (BOP)
                'debit' => $bopCost,
                'credit' => 0,
                'memo' => "HPP BOP untuk {$product->nama_produk} ({$qty} pcs)"
            ];
        }
        
        // Credit persediaan barang jadi
        $totalHPP = $totalMaterialCost + $totalBTKLCost + $totalBOPCost;
        if ($totalHPP > 0) {
            // Find the appropriate persediaan barang jadi COA
            $persediaanCOA = $this->getPersediaanBarangJadiCOA($product);
            
            $lines[] = [
                'code' => $persediaanCOA,
                'debit' => 0,
                'credit' => $totalHPP,
                'memo' => "HPP Total - {$product->nama_produk} ({$qty} pcs)"
            ];
        }
        
        return $lines;
    }
    
    /**
     * Create HPP lines for single item penjualan
     */
    private function createHPPLinesForSingleItem($penjualan): array
    {
        $lines = [];
        $qty = $penjualan->jumlah ?? 0;
        $product = $penjualan->produk;
        
        if (!$product || $qty <= 0) {
            return $lines;
        }
        
        // Similar logic as above but for single item
        $totalMaterialCost = 0;
        $totalBTKLCost = ($product->btkl_default ?? 0) * $qty;
        $totalBOPCost = ($product->bop_default ?? 0) * $qty;
        
        // Get BOM cost
        $bomCost = \App\Models\Bom::where('produk_id', $product->id)->sum('total_biaya');
        $totalMaterialCost = $bomCost * $qty;
        
        // Create material lines (simplified for single item)
        if ($totalMaterialCost > 0) {
            $lines[] = [
                'code' => '117', // Barang Dalam Proses (WIP) - temporary
                'debit' => $totalMaterialCost,
                'credit' => 0,
                'memo' => "HPP Material untuk {$product->nama_produk} ({$qty} pcs)"
            ];
        }
        
        if ($totalBTKLCost > 0) {
            $lines[] = [
                'code' => '52', // BIAYA TENAGA KERJA LANGSUNG (BTKL)
                'debit' => $totalBTKLCost,
                'credit' => 0,
                'memo' => "HPP BTKL untuk {$product->nama_produk} ({$qty} pcs)"
            ];
        }
        
        if ($totalBOPCost > 0) {
            $lines[] = [
                'code' => '53', // BIAYA OVERHEAD PABRIK (BOP)
                'debit' => $totalBOPCost,
                'credit' => 0,
                'memo' => "HPP BOP untuk {$product->nama_produk} ({$qty} pcs)"
            ];
        }
        
        // Credit persediaan barang jadi
        $totalHPP = $totalMaterialCost + $totalBTKLCost + $totalBOPCost;
        if ($totalHPP > 0) {
            $persediaanCOA = $this->getPersediaanBarangJadiCOA($product);
            
            $lines[] = [
                'code' => $persediaanCOA,
                'debit' => 0,
                'credit' => $totalHPP,
                'memo' => "HPP Total - {$product->nama_produk} ({$qty} pcs)"
            ];
        }
        
        return $lines;
    }
    
    /**
     * Get appropriate persediaan barang jadi COA for product
     */
    private function getPersediaanBarangJadiCOA($product): string
    {
        // Try to find specific COA for product
        if ($product->coa_persediaan_id) {
            return $product->coa_persediaan_id;
        }
        
        // Default persediaan barang jadi COAs based on product type
        if (strpos(strtolower($product->nama_produk), 'macdi') !== false) {
            return '1161'; // Persediaan Ayam Crispy Macdi
        } elseif (strpos(strtolower($product->nama_produk), 'bundo') !== false) {
            return '1162'; // Persediaan Ayam Goreng Bundo
        }
        
        // Default to general persediaan barang jadi
        return '116'; // Persediaan Barang Jadi
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
        $amount = $expensePayment->nominal_pembayaran ?? 0;
        
        // Create debit entry for expense
        $expenseAccount = $expensePayment->coa_beban_id; // Ambil dari database
        
        $lines[] = [
            'code' => $expenseAccount,
            'debit' => $amount,
            'credit' => 0,
            'memo' => 'Pembayaran beban'
        ];
        
        // Create credit entry based on payment method
        $creditAccount = $expensePayment->coa_kasbank; // Ambil dari database
        
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

    /**
     * Create journal entry from pelunasan utang
     */
    public static function createJournalFromPelunasanUtang($pelunasanUtang): void
    {
        $service = new static();
        
        // Delete existing journal entries for this pelunasan utang
        $service->deleteByRef('debt_payment', $pelunasanUtang->id);
        
        $lines = [];
        $amount = $pelunasanUtang->jumlah ?? 0;
        
        // Debit: COA Pelunasan yang dipilih user (mengurangi utang)
        $coaPelunasan = $pelunasanUtang->coaPelunasan;
        $kodeCoaPelunasan = $coaPelunasan ? $coaPelunasan->kode_akun : '210'; // Default ke Hutang Usaha jika tidak ada
        
        $lines[] = [
            'code' => $kodeCoaPelunasan,
            'debit' => $amount,
            'credit' => 0,
            'memo' => 'Pelunasan utang - ' . ($pelunasanUtang->pembelian->vendor->nama_vendor ?? 'Vendor')
        ];
        
        // Credit: Kas/Bank account (mengurangi kas/bank)
        $akunKas = $pelunasanUtang->akunKas;
        $kodeAkun = $akunKas ? $akunKas->kode_akun : '112'; // Default ke Kas jika tidak ada
        
        $lines[] = [
            'code' => $kodeAkun,
            'debit' => 0,
            'credit' => $amount,
            'memo' => 'Pembayaran utang via ' . ($akunKas->nama_akun ?? 'Kas')
        ];
        
        // Create journal entry
        $memo = 'Pelunasan Utang #' . $pelunasanUtang->kode_transaksi . ' - ' . ($pelunasanUtang->pembelian->vendor->nama_vendor ?? 'Vendor');
        $tanggal = $pelunasanUtang->tanggal instanceof \Carbon\Carbon ? 
                   $pelunasanUtang->tanggal->format('Y-m-d') : 
                   $pelunasanUtang->tanggal;
        
        $service->post($tanggal, 'debt_payment', $pelunasanUtang->id, $memo, $lines);
    }

    /**
     * Create journal entries from ReturPenjualan (Sales Return)
     * Dr. Retur Penjualan | Cr. Kas/Bank (for refund)
     * Dr. Persediaan | Cr. HPP (for stock return)
     */
    public static function createJournalFromReturPenjualan($returPenjualan): void
    {
        $service = new static();
        
        // Delete existing journal entries for this retur penjualan
        $service->deleteByRef('sales_return', $returPenjualan->id);
        
        // Skip journal for tukar_barang (no financial impact)
        if ($returPenjualan->jenis_retur === 'tukar_barang') {
            return;
        }
        
        $lines = [];
        $totalAmount = $returPenjualan->total_retur ?? 0;
        
        // Create debit entry for sales return (Retur Penjualan account)
        $returCoa = Coa::where('tipe_akun', 'Revenue')
            ->where(function($query) {
                $query->where('nama_akun', 'like', '%retur%')
                      ->orWhere('nama_akun', 'like', '%return%');
            })
            ->orWhere('kode_akun', '4201')
            ->first();
        
        $returAccount = $returCoa ? $returCoa->kode_akun : '4201'; // Retur Penjualan
        
        $lines[] = [
            'code' => $returAccount,
            'debit' => $totalAmount,
            'credit' => 0,
            'memo' => 'Retur penjualan - ' . $returPenjualan->jenis_retur
        ];
        
        // Create credit entry based on return type
        if ($returPenjualan->jenis_retur === 'refund') {
            // Credit to Kas/Bank (cash refund)
            $kasCoa = Coa::where('tipe_akun', 'Asset')
                ->where(function($query) {
                    $query->where('nama_akun', 'like', '%kas%')
                          ->where('nama_akun', 'not like', '%bank%');
                })
                ->orWhere('kode_akun', '1101')
                ->first();
            
            $kasAccount = $kasCoa ? $kasCoa->kode_akun : '1101';
            
            $lines[] = [
                'code' => $kasAccount,
                'debit' => 0,
                'credit' => $totalAmount,
                'memo' => 'Refund retur penjualan'
            ];
        } elseif ($returPenjualan->jenis_retur === 'kredit') {
            // Credit to Piutang (credit note)
            $piutangCoa = Coa::where('tipe_akun', 'Asset')
                ->where(function($query) {
                    $query->where('nama_akun', 'like', '%piutang%')
                          ->orWhere('nama_akun', 'like', '%receivable%');
                })
                ->orWhere('kode_akun', '1103')
                ->first();
            
            $piutangAccount = $piutangCoa ? $piutangCoa->kode_akun : '1103';
            
            $lines[] = [
                'code' => $piutangAccount,
                'debit' => 0,
                'credit' => $totalAmount,
                'memo' => 'Kredit note retur penjualan'
            ];
        }
        
        // Create journal entry
        $memo = 'Retur Penjualan #' . $returPenjualan->nomor_retur . ' - ' . ucfirst($returPenjualan->jenis_retur);
        $tanggal = $returPenjualan->tanggal instanceof \Carbon\Carbon ? 
                   $returPenjualan->tanggal->format('Y-m-d') : 
                   $returPenjualan->tanggal;
        
        $service->post($tanggal, 'sales_return', $returPenjualan->id, $memo, $lines);
    }

    /**
     * Sync existing transactions to ensure all are posted to both journal systems
     */
    public static function syncAllTransactionsToJurnalUmum(): array
    {
        $stats = ['synced' => 0, 'errors' => 0, 'skipped' => 0];
        
        try {
            // Sync all Penjualan transactions
            $penjualans = \App\Models\Penjualan::all();
            foreach ($penjualans as $penjualan) {
                try {
                    static::createJournalFromPenjualan($penjualan);
                    $stats['synced']++;
                } catch (\Exception $e) {
                    $stats['errors']++;
                    \Log::error('Error syncing penjualan journal: ' . $e->getMessage(), ['penjualan_id' => $penjualan->id]);
                }
            }
            
            // Sync all Pembelian transactions
            $pembelians = \App\Models\Pembelian::all();
            foreach ($pembelians as $pembelian) {
                try {
                    static::createJournalFromPembelian($pembelian);
                    $stats['synced']++;
                } catch (\Exception $e) {
                    $stats['errors']++;
                    \Log::error('Error syncing pembelian journal: ' . $e->getMessage(), ['pembelian_id' => $pembelian->id]);
                }
            }
            
            // Sync all ReturPenjualan transactions
            $returPenjualans = \App\Models\ReturPenjualan::all();
            foreach ($returPenjualans as $retur) {
                try {
                    static::createJournalFromReturPenjualan($retur);
                    $stats['synced']++;
                } catch (\Exception $e) {
                    $stats['errors']++;
                    \Log::error('Error syncing retur penjualan journal: ' . $e->getMessage(), ['retur_id' => $retur->id]);
                }
            }
            
            // Sync all PelunasanUtang transactions
            $pelunasanUtangs = \App\Models\PelunasanUtang::all();
            foreach ($pelunasanUtangs as $pelunasan) {
                try {
                    static::createJournalFromPelunasanUtang($pelunasan);
                    $stats['synced']++;
                } catch (\Exception $e) {
                    $stats['errors']++;
                    \Log::error('Error syncing pelunasan utang journal: ' . $e->getMessage(), ['pelunasan_id' => $pelunasan->id]);
                }
            }
            
        } catch (\Exception $e) {
            \Log::error('Error in syncAllTransactionsToJurnalUmum: ' . $e->getMessage());
            $stats['errors']++;
        }
        
        return $stats;
    }
}
