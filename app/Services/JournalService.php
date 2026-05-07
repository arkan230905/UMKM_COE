<?php

namespace App\Services;

use App\Models\JurnalUmum;
use App\Models\Coa;
use Illuminate\Support\Facades\DB;

class JournalService
{

    protected function coaId(string $code, $userId = null): int
    {
        $userId = $userId ?? auth()->id();
        
        $coa = Coa::where('kode_akun', $code)
            ->where('user_id', $userId)
            ->first();
            
        if ($coa) {
            return (int)$coa->getAttribute('id');
}
        
        // Fallback: ambil berdasarkan auth user
        if (auth()->check()) {
            $coa = (clone $query)->where('user_id', auth()->id())->first();
            if ($coa) return (int)$coa->getAttribute('id');
        }
        
        // Last fallback: ambil yang pertama
        $coa = $query->first();
        if ($coa) return (int)$coa->getAttribute('id');


        throw new \RuntimeException(
            "COA dengan kode '{$code}' tidak ditemukan untuk user ID {$userId}. " .
            "Silakan buat COA terlebih dahulu di Master Data > Chart of Accounts."
        );
}

    /**
     * Post a balanced journal entry with given lines. Each line element: ['code'=>account_code, 'debit'=>float, 'credit'=>float, 'memo'=>string (optional)]
     */

