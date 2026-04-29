<?php

namespace App\Services;

use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\Coa;
use Illuminate\Support\Facades\DB;

class JournalService
{
    protected function coaId(string $code, ?int $userId = null): int
    {
        // Bypass global scope, filter by user_id if provided
        $query = Coa::withoutGlobalScopes()->where('kode_akun', $code);
        
        if ($userId) {
            $coa = (clone $query)->where('user_id', $userId)->first();
            if ($coa) return (int)$coa->getAttribute('id');
        }
        
        // Fallback: ambil berdasarkan auth user
        if (auth()->check()) {
            $coa = (clone $query)->where('user_id', auth()->id())->first();
            if ($coa) return (int)$coa->getAttribute('id');
        }
        
        // Last fallback: ambil yang pertama
        $coa = $query->first();
        if ($coa) return (int)$coa->getAttribute('id');

        throw new \RuntimeException("COA dengan kode {$code} tidak ditemukan. Silakan buat COA terlebih dahulu di master data.");
    }

    /**
     * Post a balanced journal entry with given lines. Each line element: ['code'=>account_code, 'debit'=>float, 'credit'=>float, 'memo'=>string (optional)]
     */
    public function post(string $tanggal, string $refType, int $refId, string $memo, array $lines, ?int $userId = null): JournalEntry
    {
        return DB::transaction(function () use ($tanggal, $refType, $refId, $memo, $lines, $userId) {
            $entry = JournalEntry::create([
                'tanggal' => $tanggal,
                'ref_type' => $refType,
                'ref_id' => $refId,
                'memo' => $memo,
            ]);

            $totalDebit = 0.0; $totalCredit = 0.0;
            foreach ($lines as $ln) {
                // Handle both 'code' and 'coa_id' fields
                if (isset($ln['coa_id'])) {
                    $aid = (int)$ln['coa_id'];
                } else {
                    $aid = $this->coaId($ln['code'], $userId);
                }
                
                $debit = (float)($ln['debit'] ?? 0); $credit = (float)($ln['credit'] ?? 0);
                $lineMemo = $ln['memo'] ?? null;
                
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
                'code' => '127', // PPN Masukan
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
                    // bank_id is stored as COA ID, not code - use it directly
                    $bankCoa = \App\Models\Coa::find($pembelian->bank_id);
                    if ($bankCoa) {
                        // Use the COA ID directly instead of resolving by code
                        $lines[] = [
                            'coa_id' => $bankCoa->id, // Use direct COA ID
                            'debit' => 0,
                            'credit' => $totalAmount,
                            'memo' => 'Pembayaran tunai pembelian via ' . $bankCoa->nama_akun
                        ];
                        
                        // Create journal entry
                        $memo = 'Pembelian #' . $pembelian->nomor_pembelian . ' - ' . ($pembelian->vendor->nama_vendor ?? 'Vendor');
                        $tanggal = $pembelian->tanggal instanceof \Carbon\Carbon ? 
                                   $pembelian->tanggal->format('Y-m-d') : 
                                   $pembelian->tanggal;
                        
                        $service->post($tanggal, 'purchase', $pembelian->id, $memo, $lines);
                        return; // Exit early since we handled the credit line directly
                    } else {
                        $creditAccount = '1111'; // Use a more specific Kas code
                        $creditMemo = 'Pembayaran tunai pembelian';
                    }
                } else {
                    $creditAccount = '1111'; // Use a more specific Kas code
                    $creditMemo = 'Pembayaran tunai pembelian';
                }
                break;
                
            case 'transfer':
                // Use specific bank account if provided, otherwise default to Kas di Bank
                if ($pembelian->bank_id) {
                    // bank_id is stored as COA ID, not code - use it directly
                    $bankCoa = \App\Models\Coa::find($pembelian->bank_id);
                    if ($bankCoa) {
                        // Use the COA ID directly instead of resolving by code
                        $lines[] = [
                            'coa_id' => $bankCoa->id, // Use direct COA ID
                            'debit' => 0,
                            'credit' => $totalAmount,
                            'memo' => 'Pembayaran transfer pembelian via ' . $bankCoa->nama_akun
                        ];
                        
                        // Create journal entry
                        $memo = 'Pembelian #' . $pembelian->nomor_pembelian . ' - ' . ($pembelian->vendor->nama_vendor ?? 'Vendor');
                        $tanggal = $pembelian->tanggal instanceof \Carbon\Carbon ? 
                                   $pembelian->tanggal->format('Y-m-d') : 
                                   $pembelian->tanggal;
                        
                        $service->post($tanggal, 'purchase', $pembelian->id, $memo, $lines);
                        return; // Exit early since we handled the credit line directly
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
        
        // ── Debit: gunakan coa_id yang dipilih user (Terima di) ─────────────
        $debitAccount = null;
        $debitMemo    = '';
        $userId = $penjualan->user_id ?? null;

        // Prioritas: coa_id dari record penjualan (akun "Terima di" yang dipilih user)
        if ($penjualan->coa_id) {
            $selectedCoa = Coa::withoutGlobalScopes()->find($penjualan->coa_id);
            if ($selectedCoa) {
                $debitAccount = $selectedCoa->kode_akun;
                $debitMemo    = 'Penerimaan penjualan - ' . $selectedCoa->nama_akun;
            }
        }

        // Fallback berdasarkan payment_method + user_id
        if (!$debitAccount) {
            $findDebitCoa = function(string $namaAkun, string $tipeAkun) use ($userId) {
                $q = Coa::withoutGlobalScopes()->where('nama_akun', $namaAkun)->where('tipe_akun', $tipeAkun);
                if ($userId) {
                    $found = (clone $q)->where('user_id', $userId)->first();
                    if ($found) return $found;
                }
                return $q->orderBy('id', 'desc')->first();
            };

            switch ($penjualan->payment_method) {
                case 'cash':
                    $coa = $findDebitCoa('Kas', 'Asset');
                    $debitAccount = $coa ? $coa->kode_akun : '1111';
                    $debitMemo    = 'Penerimaan tunai penjualan';
                    break;
                case 'transfer':
                    $coa = Coa::withoutGlobalScopes()
                               ->where('tipe_akun', 'Asset')
                               ->where('nama_akun', 'like', '%bank%')
                               ->when($userId, fn($q) => $q->where('user_id', $userId))
                               ->first();
                    $debitAccount = $coa ? $coa->kode_akun : '1111';
                    $debitMemo    = 'Penerimaan transfer penjualan';
                    break;
                case 'credit':
                    $coa = Coa::withoutGlobalScopes()
                               ->where('tipe_akun', 'Asset')
                               ->where('nama_akun', 'like', '%piutang%')
                               ->when($userId, fn($q) => $q->where('user_id', $userId))
                               ->first();
                    $debitAccount = $coa ? $coa->kode_akun : '118';
                    $debitMemo    = 'Penjualan kredit';
                    break;
                default:
                    $coa = $findDebitCoa('Kas', 'Asset');
                    $debitAccount = $coa ? $coa->kode_akun : '1111';
                    $debitMemo    = 'Penerimaan penjualan';
            }
        }
        
        $lines[] = [
            'code' => $debitAccount,
            'debit' => $totalAmount,
            'credit' => 0,
            'memo' => $debitMemo
        ];
        
        // ── Credit lines: Penjualan, PPN Keluaran, Ongkir ────────────────────
        $subtotalProduk = 0;
        foreach ($penjualan->details as $d) {
            $subtotalProduk += (float)($d->subtotal ?? ((float)$d->harga_satuan * (float)$d->jumlah));
        }
        if ($subtotalProduk <= 0) {
            $subtotalProduk = (float)($penjualan->total ?? 0)
                            - (float)($penjualan->biaya_ongkir ?? 0)
                            - (float)($penjualan->biaya_ppn ?? 0);
        }

        $biayaOngkir = (float)($penjualan->biaya_ongkir ?? 0);
        $biayaPPN    = (float)($penjualan->biaya_ppn    ?? 0);
        $userId      = $penjualan->user_id ?? null;

        // Helper: cari COA tanpa global scope, filter user_id jika ada
        $findCoa = function(string $namaAkun, ?string $tipeAkun = null) use ($userId) {
            $q = Coa::withoutGlobalScopes()->where('nama_akun', $namaAkun);
            if ($tipeAkun) $q->where('tipe_akun', $tipeAkun);
            if ($userId) {
                $found = (clone $q)->where('user_id', $userId)->first();
                if ($found) return $found;
            }
            return $q->orderBy('id', 'desc')->first();
        };

        // Cr. Penjualan (nama='Penjualan', tipe=Revenue)
        $penjualanCoa = $findCoa('Penjualan', 'Revenue');
        $lines[] = [
            'code'   => $penjualanCoa ? $penjualanCoa->kode_akun : '41',
            'debit'  => 0,
            'credit' => $subtotalProduk,
            'memo'   => 'Pendapatan penjualan produk',
        ];

        // Cr. PPN Keluaran (nama='PPN Keluaran', tipe=Liability)
        if ($biayaPPN > 0) {
            $ppnCoa = $findCoa('PPN Keluaran', 'Liability');
            $lines[] = [
                'code'   => $ppnCoa ? $ppnCoa->kode_akun : '212',
                'debit'  => 0,
                'credit' => $biayaPPN,
                'memo'   => 'PPN Keluaran',
            ];
        }

        // Cr. Pendapatan Lain-lain / ongkir (nama LIKE 'Pendapatan Lain%')
        if ($biayaOngkir > 0) {
            $ongkirCoa = null;
            $qOngkir = Coa::withoutGlobalScopes()
                ->where('nama_akun', 'like', 'Pendapatan Lain%')
                ->whereIn('tipe_akun', ['Revenue', 'Pendapatan']);
            if ($userId) {
                $ongkirCoa = (clone $qOngkir)->where('user_id', $userId)->first();
            }
            if (!$ongkirCoa) {
                $ongkirCoa = $qOngkir->orderBy('id', 'desc')->first();
            }
            $lines[] = [
                'code'   => $ongkirCoa ? $ongkirCoa->kode_akun : '42',
                'debit'  => 0,
                'credit' => $biayaOngkir,
                'memo'   => 'Pendapatan ongkir',
            ];
        }
        
        // Add HPP journal entries with detailed breakdown
        $hppLines = $service->createHPPLinesFromPenjualan($penjualan);
        $lines = array_merge($lines, $hppLines);
        
        // Create journal entry
        $memo = 'Penjualan #' . ($penjualan->nomor_penjualan ?? $penjualan->id);
        $tanggal = $penjualan->tanggal instanceof \Carbon\Carbon ? 
                   $penjualan->tanggal->format('Y-m-d') : 
                   $penjualan->tanggal;
        
        $service->post($tanggal, 'sale', $penjualan->id, $memo, $lines, $userId);
    }

    /**
     * Create HPP journal lines from Penjualan.
     * Per produk: Dr. HPP (nama produk) | Cr. Persediaan Barang Jadi (nama produk)
     * Nilai HPP = hpp/harga_pokok produk × qty terjual.
     */
    private function createHPPLinesFromPenjualan($penjualan): array
    {
        $lines  = [];
        $userId = $penjualan->user_id ?? auth()->id() ?? null;

        // Pastikan COA yang dibuat selalu punya user_id
        if (!$userId && auth()->check()) {
            $userId = auth()->id();
        }

        // ── Hapus HPP lines lama untuk mencegah duplikat ─────────────
        $entry = \App\Models\JournalEntry::where('ref_type', 'sale')
            ->where('ref_id', $penjualan->id)
            ->first();

        if ($entry) {
            $tanggal = $penjualan->tanggal instanceof \Carbon\Carbon
                ? $penjualan->tanggal->format('Y-m-d')
                : $penjualan->tanggal;

            $oldLines = \App\Models\JournalLine::where('journal_entry_id', $entry->id)
                ->whereHas('coa', function($q) {
                    $q->where(function($q2) {
                        $q2->whereIn('tipe_akun', ['Beban','HPP','Expense','Cost'])
                           ->where('nama_akun', 'like', '%HPP%');
                    })->orWhere(function($q2) {
                        $q2->whereIn('tipe_akun', ['Asset','Aset'])
                           ->where('nama_akun', 'like', '%Barang Jadi%');
                    });
                })
                ->get();

            foreach ($oldLines as $jl) {
                \App\Models\JurnalUmum::where('coa_id', $jl->coa_id)
                    ->where('tanggal', $tanggal)
                    ->where('referensi', 'sale#' . $penjualan->id)
                    ->where(function($q) use ($jl) {
                        $q->where('debit', $jl->debit)->where('kredit', $jl->credit);
                    })
                    ->delete();
                $jl->delete();
            }
        }
        // ─────────────────────────────────────────────────────────────

        // Kumpulkan item penjualan
        $items = [];
        if ($penjualan->details && $penjualan->details->count() > 0) {
            foreach ($penjualan->details as $detail) {
                $produk = $detail->produk;
                if (!$produk) continue;
                $items[] = ['produk' => $produk, 'qty' => (float)($detail->jumlah ?? 0)];
            }
        } elseif ($penjualan->produk) {
            $items[] = [
                'produk' => $penjualan->produk,
                'qty'    => (float)($penjualan->jumlah ?? 1),
            ];
        }

        foreach ($items as $item) {
            $produk = $item['produk'];
            $qty    = $item['qty'];
            if ($qty <= 0) continue;

            // Nilai HPP per unit dari kolom hpp / harga_pokok / harga_bom
            $hppPerUnit = (float)($produk->hpp ?? $produk->harga_pokok ?? $produk->harga_bom ?? 0);
            $totalHPP   = round($hppPerUnit * $qty);
            if ($totalHPP <= 0) continue;

            $namaProduk = $produk->nama_produk;

            // Cari COA HPP yang sesuai (tidak buat baru)
            $coaHppKode = $this->findCoaHpp($produk, $userId);
            // Cari COA Persediaan Barang Jadi yang sesuai (tidak buat baru)
            $coaPersediaanKode = $this->findCoaPersediaan($produk, $userId);

            // Jika salah satu COA tidak ditemukan, skip dan log warning
            if (!$coaHppKode || !$coaPersediaanKode) {
                $missing = [];
                if (!$coaHppKode) $missing[] = "COA HPP untuk '{$namaProduk}'";
                if (!$coaPersediaanKode) $missing[] = "COA Persediaan Barang Jadi untuk '{$namaProduk}'";
                \Log::warning('HPP Journal skipped - COA tidak ditemukan: ' . implode(', ', $missing));
                continue;
            }

            // Dr. HPP (nama produk)
            $lines[] = [
                'code'   => $coaHppKode,
                'debit'  => $totalHPP,
                'credit' => 0,
                'memo'   => "HPP {$namaProduk} ({$qty} pcs)",
            ];

            // Cr. Persediaan Barang Jadi (nama produk)
            $lines[] = [
                'code'   => $coaPersediaanKode,
                'debit'  => 0,
                'credit' => $totalHPP,
                'memo'   => "Persediaan Barang Jadi {$namaProduk} ({$qty} pcs)",
            ];
        }

        return $lines;
    }

    /**
     * Cari COA HPP untuk produk dari COA yang sudah ada.
     * Prioritas: COA spesifik produk → COA HPP umum (kode 56).
     * Return null jika tidak ditemukan.
     */
    private function findCoaHpp($produk, $userId): ?string
    {
        $namaProduk = $produk->nama_produk;

        // 1. Cari COA spesifik: "HPP {nama_produk}" atau "Harga Pokok {nama_produk}"
        $spesifik = Coa::withoutGlobalScopes()
            ->where(function($q) use ($namaProduk) {
                $q->where('nama_akun', 'HPP ' . $namaProduk)
                  ->orWhere('nama_akun', 'Harga Pokok Penjualan ' . $namaProduk)
                  ->orWhere('nama_akun', 'like', '%HPP%' . $namaProduk . '%');
            })
            ->whereIn('tipe_akun', ['Beban', 'HPP', 'Expense', 'Cost'])
            ->where(function($q) use ($userId) {
                $q->where('user_id', $userId)->orWhereNull('user_id');
            })
            ->orderByRaw('CASE WHEN user_id = ? THEN 0 ELSE 1 END', [$userId ?? 0])
            ->first();

        if ($spesifik) return (string)$spesifik->kode_akun;

        // 2. Cari COA HPP umum: kode 56 "Harga Pokok Penjualan"
        $umum = Coa::withoutGlobalScopes()
            ->where(function($q) {
                $q->where('kode_akun', '56')
                  ->orWhere('nama_akun', 'Harga Pokok Penjualan')
                  ->orWhere('nama_akun', 'HPP');
            })
            ->whereIn('tipe_akun', ['Beban', 'HPP', 'Expense', 'Cost'])
            ->where(function($q) use ($userId) {
                $q->where('user_id', $userId)->orWhereNull('user_id');
            })
            ->orderByRaw('CASE WHEN user_id = ? THEN 0 ELSE 1 END', [$userId ?? 0])
            ->first();

        if ($umum) return (string)$umum->kode_akun;

        return null; // COA tidak ditemukan
    }

    /**
     * Cari COA Persediaan Barang Jadi untuk produk dari COA yang sudah ada.
     * Prioritas: coa_persediaan_id produk → COA spesifik nama → COA umum 116.
     * Return null jika tidak ditemukan.
     */
    private function findCoaPersediaan($produk, $userId): ?string
    {
        // 1. Gunakan coa_persediaan_id dari produk jika ada (bigint FK = id COA)
        if (!empty($produk->coa_persediaan_id)) {
            $existing = Coa::withoutGlobalScopes()->find($produk->coa_persediaan_id);
            if ($existing) return (string)$existing->kode_akun;
        }

        $namaProduk = $produk->nama_produk;

        // 2. Cari COA spesifik: "Pers. Barang Jadi {nama_produk}" atau "Persediaan Barang Jadi {nama_produk}"
        $spesifik = Coa::withoutGlobalScopes()
            ->where(function($q) use ($namaProduk) {
                $q->where('nama_akun', 'Pers. Barang Jadi ' . $namaProduk)
                  ->orWhere('nama_akun', 'Persediaan Barang Jadi ' . $namaProduk)
                  ->orWhere('nama_akun', 'like', '%Barang Jadi%' . $namaProduk . '%');
            })
            ->whereIn('tipe_akun', ['Asset', 'Aset'])
            ->where(function($q) use ($userId) {
                $q->where('user_id', $userId)->orWhereNull('user_id');
            })
            ->orderByRaw('CASE WHEN user_id = ? THEN 0 ELSE 1 END', [$userId ?? 0])
            ->first();

        if ($spesifik) {
            // Simpan id COA ke produk agar tidak dicari ulang
            \DB::table('produks')->where('id', $produk->id)
                ->update(['coa_persediaan_id' => $spesifik->id]);
            return (string)$spesifik->kode_akun;
        }

        // 3. Cari COA Persediaan Barang Jadi umum: kode 116
        $umum = Coa::withoutGlobalScopes()
            ->where(function($q) {
                $q->where('kode_akun', '116')
                  ->orWhere('nama_akun', 'Pers. Barang Jadi')
                  ->orWhere('nama_akun', 'Persediaan Barang Jadi');
            })
            ->whereIn('tipe_akun', ['Asset', 'Aset'])
            ->where(function($q) use ($userId) {
                $q->where('user_id', $userId)->orWhereNull('user_id');
            })
            ->orderByRaw('CASE WHEN user_id = ? THEN 0 ELSE 1 END', [$userId ?? 0])
            ->first();

        if ($umum) return (string)$umum->kode_akun;

        return null; // COA tidak ditemukan
    }

    /**
     * Cari atau buat COA HPP untuk produk.
     * Nama: "HPP {nama_produk}", tipe: Beban, kode: 51xx
     * @deprecated Gunakan findCoaHpp() — tidak lagi membuat COA baru otomatis
     */
    private function getOrCreateCoaHpp($produk, $userId): string
    {
        return $this->findCoaHpp($produk, $userId) ?? '56';
    }

    /**
     * Cari atau buat COA Persediaan Barang Jadi untuk produk.
     * @deprecated Gunakan findCoaPersediaan() — tidak lagi membuat COA baru otomatis
     */
    private function getOrCreateCoaPersediaan($produk, $userId): string
    {
        return $this->findCoaPersediaan($produk, $userId) ?? '116';
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
