<?php

namespace App\Services;

use App\Models\Pembelian;
use App\Models\JurnalUmum;
use App\Models\Coa;
use Illuminate\Support\Facades\Log;

class PembelianJournalService
{
    /**
     * Buat jurnal umum untuk transaksi pembelian
     * 
     * @param Pembelian $pembelian
     * @return Pembelian|null
     */
    public function createJournalFromPembelian(Pembelian $pembelian): ?Pembelian
    {
        try {
            Log::info("Starting journal creation for pembelian", [
                'pembelian_id' => $pembelian->id,
                'nomor_pembelian' => $pembelian->nomor_pembelian,
                'user_id' => $pembelian->user_id,
                'total' => $pembelian->total,
                'payment_method' => $pembelian->payment_method
            ]);
            
            // Hapus jurnal yang sudah ada untuk pembelian ini
            $this->deleteExistingJournalPrivate($pembelian->id);
            
            // Skip jika tidak ada detail
            if (!$pembelian->details || $pembelian->details->isEmpty()) {
                Log::warning("Pembelian {$pembelian->id} tidak memiliki detail, skip jurnal");
                return null;
            }
            
            Log::info("Pembelian has {$pembelian->details->count()} details");
            
            $lines = [];
            $subtotalAmount = 0;
            
            // 1. DEBIT: Persediaan spesifik untuk setiap bahan
            foreach ($pembelian->details as $detail) {
                $amount = ($detail->jumlah ?? 0) * ($detail->harga_satuan ?? 0);
                $subtotalAmount += $amount;
                
                $coaInfo = $this->getCoaForItem($detail);
                
                if ($coaInfo['coa_id']) {
                    $lines[] = [
                        'coa_id' => $coaInfo['coa_id'],
                        'debit' => $amount,
                        'credit' => 0,
                        'memo' => $coaInfo['memo']
                    ];
                    
                    Log::info("Jurnal Pembelian - Persediaan Spesifik", [
                        'item' => $detail->nama_bahan,
                        'coa' => $coaInfo['coa_code'],
                        'coa_name' => $coaInfo['coa_name'],
                        'amount' => $amount
                    ]);
                }
            }
            
            // 2. DEBIT: PPN Masukan (jika ada)
            $ppnNominal = (float) ($pembelian->ppn_nominal ?? 0);
            if ($ppnNominal > 0) {
                // Use the new PPN Masukan COA (127)
                $ppnCoa = $this->getCoaByCode('127'); // PPN Masukan
                
                $lines[] = [
                    'coa_id' => $ppnCoa->id,
                    'debit' => $ppnNominal,
                    'credit' => 0,
                    'memo' => 'PPN Masukan'
                ];
                
                Log::info("Jurnal Pembelian - PPN Masukan", [
                    'persen' => $pembelian->ppn_persen,
                    'nominal' => $ppnNominal,
                    'coa_used' => $ppnCoa->kode_akun
                ]);
            }
            
            // 3. DEBIT: Biaya Kirim (jika ada)
            $biayaKirim = (float) ($pembelian->biaya_kirim ?? 0);
            if ($biayaKirim > 0) {
                // Use Beban Transport Pembelian COA (558)
                // CRITICAL: Filter by user_id for multi-tenant isolation
                $biayaKirimCoa = \App\Models\Coa::where('kode_akun', '558')
                    ->where('user_id', auth()->id())
                    ->first();
                if (!$biayaKirimCoa) {
                    // Fallback to BOP Lainnya (557)
                    $biayaKirimCoa = \App\Models\Coa::where('kode_akun', '557')
                        ->where('user_id', auth()->id())
                        ->first();
                }
                
                if ($biayaKirimCoa) {
                    $lines[] = [
                        'coa_id' => $biayaKirimCoa->id,
                        'debit' => $biayaKirim,
                        'credit' => 0,
                        'memo' => 'Beban Transport Pembelian'
                    ];
                    
                    Log::info("Jurnal Pembelian - Beban Transport Pembelian", [
                        'amount' => $biayaKirim,
                        'coa_used' => $biayaKirimCoa->kode_akun,
                        'coa_name' => $biayaKirimCoa->nama_akun
                    ]);
                } else {
                    Log::warning("Beban Transport Pembelian COA not found, skipping biaya kirim entry", [
                        'biaya_kirim' => $biayaKirim
                    ]);
                }
            }
            
            // 4. CREDIT: Kas/Bank atau Utang berdasarkan metode pembayaran
            $totalAmount = $subtotalAmount + $ppnNominal + $biayaKirim;
            
            Log::info("Getting credit account info", [
                'pembelian_id' => $pembelian->id,
                'bank_id' => $pembelian->bank_id,
                'payment_method' => $pembelian->payment_method,
                'dp' => $pembelian->dp ?? 0
            ]);
            
            // Jika pembelian kredit dan ada DP, buat 2 entry kredit
            if ($pembelian->payment_method === 'credit' && ($pembelian->dp ?? 0) > 0) {
                $dpAmount = (float) $pembelian->dp;
                $sisaUtang = $totalAmount - $dpAmount;
                
                Log::info("Pembelian kredit dengan DP detected", [
                    'total_amount' => $totalAmount,
                    'dp' => $dpAmount,
                    'sisa_utang' => $sisaUtang
                ]);
                
                // CREDIT 1: Kas/Bank untuk DP
                $kasInfo = $this->getKasAccountInfo($pembelian);
                $lines[] = [
                    'coa_id' => $kasInfo['coa_id'],
                    'debit' => 0,
                    'credit' => $dpAmount,
                    'memo' => $kasInfo['memo'] . ' (DP)'
                ];
                
                Log::info("Jurnal Pembelian - DP dari Kas/Bank", [
                    'coa' => $kasInfo['coa_code'],
                    'coa_name' => $kasInfo['coa_name'],
                    'amount' => $dpAmount
                ]);
                
                // CREDIT 2: Utang Usaha untuk sisa
                $utangCoa = $this->getCoaByCode('211'); // Hutang Usaha
                $lines[] = [
                    'coa_id' => $utangCoa->id,
                    'debit' => 0,
                    'credit' => $sisaUtang,
                    'memo' => 'Hutang Usaha (Sisa setelah DP)'
                ];
                
                Log::info("Jurnal Pembelian - Sisa Utang", [
                    'coa' => $utangCoa->kode_akun,
                    'coa_name' => $utangCoa->nama_akun,
                    'amount' => $sisaUtang
                ]);
                
            } else {
                // Logika normal tanpa DP (tidak berubah)
                $creditInfo = $this->getCreditAccountInfo($pembelian);
                
                Log::info("Credit account determined", [
                    'coa_id' => $creditInfo['coa_id'],
                    'coa_code' => $creditInfo['coa_code'],
                    'coa_name' => $creditInfo['coa_name']
                ]);
                
                $lines[] = [
                    'coa_id' => $creditInfo['coa_id'],
                    'debit' => 0,
                    'credit' => $totalAmount,
                    'memo' => $creditInfo['memo']
                ];
                
                Log::info("Jurnal Pembelian - Pembayaran", [
                    'method' => $pembelian->payment_method,
                    'bank_id' => $pembelian->bank_id,
                    'coa' => $creditInfo['coa_code'],
                    'coa_name' => $creditInfo['coa_name'],
                    'amount' => $totalAmount
                ]);
            }
            
            // Validasi balance
            if (!$this->validateBalance($lines)) {
                throw new \Exception("Jurnal tidak balance untuk pembelian {$pembelian->id}");
            }
            
            // Buat jurnal entry
            $success = $this->createJournalEntry($pembelian, $lines);
            
            if ($success) {
                Log::info("Jurnal Pembelian berhasil dibuat", [
                    'pembelian_id' => $pembelian->id,
                    'total_debit' => array_sum(array_column($lines, 'debit')),
                    'total_credit' => array_sum(array_column($lines, 'credit'))
                ]);
                return $pembelian;
            } else {
                Log::error("Jurnal Pembelian gagal dibuat", [
                    'pembelian_id' => $pembelian->id
                ]);
                return null;
            }
            
        } catch (\Exception $e) {
            Log::error("Error creating journal for pembelian {$pembelian->id}: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Dapatkan COA untuk item berdasarkan jenis bahan
     */
    private function getCoaForItem($detail): array
    {
        $coaId = null;
        $coaCode = null;
        $coaName = null;
        $memo = '';
        
        // ==========================================
        // ENHANCED LOGGING FOR DEBUGGING
        // ==========================================
        Log::info('[PembelianJournal] getCoaForItem START', [
            'detail_id' => $detail->id ?? 'unknown',
            'bahan_baku_id' => $detail->bahan_baku_id,
            'bahan_pendukung_id' => $detail->bahan_pendukung_id,
            'has_bahanBaku_relation' => $detail->relationLoaded('bahanBaku'),
            'has_bahanPendukung_relation' => $detail->relationLoaded('bahanPendukung'),
        ]);
        
        if ($detail->bahan_baku_id && $detail->bahanBaku) {
            Log::info('[PembelianJournal] Processing Bahan Baku', [
                'bahan_baku_id' => $detail->bahan_baku_id,
                'nama_bahan' => $detail->bahanBaku->nama_bahan,
                'coa_persediaan_id' => $detail->bahanBaku->coa_persediaan_id,
                'user_id' => auth()->id()
            ]);
            
            // WAJIB gunakan COA Persediaan spesifik dari bahan baku
            if ($detail->bahanBaku->coa_persediaan_id) {
                // coa_persediaan_id berisi kode COA, bukan ID COA
                // CRITICAL: Filter by user_id for multi-tenant isolation
                $coa = Coa::where('kode_akun', $detail->bahanBaku->coa_persediaan_id)
                    ->where('user_id', auth()->id())
                    ->first();
                
                Log::info('[PembelianJournal] COA Query Result', [
                    'searching_kode_akun' => $detail->bahanBaku->coa_persediaan_id,
                    'coa_found' => $coa ? true : false,
                    'coa_id' => $coa ? $coa->id : null,
                    'coa_nama' => $coa ? $coa->nama_akun : null
                ]);
                
                if ($coa) {
                    $coaId = $coa->id;
                    $coaCode = $coa->kode_akun;
                    $coaName = $coa->nama_akun;
                    $memo = $coa->nama_akun; // Langsung pakai nama COA spesifik
                    
                    Log::info('[PembelianJournal] ✓ Bahan Baku COA Found', [
                        'coa_id' => $coaId,
                        'coa_code' => $coaCode,
                        'coa_name' => $coaName
                    ]);
                } else {
                    Log::error('[PembelianJurnal] ✗ COA Not Found', [
                        'bahan' => $detail->bahanBaku->nama_bahan,
                        'coa_persediaan_id' => $detail->bahanBaku->coa_persediaan_id,
                        'user_id' => auth()->id()
                    ]);
                    throw new \Exception("COA Persediaan untuk bahan baku '{$detail->bahanBaku->nama_bahan}' tidak ditemukan (Kode: {$detail->bahanBaku->coa_persediaan_id})");
                }
            } else {
                Log::error('[PembelianJurnal] ✗ COA Persediaan ID NULL', [
                    'bahan' => $detail->bahanBaku->nama_bahan,
                    'bahan_id' => $detail->bahan_baku_id
                ]);
                throw new \Exception("Bahan baku '{$detail->bahanBaku->nama_bahan}' belum memiliki COA Persediaan. Silakan set di master data bahan baku.");
            }
            
        } elseif ($detail->bahan_pendukung_id && $detail->bahanPendukung) {
            Log::info('[PembelianJournal] Processing Bahan Pendukung', [
                'bahan_pendukung_id' => $detail->bahan_pendukung_id,
                'nama_bahan' => $detail->bahanPendukung->nama_bahan,
                'coa_persediaan_id' => $detail->bahanPendukung->coa_persediaan_id,
                'user_id' => auth()->id()
            ]);
            
            // WAJIB gunakan COA Persediaan spesifik dari bahan pendukung
            if ($detail->bahanPendukung->coa_persediaan_id) {
                // coa_persediaan_id berisi kode COA, bukan ID COA
                // CRITICAL: Filter by user_id for multi-tenant isolation
                $coa = Coa::where('kode_akun', $detail->bahanPendukung->coa_persediaan_id)
                    ->where('user_id', auth()->id())
                    ->first();
                
                Log::info('[PembelianJournal] COA Query Result', [
                    'searching_kode_akun' => $detail->bahanPendukung->coa_persediaan_id,
                    'coa_found' => $coa ? true : false,
                    'coa_id' => $coa ? $coa->id : null,
                    'coa_nama' => $coa ? $coa->nama_akun : null
                ]);
                
                if ($coa) {
                    $coaId = $coa->id;
                    $coaCode = $coa->kode_akun;
                    $coaName = $coa->nama_akun;
                    $memo = $coa->nama_akun; // Langsung pakai nama COA spesifik
                    
                    Log::info('[PembelianJournal] ✓ Bahan Pendukung COA Found', [
                        'coa_id' => $coaId,
                        'coa_code' => $coaCode,
                        'coa_name' => $coaName
                    ]);
                } else {
                    Log::error('[PembelianJurnal] ✗ COA Not Found', [
                        'bahan' => $detail->bahanPendukung->nama_bahan,
                        'coa_persediaan_id' => $detail->bahanPendukung->coa_persediaan_id,
                        'user_id' => auth()->id()
                    ]);
                    throw new \Exception("COA Persediaan untuk bahan pendukung '{$detail->bahanPendukung->nama_bahan}' tidak ditemukan (Kode: {$detail->bahanPendukung->coa_persediaan_id})");
                }
            } else {
                Log::error('[PembelianJurnal] ✗ COA Persediaan ID NULL', [
                    'bahan' => $detail->bahanPendukung->nama_bahan,
                    'bahan_id' => $detail->bahan_pendukung_id
                ]);
                throw new \Exception("Bahan pendukung '{$detail->bahanPendukung->nama_bahan}' belum memiliki COA Persediaan. Silakan set di master data bahan pendukung.");
            }
            
        } else {
            Log::error('[PembelianJurnal] ✗ Invalid Detail - No Bahan', [
                'detail_id' => $detail->id ?? 'unknown',
                'bahan_baku_id' => $detail->bahan_baku_id,
                'bahan_pendukung_id' => $detail->bahan_pendukung_id
            ]);
            throw new \Exception("Detail pembelian tidak memiliki bahan baku atau bahan pendukung yang valid");
        }
        
        Log::info('[PembelianJournal] getCoaForItem END', [
            'coa_id' => $coaId,
            'coa_code' => $coaCode,
            'coa_name' => $coaName,
            'memo' => $memo
        ]);
        
        return [
            'coa_id' => $coaId,
            'coa_code' => $coaCode,
            'coa_name' => $coaName,
            'memo' => $memo
        ];
    }
    
    /**
     * Dapatkan informasi akun kredit berdasarkan metode pembayaran
     */
    private function getCreditAccountInfo(Pembelian $pembelian): array
    {
        $coaId = null;
        $coaCode = null;
        $coaName = null;
        $memo = '';
        
        // PRIORITAS 1: Gunakan bank_id yang sudah dipilih user (PALING PENTING!)
        if ($pembelian->bank_id) {
            $coa = Coa::where('id', $pembelian->bank_id)
                ->where('user_id', auth()->id())
                ->first();
            
            if ($coa) {
                return [
                    'coa_id' => $coa->id,
                    'coa_code' => $coa->kode_akun,
                    'coa_name' => $coa->nama_akun,
                    'memo' => $coa->nama_akun
                ];
            }
        }
        
        // PRIORITAS 2: Jika payment_method credit, gunakan Hutang Usaha
        if ($pembelian->payment_method === 'credit') {
            $coa = $this->getCoaByCode('211'); // Hutang Usaha
            return [
                'coa_id' => $coa->id,
                'coa_code' => $coa->kode_akun,
                'coa_name' => $coa->nama_akun,
                'memo' => 'Hutang Usaha'
            ];
        }
        
        // FALLBACK: Kas Bank (seharusnya tidak sampai sini jika bank_id ada)
        $coa = $this->getCoaByCode('111');
        return [
            'coa_id' => $coa->id,
            'coa_code' => $coa->kode_akun,
            'coa_name' => $coa->nama_akun,
            'memo' => $coa->nama_akun
        ];
    }
    
    /**
     * Dapatkan informasi akun kas/bank untuk pembayaran DP
     */
    private function getKasAccountInfo(Pembelian $pembelian): array
    {
        // Gunakan dp_payment_method_id jika ada
        if ($pembelian->dp_payment_method_id) {
            $coa = Coa::where('id', $pembelian->dp_payment_method_id)
                ->where('user_id', auth()->id())
                ->first();
            
            if ($coa) {
                return [
                    'coa_id' => $coa->id,
                    'coa_code' => $coa->kode_akun,
                    'coa_name' => $coa->nama_akun,
                    'memo' => $coa->nama_akun
                ];
            }
        }
        
        // FALLBACK: Gunakan bank_id yang dipilih user untuk DP
        if ($pembelian->bank_id) {
            $coa = Coa::where('id', $pembelian->bank_id)
                ->where('user_id', auth()->id())
                ->first();
            
            if ($coa) {
                return [
                    'coa_id' => $coa->id,
                    'coa_code' => $coa->kode_akun,
                    'coa_name' => $coa->nama_akun,
                    'memo' => $coa->nama_akun
                ];
            }
        }
        
        // FALLBACK: Kas Bank default
        $coa = $this->getCoaByCode('111');
        return [
            'coa_id' => $coa->id,
            'coa_code' => $coa->kode_akun,
            'coa_name' => $coa->nama_akun,
            'memo' => $coa->nama_akun
        ];
    }

    
    /**
     * Dapatkan COA berdasarkan kode
     */
    private function getCoaByCode(string $code): Coa
    {
        // CRITICAL: Filter by user_id for multi-tenant isolation
        $coa = Coa::where('kode_akun', $code)
            ->where('user_id', auth()->id())
            ->first();
        
        if (!$coa) {
            throw new \Exception("COA dengan kode {$code} tidak ditemukan untuk user Anda");
        }
        
        return $coa;
    }
    
    /**
     * Buat journal entry
     */
    private function createJournalEntry(Pembelian $pembelian, array $lines): bool
    {
        $tanggal = $pembelian->tanggal instanceof \Carbon\Carbon ? 
                   $pembelian->tanggal->format('Y-m-d') : 
                   $pembelian->tanggal;
        
        $keterangan = "Pembelian #{$pembelian->nomor_pembelian}";
        if ($pembelian->vendor) {
            $keterangan .= " - {$pembelian->vendor->nama_vendor}";
        }
        
        // CRITICAL: Gunakan user_id dari pembelian untuk multi-tenant isolation
        $userId = $pembelian->user_id ?? auth()->id() ?? 1;
        
        // Buat jurnal entries untuk setiap line
        $journalData = [];
        foreach ($lines as $line) {
            $journalData[] = [
                'user_id' => $userId,  // CRITICAL: Multi-tenant isolation
                'coa_id' => $line['coa_id'],
                'tanggal' => $tanggal,
                'keterangan' => $keterangan,
                'debit' => $line['debit'],
                'kredit' => $line['credit'],
                'referensi' => $pembelian->nomor_pembelian,
                'tipe_referensi' => 'pembelian',
                'created_by' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        // Insert semua jurnal
        try {
            JurnalUmum::insert($journalData);
            
            Log::info("Jurnal entries inserted successfully", [
                'pembelian_id' => $pembelian->id,
                'user_id' => $userId,
                'entries_count' => count($journalData),
                'total_debit' => array_sum(array_column($journalData, 'debit')),
                'total_kredit' => array_sum(array_column($journalData, 'kredit'))
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to insert jurnal for pembelian: " . $e->getMessage(), [
                'pembelian_id' => $pembelian->id,
                'user_id' => $userId,
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }
    
    /**
     * Hapus jurnal yang sudah ada (public method)
     */
    public function deleteExistingJournal(int $pembelianId): void
    {
        $this->deleteExistingJournalPrivate($pembelianId);
    }
    
    /**
     * Hapus jurnal yang sudah ada (private method)
     */
    private function deleteExistingJournalPrivate(int $pembelianId): void
    {
        // Get the pembelian to find its nomor_pembelian
        $pembelian = Pembelian::find($pembelianId);
        if ($pembelian) {
            // Delete existing journal entries based on referensi (nomor_pembelian)
            JurnalUmum::where('tipe_referensi', 'pembelian')
                ->where('referensi', $pembelian->nomor_pembelian)
                ->delete();
                
            Log::info("Deleted existing journal entries for pembelian", [
                'pembelian_id' => $pembelianId,
                'nomor_pembelian' => $pembelian->nomor_pembelian
            ]);
        }
    }
    
    /**
     * Validasi keseimbangan debit-kredit
     */
    private function validateBalance(array $lines): bool
    {
        $totalDebit = array_sum(array_column($lines, 'debit'));
        $totalCredit = array_sum(array_column($lines, 'credit'));
        
        return abs($totalDebit - $totalCredit) < 0.01;
    }
}