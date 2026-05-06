<?php

namespace App\Services;

use App\Models\JurnalUmum;
use App\Models\Coa;
use Illuminate\Support\Facades\DB;

class JournalService
{
<<<<<<< HEAD
    protected function coaId(string $code, ?int $userId = null): int
    {
        // Bypass global scope, filter by user_id if provided
        $query = Coa::withoutGlobalScopes()->where('kode_akun', $code);
        
        if ($userId) {
            $coa = (clone $query)->where('user_id', $userId)->first();
            if ($coa) return (int)$coa->getAttribute('id');
=======
    protected function coaId(string $code, $userId = null): int
    {
        $userId = $userId ?? auth()->id();
        
        $coa = Coa::where('kode_akun', $code)
            ->where('user_id', $userId)
            ->first();
            
        if ($coa) {
            return (int)$coa->getAttribute('id');
>>>>>>> cb46e8bf88bbf58f140ce82a4feead3f3abd254b
        }
        
        // Fallback: ambil berdasarkan auth user
        if (auth()->check()) {
            $coa = (clone $query)->where('user_id', auth()->id())->first();
            if ($coa) return (int)$coa->getAttribute('id');
        }
        
        // Last fallback: ambil yang pertama
        $coa = $query->first();
        if ($coa) return (int)$coa->getAttribute('id');

<<<<<<< HEAD
        throw new \RuntimeException("COA dengan kode {$code} tidak ditemukan. Silakan buat COA terlebih dahulu di master data.");
=======
        throw new \RuntimeException(
            "COA dengan kode '{$code}' tidak ditemukan untuk user ID {$userId}. " .
            "Silakan buat COA terlebih dahulu di Master Data > Chart of Accounts."
        );
>>>>>>> cb46e8bf88bbf58f140ce82a4feead3f3abd254b
    }

    /**
     * Post a balanced journal entry with given lines. Each line element: ['code'=>account_code, 'debit'=>float, 'credit'=>float, 'memo'=>string (optional)]
     */
<<<<<<< HEAD
    public function post(string $tanggal, string $refType, int $refId, string $memo, array $lines, ?int $userId = null): JournalEntry
    {
        return DB::transaction(function () use ($tanggal, $refType, $refId, $memo, $lines, $userId) {
            $entry = JournalEntry::create([
                'tanggal' => $tanggal,
                'ref_type' => $refType,
                'ref_id' => $refId,
                'memo' => $memo,
            ]);
=======
    public function post(string $tanggal, string $refType, int $refId, string $memo, array $lines)
    {
        return $this->postWithUser($tanggal, $refType, $refId, $memo, $lines, auth()->id());
    }
>>>>>>> cb46e8bf88bbf58f140ce82a4feead3f3abd254b

    /**
     * Post a balanced journal entry with specific user_id
     */
    public function postWithUser(string $tanggal, string $refType, int $refId, string $memo, array $lines, $userId)
    {
        return DB::transaction(function () use ($tanggal, $refType, $refId, $memo, $lines, $userId) {
            $totalDebit = 0.0; 
            $totalCredit = 0.0;
            
            foreach ($lines as $ln) {
<<<<<<< HEAD
                // Handle both 'code' and 'coa_id' fields
                if (isset($ln['coa_id'])) {
                    $aid = (int)$ln['coa_id'];
                } else {
                    $aid = $this->coaId($ln['code'], $userId);
                }
                
                $debit = (float)($ln['debit'] ?? 0); $credit = (float)($ln['credit'] ?? 0);
                $lineMemo = $ln['memo'] ?? null;
=======
                $aid = $this->coaId($ln['code'], $userId); // Pass userId for multi-tenant
                $debit = (float)($ln['debit'] ?? 0); 
                $credit = (float)($ln['credit'] ?? 0);
                $lineMemo = $ln['memo'] ?? $memo;
>>>>>>> cb46e8bf88bbf58f140ce82a4feead3f3abd254b
                
                // Create journal entry using JurnalUmum
                JurnalUmum::create([
                    'user_id' => $userId,
                    'coa_id' => $aid,
                    'tanggal' => $tanggal,
                    'keterangan' => $lineMemo,
                    'debit' => $debit,
                    'kredit' => $credit,
                    'referensi' => $refId,
                    'tipe_referensi' => $refType,
                    'created_by' => $userId,
                ]);
                
                $totalDebit += $debit;
                $totalCredit += $credit;
            }

            // Validate balance
            if (abs($totalDebit - $totalCredit) > 0.01) {
                throw new \RuntimeException("Journal entry must balance. Total debit: $totalDebit, Total credit: $totalCredit");
            }

            return (object)['id' => $refId];
        });
    }

    /**
     * Delete journal entries by reference
     */
    public function deleteByRef(string $refType, int $refId)
    {
        return JurnalUmum::where('tipe_referensi', $refType)->where('referensi', $refId)->delete();
    }

    /**
     * Get journal entries by reference
     */
    public function getJournalEntries(string $refType, int $refId)
    {
        return JurnalUmum::where('tipe_referensi', $refType)->where('referensi', $refId)->with('coa')->get();
    }

    /**
     * Get journal entries by date range for user
     */
    public function getJournalEntriesByDateRange($userId, $startDate, $endDate)
    {
        return JurnalUmum::where('user_id', $userId)
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->with('coa')
            ->orderBy('tanggal')
            ->orderBy('created_at')
            ->get();
    }

    /**
<<<<<<< HEAD
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
                        $creditAccount = '112'; // Use correct Kas code
                        $creditMemo = 'Pembayaran tunai pembelian';
                    }
                } else {
                    $creditAccount = '112'; // Use correct Kas code
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
     * Create journal entries from Penjualan (Sales).
     *
     * Jurnal Penjualan:
     *   Dr. Kas / Bank / Piutang          (grand_total)
     *   Cr. Penjualan                     (subtotal produk GROSS / sebelum diskon)
     *   Cr. PPN Keluaran                  (biaya_ppn, jika ada)
     *   Cr. Pendapatan Lain-lain          (biaya_ongkir, jika ada)
     *   Dr. Diskon Penjualan              (total diskon, jika ada)
     *
     * Jurnal HPP (per produk):
     *   Dr. HPP {nama produk}
     *   Cr. Persediaan Barang Jadi {nama produk}
     *
     * VALIDASI: Jika ada akun yang belum tersedia, jurnal TIDAK dibuat dan
     * exception dilempar dengan pesan yang informatif.
     *
     * @throws \RuntimeException jika ada akun yang belum tersedia
=======
     * Create journal entries from Penjualan with HPP
     * Dr. Kas/Bank/Piutang | Cr. Pendapatan Penjualan
     * Dr. HPP | Cr. Persediaan Barang Jadi
>>>>>>> cb46e8bf88bbf58f140ce82a4feead3f3abd254b
     */
    public static function createJournalFromPenjualan($penjualan, $userId = null): void
    {
        $service = new static();

        // Pastikan relasi sudah di-load
        if (!$penjualan->relationLoaded('details')) {
            $penjualan->load('details.produk');
        }
        if (!$penjualan->relationLoaded('produk')) {
            $penjualan->load('produk');
        }

        // ── VALIDASI AKUN ────────────────────────────────────────────────────
        $validator = new \App\Services\JournalValidationService();
        $validation = $validator->validate($penjualan);

        if (!$validation['valid']) {
            $namaAkunMissing = array_map(fn($m) => $m['nama'], $validation['missing']);
            $pesanList = array_map(fn($m) => '• ' . $m['pesan'], $validation['missing']);

            if (count($namaAkunMissing) === 1) {
                $pesan = "Jurnal penjualan tidak dapat dibuat.\n" . $pesanList[0];
            } else {
                $pesan = "Jurnal penjualan tidak dapat dibuat. Akun berikut belum tersedia:\n"
                       . implode("\n", $pesanList);
        
        // Refresh and load relationships to ensure we have latest data
        $penjualan = $penjualan->fresh(['details.produk', 'produk']);
        
        if (!$penjualan) {
            \Log::error('Penjualan not found when creating journal');
            return;
        }
        
        // Delete existing journal entries for this penjualan
        $service->deleteByRef('sale', $penjualan->id);
        
        $lines = [];
        $totalAmount = $penjualan->grand_total ?? $penjualan->total ?? 0;
        
        // Create debit entry based on payment method (Kas/Bank/Piutang)
        $debitAccount = null;
        $debitMemo = '';
        
<<<<<<< HEAD
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

            \Log::warning('Journal penjualan #' . ($penjualan->nomor_penjualan ?? $penjualan->id)
                . ' tidak dibuat – akun missing: ' . implode(', ', $namaAkunMissing));

            throw new \RuntimeException($pesan);
=======
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
                              ->orWhere('nama_akun', 'like', '%piutang%usaha%');
                    })
                    ->orWhere('kode_akun', '113') // Piutang Usaha
                    ->orWhere('kode_akun', '103')
                    ->first();
                
                $debitAccount = $piutangCoa ? $piutangCoa->kode_akun : '113';
                $debitMemo = 'Penerimaan kredit penjualan';
                break;
                
            default:
                // Default to Kas
                $debitAccount = '112';
                $debitMemo = 'Penerimaan penjualan';
>>>>>>> cb46e8bf88bbf58f140ce82a4feead3f3abd254b
        }

        // ── HAPUS JURNAL LAMA ────────────────────────────────────────────────
        $service->deleteByRef('sale', $penjualan->id);

        // Pastikan userId tidak null — fallback ke auth user
        $userId = $penjualan->user_id ?? auth()->id() ?? null;
        $lines  = [];

        // ── Hitung nilai-nilai dasar ─────────────────────────────────────────
        // subtotalGross = harga × qty (sebelum diskon), untuk kredit akun Penjualan
        // subtotalNet   = subtotal setelah diskon, untuk cek balance
        $subtotalGross = 0;
        $subtotalNet   = 0;
        $totalDiskon   = (float)($penjualan->diskon_nominal ?? 0);

        if ($penjualan->details && $penjualan->details->count() > 0) {
            foreach ($penjualan->details as $d) {
                $diskonBaris = (float)($d->diskon_nominal ?? 0);
                // Fallback: hitung dari diskon_persen jika nominal belum tersimpan
                if ($diskonBaris == 0 && ($d->diskon_persen ?? 0) > 0) {
                    $diskonBaris = round((float)$d->harga_satuan * (float)$d->jumlah * (float)$d->diskon_persen / 100);
                }
                $subtotalBaris  = (float)($d->subtotal ?? ((float)$d->harga_satuan * (float)$d->jumlah - $diskonBaris));
                $grossBaris     = $subtotalBaris + $diskonBaris;

                $subtotalNet   += $subtotalBaris;
                $subtotalGross += $grossBaris;
                $totalDiskon   += $diskonBaris;
                return $q->orderBy('id', 'desc')->first();
            };

            switch ($penjualan->payment_method) {
                case 'cash':
                    $coa = $findDebitCoa('Kas', 'Asset');
                    $debitAccount = $coa ? $coa->kode_akun : '112';
                    $debitMemo    = 'Penerimaan tunai penjualan';
                    break;
                case 'transfer':
                    $coa = Coa::withoutGlobalScopes()
                               ->where('tipe_akun', 'Asset')
                               ->where('nama_akun', 'like', '%bank%')
                               ->when($userId, fn($q) => $q->where('user_id', $userId))
                               ->first();
                    $debitAccount = $coa ? $coa->kode_akun : '111';
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
                    $debitAccount = $coa ? $coa->kode_akun : '112';
                    $debitMemo    = 'Penerimaan penjualan';
            }
        } else {
            // Transaksi header-only (tanpa detail)
            $subtotalNet   = (float)($penjualan->total ?? 0)
                           - (float)($penjualan->biaya_ongkir ?? 0)
                           - (float)($penjualan->biaya_ppn ?? 0);
            $subtotalGross = $subtotalNet + $totalDiskon;
        }

        $biayaOngkir = (float)($penjualan->biaya_ongkir ?? 0);
        $biayaPPN    = (float)($penjualan->biaya_ppn    ?? 0);

        // ── Hitung grand_total dari komponen (jangan ambil dari DB karena bisa tidak konsisten) ──
        // grand_total = subtotalNet + biayaPPN + biayaOngkir
        // (diskon sudah tercermin di subtotalNet)
        $grandTotal = round($subtotalNet + $biayaPPN + $biayaOngkir);

        // ── DEBIT: Kas / Bank / Piutang ──────────────────────────────────────
        $debitCoa  = $validation['accounts']['debit'];
        $debitMemo = match ($penjualan->payment_method ?? 'cash') {
            'transfer' => 'Penerimaan transfer penjualan - ' . $debitCoa->nama_akun,
            'credit'   => 'Penjualan kredit - ' . $debitCoa->nama_akun,
            default    => 'Penerimaan tunai penjualan - ' . $debitCoa->nama_akun,
        };

        $lines[] = [
            'code'   => $debitCoa->kode_akun,
            'debit'  => $grandTotal,
            'credit' => 0,
            'memo'   => $debitMemo,
        ];

        // ── DEBIT: Diskon Penjualan (jika ada) ───────────────────────────────
        if ($totalDiskon > 0 && isset($validation['accounts']['diskon_penjualan'])) {
            $diskonCoa = $validation['accounts']['diskon_penjualan'];
            $lines[] = [
                'code'   => $diskonCoa->kode_akun,
                'debit'  => round($totalDiskon),
                'credit' => 0,
                'memo'   => 'Diskon penjualan',
            ];
        }

        // ── KREDIT: Penjualan ────────────────────────────────────────────────
        $penjualanCoa = $validation['accounts']['penjualan'];
        // Jika ada akun Diskon Penjualan → kredit nilai GROSS (sebelum diskon),
        // karena diskon sudah dicatat terpisah di sisi Debit.
        // Jika tidak ada akun Diskon → kredit nilai NET (setelah diskon).
        $nilaiPenjualan = ($totalDiskon > 0 && isset($validation['accounts']['diskon_penjualan']))
            ? round($subtotalGross)
            : round($subtotalNet);
        
        // ── Credit lines: Penjualan, PPN Keluaran, Ongkir ────────────────────
        $subtotalProduk = 0;
        foreach ($penjualan->details as $d) {
            $subtotalProduk += (float)($d->subtotal ?? ((float)$d->harga_satuan * (float)$d->jumlah));
        }
        if ($subtotalProduk <= 0) {
            $subtotalProduk = (float)($penjualan->subtotal_produk ?? $penjualan->total ?? 0)
                            - (float)($penjualan->biaya_ongkir ?? 0);
            // Don't subtract PPN - total is already before PPN, grand_total includes PPN
        }

        $biayaOngkir = (float)($penjualan->biaya_ongkir ?? 0);
        $biayaPPN    = (float)($penjualan->total_ppn ?? $penjualan->biaya_ppn ?? 0);
        $diskonNominal = (float)($penjualan->diskon_nominal ?? 0);
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

        $lines[] = [
            'code'   => $penjualanCoa->kode_akun,
            'debit'  => 0,
            'credit' => $nilaiPenjualan,
            'memo'   => 'Pendapatan penjualan produk',
        ];

        // ── KREDIT: PPN Keluaran ─────────────────────────────────────────────
        if ($biayaPPN > 0 && isset($validation['accounts']['ppn_keluaran'])) {
            $ppnCoa = $validation['accounts']['ppn_keluaran'];
            $lines[] = [
                'code'   => $ppnCoa->kode_akun,
                'debit'  => 0,
                'credit' => round($biayaPPN),
                'memo'   => 'PPN Keluaran 11%',
            ];
        }

        // ── KREDIT: Pendapatan Lain-lain (Ongkir) ───────────────────────────
        if ($biayaOngkir > 0 && isset($validation['accounts']['pendapatan_lain'])) {
            $ongkirCoa = $validation['accounts']['pendapatan_lain'];
            $lines[] = [
                'code'   => $ongkirCoa->kode_akun,
                'debit'  => 0,
                'credit' => round($biayaOngkir),
                'memo'   => 'Pendapatan ongkos kirim',
            ];
        }

        // ── HPP & PERSEDIAAN (per produk) ────────────────────────────────────
        
        // Dr. Diskon Penjualan (potongan pendapatan)
        if ($diskonNominal > 0) {
            $diskonCoa = $findCoa('Diskon Penjualan', 'Expense');
            if (!$diskonCoa) {
                $diskonCoa = Coa::withoutGlobalScopes()
                    ->where('nama_akun', 'like', '%Diskon%')
                    ->whereIn('tipe_akun', ['Expense', 'Beban'])
                    ->when($userId, fn($q) => $q->where('user_id', $userId))
                    ->orderBy('id', 'desc')
                    ->first();
            }
            if (!$diskonCoa) {
                // Buat COA Diskon Penjualan otomatis
                $diskonCoa = Coa::create([
                    'kode_akun' => '5112',
                    'nama_akun' => 'Diskon Penjualan',
                    'tipe_akun' => 'Expense',
                    'kategori_akun' => 'Diskon',
                    'saldo_normal' => 'Debit',
                    'user_id' => $userId ?? 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            $lines[] = [
                'code'   => $diskonCoa->kode_akun,
                'debit'  => $diskonNominal,
                'credit' => 0,
                'memo'   => 'Diskon penjualan',
            ];
        }
        
        // Add HPP journal entries with detailed breakdown
        $hppLines = $service->createHPPLinesFromPenjualan($penjualan);
<<<<<<< HEAD
        $lines    = array_merge($lines, $hppLines);

        // ── VERIFIKASI BALANCE sebelum post ──────────────────────────────────
        // Jurnal penjualan harus balance terpisah dari HPP.
        // HPP lines selalu berpasangan (Dr.HPP = Cr.Persediaan) sehingga tidak
        // mempengaruhi balance jurnal penjualan.
        // Jika ada selisih akibat rounding, koreksi ke akun Penjualan.
        $totalDebitPenjualan  = $grandTotal + ($totalDiskon > 0 && isset($validation['accounts']['diskon_penjualan']) ? round($totalDiskon) : 0);
        $totalKreditPenjualan = $nilaiPenjualan + round($biayaPPN) + round($biayaOngkir);

        if ($totalDebitPenjualan !== $totalKreditPenjualan) {
            // Koreksi selisih ke nilai Penjualan (cari index baris Penjualan)
            foreach ($lines as &$line) {
                if (isset($line['memo']) && $line['memo'] === 'Pendapatan penjualan produk') {
                    $line['credit'] = $totalDebitPenjualan - round($biayaPPN) - round($biayaOngkir);
                    break;
                }
            }
            unset($line);
        }

        // ── POST JURNAL ──────────────────────────────────────────────────────
        $memo    = 'Penjualan #' . ($penjualan->nomor_penjualan ?? $penjualan->id);
        $tanggal = $penjualan->tanggal instanceof \Carbon\Carbon
            ? $penjualan->tanggal->format('Y-m-d')
            : $penjualan->tanggal;

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
=======
        $lines = array_merge($lines, $hppLines);
        
        // Create journal entry
        $memo = 'Penjualan #' . ($penjualan->nomor_penjualan ?? $penjualan->id);
        $tanggal = $penjualan->tanggal instanceof \Carbon\Carbon ? 
                   $penjualan->tanggal->format('Y-m-d') : 
                   $penjualan->tanggal;
        
        // Use provided userId or fallback to penjualan's user_id
        $finalUserId = $userId ?? $penjualan->user_id ?? auth()->id();
        
        $service->postWithUser($tanggal, 'sale', $penjualan->id, $memo, $lines, $finalUserId);
    }

    /**
     * Create HPP journal lines from Penjualan with simplified calculation
     * Dr. HPP | Cr. Persediaan Barang Jadi
     */
    private function createHPPLinesFromPenjualan($penjualan): array
    {
        $lines = [];
        
        // Log for debugging
        \Log::info('Creating HPP lines for penjualan', [
            'penjualan_id' => $penjualan->id,
            'has_details' => $penjualan->details ? $penjualan->details->count() : 0,
            'has_produk' => $penjualan->produk ? true : false
        ]);
        
        // PRIORITY 1: Use details if available (modern multi-item penjualan)
>>>>>>> cb46e8bf88bbf58f140ce82a4feead3f3abd254b
        if ($penjualan->details && $penjualan->details->count() > 0) {
            foreach ($penjualan->details as $detail) {
<<<<<<< HEAD
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

            $namaProduk = $produk->nama_produk;

            // Cari atau buat COA HPP yang sesuai
            $coaHppKode = $this->getOrCreateCoaHpp($produk, $userId);
            // Cari atau buat COA Persediaan Barang Jadi yang sesuai
            $coaPersediaanKode = $this->getOrCreateCoaPersediaan($produk, $userId);

            // Debug log
            \Log::info("HPP Processing for {$namaProduk}: HPP={$totalHPP}, COA HPP={$coaHppKode}, COA Persediaan={$coaPersediaanKode}");

            // Jika salah satu COA tidak ditemukan, skip dan log warning
            if (!$coaHppKode || !$coaPersediaanKode) {
                $missing = [];
                if (!$coaHppKode) $missing[] = "COA HPP untuk '{$namaProduk}'";
                if (!$coaPersediaanKode) $missing[] = "COA Persediaan Barang Jadi untuk '{$namaProduk}'";
                \Log::warning('HPP Journal skipped - COA tidak ditemukan: ' . implode(', ', $missing));
                continue;
            }
            
            
            // Dr. HPP (nama produk)
=======
                $detailLines = $this->createHPPLinesForDetail($detail, $penjualan);
                \Log::info('HPP lines for detail', [
                    'detail_id' => $detail->id,
                    'lines_count' => count($detailLines)
                ]);
                $lines = array_merge($lines, $detailLines);
            }
        } 
        // PRIORITY 2: Use single produk_id (legacy single-item penjualan)
        elseif ($penjualan->produk) {
            $singleLines = $this->createHPPLinesForSingleItem($penjualan);
            \Log::info('HPP lines for single item', [
                'lines_count' => count($singleLines)
            ]);
            $lines = $singleLines;
        }
        else {
            \Log::warning('No details or produk found for penjualan', [
                'penjualan_id' => $penjualan->id
            ]);
        }
        
        \Log::info('Total HPP lines created', ['count' => count($lines)]);
        
        return $lines;
    }
    
    /**
     * Create HPP lines for penjualan detail (simplified version)
     */
    private function createHPPLinesForDetail($detail, $penjualan): array
    {
        $lines = [];
        $qty = $detail->jumlah ?? 0;
        $product = $detail->produk;
        
        if (!$product || $qty <= 0) {
            return $lines;
        }
        
        // Get HPP using product's built-in method
        $hppPerUnit = $product->getActualHPP($penjualan->tanggal);
        $totalHPP = $hppPerUnit * $qty;
        
        if ($totalHPP > 0) {
            // Debit HPP account
            $lines[] = [
                'code' => '554', // HARGA POKOK PENJUALAN (HPP) - Updated from 560 to 554
                'debit' => $totalHPP,
                'credit' => 0,
                'memo' => "HPP untuk {$product->nama_produk} ({$qty} pcs @ Rp " . number_format($hppPerUnit, 2) . ")"
            ];
            
            // Credit persediaan barang jadi
            $persediaanCOA = $this->getPersediaanBarangJadiCOA($product);
>>>>>>> cb46e8bf88bbf58f140ce82a4feead3f3abd254b
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
<<<<<<< HEAD
                'memo'   => "Persediaan Barang Jadi {$namaProduk} ({$qty} pcs)",
=======
                'memo' => "Keluar persediaan - {$product->nama_produk} ({$qty} pcs)"
>>>>>>> cb46e8bf88bbf58f140ce82a4feead3f3abd254b
            ];
        }

        return $lines;
    }

    /**
<<<<<<< HEAD
     * Cari atau buat COA HPP untuk produk.
     * Prioritas: COA spesifik produk → COA HPP umum → Buat baru.
     * Return string kode_akun.
=======
     * Create HPP lines for single item penjualan (simplified version)
>>>>>>> cb46e8bf88bbf58f140ce82a4feead3f3abd254b
     */
    private function findOrCreateCoaHpp($produk, $userId): ?string
    {
<<<<<<< HEAD
        $namaProduk = $produk->nama_produk;

        // 1. Cari COA spesifik per produk (milik user atau global)
        $spesifik = Coa::withoutGlobalScopes()
            ->where(function($q) use ($namaProduk) {
                $q->where('nama_akun', 'HPP ' . $namaProduk)
                  ->orWhere('nama_akun', 'Harga Pokok Penjualan ' . $namaProduk)
                  ->orWhere('nama_akun', 'like', '%HPP%' . $namaProduk . '%');
            })
            ->whereIn('tipe_akun', ['Beban', 'HPP', 'Expense', 'Cost'])
            ->when($userId, function($q) use ($userId) {
                // Prioritaskan milik user, tapi juga ambil yang null
                $q->where(function($q2) use ($userId) {
                    $q2->where('user_id', $userId)->orWhereNull('user_id');
                });
            })
            ->orderByRaw($userId ? 'CASE WHEN user_id = ? THEN 0 ELSE 1 END' : '1', $userId ? [$userId] : [])
            ->first();

        if ($spesifik) return (string)$spesifik->kode_akun;

        // 2. Cari COA HPP umum: nama "Harga Pokok Penjualan" atau "HPP"
        $umum = Coa::withoutGlobalScopes()
            ->where(function($q) {
                $q->where('nama_akun', 'Harga Pokok Penjualan')
                  ->orWhere('nama_akun', 'HPP')
                  ->orWhere('kode_akun', '56');
            })
            ->whereIn('tipe_akun', ['Beban', 'HPP', 'Expense', 'Cost'])
            ->when($userId, function($q) use ($userId) {
                $q->where(function($q2) use ($userId) {
                    $q2->where('user_id', $userId)->orWhereNull('user_id');
                });
            })
            ->orderByRaw($userId ? 'CASE WHEN user_id = ? THEN 0 ELSE 1 END' : '1', $userId ? [$userId] : [])
            ->first();

        if ($umum) return (string)$umum->kode_akun;

        // 3. Last resort: ambil akun Expense/Beban apapun milik user
        if ($userId) {
            $any = Coa::withoutGlobalScopes()
                ->whereIn('tipe_akun', ['Beban', 'HPP', 'Expense', 'Cost'])
                ->where('user_id', $userId)
                ->orderBy('kode_akun')
                ->first();

         if ($any) {
            return (string) $any->kode_akun;
           }
=======
        $lines = [];
        $qty = $penjualan->jumlah ?? 0;
        $product = $penjualan->produk;
        
        \Log::info('createHPPLinesForSingleItem debug', [
            'qty' => $qty,
            'has_product' => $product ? true : false,
            'product_id' => $product ? $product->id : null
        ]);
        
        if (!$product || $qty <= 0) {
            \Log::warning('Skipping HPP for single item', [
                'reason' => !$product ? 'no product' : 'qty <= 0',
                'qty' => $qty
            ]);
            return $lines;
        }
        
        // Get HPP using product's built-in method
        $hppPerUnit = $product->getActualHPP($penjualan->tanggal);
        $totalHPP = $hppPerUnit * $qty;
        
        \Log::info('HPP calculation for single item', [
            'hpp_per_unit' => $hppPerUnit,
            'qty' => $qty,
            'total_hpp' => $totalHPP
        ]);
        
        if ($totalHPP > 0) {
            // Debit HPP account
            $lines[] = [
                'code' => '554', // HARGA POKOK PENJUALAN (HPP) - Updated from 560 to 554
                'debit' => $totalHPP,
                'credit' => 0,
                'memo' => "HPP untuk {$product->nama_produk} ({$qty} pcs @ Rp " . number_format($hppPerUnit, 2) . ")"
            ];
            
            // Credit persediaan barang jadi
            $persediaanCOA = $this->getPersediaanBarangJadiCOA($product);
            $lines[] = [
                'code' => $persediaanCOA,
                'debit' => 0,
                'credit' => $totalHPP,
                'memo' => "Keluar persediaan - {$product->nama_produk} ({$qty} pcs)"
            ];
            
            \Log::info('HPP lines created for single item', ['lines_count' => count($lines)]);
        } else {
            \Log::warning('HPP is zero, no lines created');
        }
        
        return $lines;
>>>>>>> cb46e8bf88bbf58f140ce82a4feead3f3abd254b
    }

// 3. Buat COA HPP baru otomatis jika tidak ditemukan
return $this->createCoaHpp($produk, $userId);
    }

    /**
     * Cari COA Persediaan Barang Jadi untuk produk dari COA yang sudah ada.
     * Prioritas: coa_persediaan_id produk → COA spesifik nama → COA umum.
     * Return null jika tidak ditemukan.
     */
    private function findCoaPersediaan($produk, $userId): ?string
    {
        // 1. Gunakan coa_persediaan_id dari produk jika ada (langsung pakai ID COA)
        if (!empty($produk->coa_persediaan_id)) {
            $existing = Coa::withoutGlobalScopes()->find($produk->coa_persediaan_id);
            if ($existing) return (string)$existing->kode_akun;
        }

        $namaProduk = $produk->nama_produk;

        // 2. Cari COA spesifik per produk
        $spesifik = Coa::withoutGlobalScopes()
            ->where(function($q) use ($namaProduk) {
                $q->where('nama_akun', 'Pers. Barang Jadi ' . $namaProduk)
                  ->orWhere('nama_akun', 'Persediaan Barang Jadi ' . $namaProduk)
                  ->orWhere('nama_akun', 'like', '%Barang Jadi%' . $namaProduk . '%');
            })
            ->whereIn('tipe_akun', ['Asset', 'Aset'])
            ->when($userId, function($q) use ($userId) {
                $q->where(function($q2) use ($userId) {
                    $q2->where('user_id', $userId)->orWhereNull('user_id');
                });
            })
            ->orderByRaw($userId ? 'CASE WHEN user_id = ? THEN 0 ELSE 1 END' : '1', $userId ? [$userId] : [])
            ->first();

        if ($spesifik) {
            \DB::table('produks')->where('id', $produk->id)
                ->update(['coa_persediaan_id' => $spesifik->id]);
            return (string)$spesifik->kode_akun;
        }

        // 3. Cari COA Persediaan Barang Jadi umum
        $umum = Coa::withoutGlobalScopes()
            ->where(function($q) {
                $q->where('nama_akun', 'Pers. Barang Jadi')
                  ->orWhere('nama_akun', 'Persediaan Barang Jadi')
                  ->orWhere('kode_akun', '116');
            })
            ->whereIn('tipe_akun', ['Asset', 'Aset'])
            ->when($userId, function($q) use ($userId) {
                $q->where(function($q2) use ($userId) {
                    $q2->where('user_id', $userId)->orWhereNull('user_id');
                });
            })
            ->orderByRaw($userId ? 'CASE WHEN user_id = ? THEN 0 ELSE 1 END' : '1', $userId ? [$userId] : [])
            ->first();

        if ($umum) return (string)$umum->kode_akun;

        return null;
    }

    /**
     * Cari atau buat COA HPP untuk produk.
     * Nama: "HPP {nama_produk}", tipe: Beban, kode: 51xx
     */
    private function getOrCreateCoaHpp($produk, $userId): string
    {
        // Cari COA yang sudah ada
        $existingCoa = $this->findCoaHpp($produk, $userId);
        if ($existingCoa) {
            return $existingCoa;
        }
        
<<<<<<< HEAD
        // Buat COA baru otomatis
        $namaProduk = $produk->nama_produk;
        
        // Generate kode akun unik
        $lastCoa = Coa::withoutGlobalScopes()
            ->where('kode_akun', 'like', '56%')
            ->orderBy('kode_akun', 'desc')
            ->first();
        
        $nextKode = '561'; // Default
        if ($lastCoa) {
            $lastNum = (int)preg_replace('/[^0-9]/', '', $lastCoa->kode_akun);
            $nextKode = '56' . ($lastNum + 1);
        }
        
        // Buat COA baru
        $newCoa = Coa::create([
            'kode_akun' => $nextKode,
            'nama_akun' => 'HPP ' . $namaProduk,
            'tipe_akun' => 'Expense',
            'kategori_akun' => 'HPP',
            'saldo_normal' => 'Debit',
            'user_id' => $userId ?? 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        \Log::info("Created new COA HPP: {$nextKode} - HPP {$namaProduk}");
        return $nextKode;
    }

    /**
     * Cari atau buat COA Persediaan Barang Jadi untuk produk.
     */
    private function getOrCreateCoaPersediaan($produk, $userId): string
    {
        // Cari COA yang sudah ada
        $existingCoa = $this->findCoaPersediaan($produk, $userId);
        if ($existingCoa) {
            return $existingCoa;
        }
        
        // Buat COA baru otomatis
        $namaProduk = $produk->nama_produk;
        
        // Generate kode akun unik
        $lastCoa = Coa::withoutGlobalScopes()
            ->where('kode_akun', 'like', '116%')
            ->orderBy('kode_akun', 'desc')
            ->first();
        
        $nextKode = '1161'; // Default
        if ($lastCoa) {
            $lastNum = (int)preg_replace('/[^0-9]/', '', $lastCoa->kode_akun);
            $nextKode = '116' . ($lastNum + 1);
        }
        
        // Buat COA baru
        $newCoa = Coa::create([
            'kode_akun' => $nextKode,
            'nama_akun' => 'Pers. Barang Jadi ' . $namaProduk,
            'tipe_akun' => 'Asset',
            'kategori_akun' => 'Persediaan',
            'saldo_normal' => 'Debit',
            'user_id' => $userId ?? 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // Update produk dengan coa_persediaan_id
        \DB::table('produks')->where('id', $produk->id)
            ->update(['coa_persediaan_id' => $newCoa->id]);
        
        \Log::info("Created new COA Persediaan: {$nextKode} - Pers. Barang Jadi {$namaProduk}");
        return $nextKode;
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
=======
        // Default to standard persediaan barang jadi account
        // Cari COA Persediaan Barang Jadi yang ada di database
        $persediaanCoa = Coa::where('tipe_akun', 'Asset')
>>>>>>> cb46e8bf88bbf58f140ce82a4feead3f3abd254b
            ->where(function($query) {
                $query->where('nama_akun', 'like', '%persediaan%barang%jadi%')
                      ->orWhere('nama_akun', 'like', '%persediaan%produk%jadi%');
            })
            ->orWhere('kode_akun', '116') // Persediaan Barang Jadi
            ->orWhere('kode_akun', '1160')
            ->first();
        
        return $persediaanCoa ? $persediaanCoa->kode_akun : '116';
    }
}