    public function post(string $tanggal, string $refType, int $refId, string $memo, array $lines)
    {
        return $this->postWithUser($tanggal, $refType, $refId, $memo, $lines, auth()->id());
    }
/**
     * Post a balanced journal entry with specific user_id
     */
    public function postWithUser(string $tanggal, string $refType, int $refId, string $memo, array $lines, $userId)
    {
        return DB::transaction(function () use ($tanggal, $refType, $refId, $memo, $lines, $userId) {
            $totalDebit = 0.0; 
            $totalCredit = 0.0;
            
            foreach ($lines as $ln) {

                $aid = $this->coaId($ln['code'], $userId); // Pass userId for multi-tenant
                $debit = (float)($ln['debit'] ?? 0); 
                $credit = (float)($ln['credit'] ?? 0);
                $lineMemo = $ln['memo'] ?? $memo;
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

     * Create journal entries from Penjualan with HPP
     * Dr. Kas/Bank/Piutang | Cr. Pendapatan Penjualan
     * Dr. HPP | Cr. Persediaan Barang Jadi
*/
    public static function createJournalFromPenjualan($penjualan, $userId = null): void
    {
        $service = new static();

        \Log::info("Starting journal creation for penjualan", [
            'penjualan_id' => $penjualan->id,
            'nomor_penjualan' => $penjualan->nomor_penjualan ?? 'N/A',
            'user_id' => $penjualan->user_id,
            'grand_total' => $penjualan->grand_total ?? $penjualan->total ?? 0,
            'payment_method' => $penjualan->payment_method ?? 'N/A'
        ]);

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
            }
            
            \Log::error("Journal validation failed for penjualan", [
                'penjualan_id' => $penjualan->id,
                'missing_accounts' => $namaAkunMissing,
                'error' => $pesan
            ]);
            
            throw new \Exception($pesan);
        }
        
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

        // Dr. Diskon Penjualan (potongan pendapatan) — hanya jika tidak ada akun diskon di validation
        // (jika ada di validation, sudah dicatat di blok DEBIT di atas)
        if ($totalDiskon > 0 && !isset($validation['accounts']['diskon_penjualan'])) {
            $diskonCoa = Coa::withoutGlobalScopes()
                ->where('nama_akun', 'like', '%Diskon%')
                ->whereIn('tipe_akun', ['Expense', 'Beban'])
                ->when($userId, fn($q) => $q->where('user_id', $userId))
                ->orderBy('id', 'desc')
                ->first();
            if (!$diskonCoa) {
                $diskonCoa = Coa::create([
                    'kode_akun'     => '5112',
                    'nama_akun'     => 'Diskon Penjualan',
                    'tipe_akun'     => 'Expense',
                    'kategori_akun' => 'Diskon',
                    'saldo_normal'  => 'Debit',
                    'user_id'       => $userId ?? 1,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            }
            $lines[] = [
                'code'   => $diskonCoa->kode_akun,
                'debit'  => round($totalDiskon),
                'credit' => 0,
                'memo'   => 'Diskon penjualan',
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
        
        // Use provided userId or fallback to penjualan's user_id
        $finalUserId = $userId ?? $penjualan->user_id ?? auth()->id();
        
        $service->postWithUser($tanggal, 'sale', $penjualan->id, $memo, $lines, $finalUserId);
        
        \Log::info("Journal created successfully for penjualan", [
            'penjualan_id' => $penjualan->id,
            'nomor_penjualan' => $penjualan->nomor_penjualan ?? 'N/A',
            'user_id' => $finalUserId,
            'total_lines' => count($lines),
            'total_debit' => array_sum(array_column($lines, 'debit')),
            'total_credit' => array_sum(array_column($lines, 'credit'))
        ]);
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
if ($penjualan->details && $penjualan->details->count() > 0) {
            foreach ($penjualan->details as $detail) {

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
            $lines[] = [
                'code' => $persediaanCOA,
                'debit' => 0,
                'credit' => $totalHPP,
                'memo' => "Keluar persediaan - {$product->nama_produk} ({$qty} pcs)"
            ];
        }

        return $lines;
    }

    /**
     * Create HPP lines for single item penjualan (simplified version)
     */
    private function createHPPLinesForSingleItem($penjualan): array
    {
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
                ->update(['coa_persediaan_id' => $spesifik->kode_akun]);
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
        $existingCoa = $this->findOrCreateCoaHpp($produk, $userId);
        if ($existingCoa) {
            return $existingCoa;
        }
        

        // Default to standard persediaan barang jadi account
        // Cari COA Persediaan Barang Jadi yang ada di database
        $persediaanCoa = Coa::where('tipe_akun', 'Asset')
->where(function($query) {
                $query->where('nama_akun', 'like', '%persediaan%barang%jadi%')
                      ->orWhere('nama_akun', 'like', '%persediaan%produk%jadi%');
            })
            ->orWhere('kode_akun', '116') // Persediaan Barang Jadi
            ->orWhere('kode_akun', '1160')
            ->first();
        
        return $persediaanCoa ? $persediaanCoa->kode_akun : '116';
    }

    /**
     * Get COA Persediaan Barang Jadi untuk produk
     * Prioritas:
     * 1. COA spesifik per produk (contoh: 1161 untuk Jasuke)
     * 2. COA umum Persediaan Barang Jadi (116)
     * 
     * @param \App\Models\Produk $product
     * @return string Kode akun COA
     */
    private function getPersediaanBarangJadiCOA($product): string
    {
        $userId = auth()->id();
        
        // 1. Cek apakah produk sudah punya coa_persediaan_id
        if (!empty($product->coa_persediaan_id)) {
            $coa = Coa::find($product->coa_persediaan_id);
            if ($coa) {
                return $coa->kode_akun;
            }
        }
        
        // 2. Cari COA spesifik untuk produk ini berdasarkan nama
        // Format: "Pers. Barang Jadi {NamaProduk}" atau "Persediaan Barang Jadi {NamaProduk}"
        $namaProduk = $product->nama_produk;
        
        $coaSpesifik = Coa::where('user_id', $userId)
            ->whereIn('tipe_akun', ['Asset', 'Aset'])
            ->where(function($query) use ($namaProduk) {
                $query->where('nama_akun', 'Pers. Barang Jadi ' . $namaProduk)
                      ->orWhere('nama_akun', 'Persediaan Barang Jadi ' . $namaProduk)
                      ->orWhere('nama_akun', 'like', '%Pers%Barang%Jadi%' . $namaProduk . '%');
            })
            ->first();
        
        if ($coaSpesifik) {
            // Update produk dengan coa_persediaan_id untuk next time
            $product->update(['coa_persediaan_id' => $coaSpesifik->kode_akun]);
            return $coaSpesifik->kode_akun;
        }
        
        // 3. Fallback ke COA umum Persediaan Barang Jadi
        $coaUmum = Coa::where('user_id', $userId)
            ->whereIn('tipe_akun', ['Asset', 'Aset'])
            ->where(function($query) {
                $query->where('kode_akun', '116')
                      ->orWhere('kode_akun', '115')
                      ->orWhere('nama_akun', 'Pers. Barang Jadi')
                      ->orWhere('nama_akun', 'Persediaan Barang Jadi');
            })
            ->first();
        
        if ($coaUmum) {
            return $coaUmum->kode_akun;
        }
        
        // 4. Default fallback
        return '116';
    }
}
