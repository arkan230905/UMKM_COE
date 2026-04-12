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
            // Hapus jurnal yang sudah ada untuk pembelian ini
            $this->deleteExistingJournalPrivate($pembelian->id);
            
            // Skip jika tidak ada detail
            if (!$pembelian->details || $pembelian->details->isEmpty()) {
                Log::warning("Pembelian {$pembelian->id} tidak memiliki detail, skip jurnal");
                return null;
            }
            
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
                $ppnCoa = $this->getCoaByCode('127'); // PPN Masukan
                
                $lines[] = [
                    'coa_id' => $ppnCoa->id,
                    'debit' => $ppnNominal,
                    'credit' => 0,
                    'memo' => 'PPN Masukan'
                ];
                
                Log::info("Jurnal Pembelian - PPN Masukan", [
                    'persen' => $pembelian->ppn_persen,
                    'nominal' => $ppnNominal
                ]);
            }
            
            // 3. DEBIT: Biaya Kirim (jika ada)
            $biayaKirim = (float) ($pembelian->biaya_kirim ?? 0);
            if ($biayaKirim > 0) {
                $biayaKirimCoa = $this->getCoaByCode('5111'); // Biaya Angkut Pembelian
                
                $lines[] = [
                    'coa_id' => $biayaKirimCoa->id,
                    'debit' => $biayaKirim,
                    'credit' => 0,
                    'memo' => 'Biaya Kirim'
                ];
                
                Log::info("Jurnal Pembelian - Biaya Kirim", [
                    'amount' => $biayaKirim
                ]);
            }
            
            // 4. CREDIT: Kas/Bank atau Utang berdasarkan metode pembayaran
            $totalAmount = $subtotalAmount + $ppnNominal + $biayaKirim;
            $creditInfo = $this->getCreditAccountInfo($pembelian);
            
            $lines[] = [
                'coa_id' => $creditInfo['coa_id'],
                'debit' => 0,
                'credit' => $totalAmount,
                'memo' => $creditInfo['memo']
            ];
            
            Log::info("Jurnal Pembelian - Pembayaran", [
                'method' => $pembelian->payment_method,
                'coa' => $creditInfo['coa_code'],
                'coa_name' => $creditInfo['coa_name'],
                'amount' => $totalAmount
            ]);
            
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
        
        if ($detail->bahan_baku_id && $detail->bahanBaku) {
            // WAJIB gunakan COA Persediaan spesifik dari bahan baku
            if ($detail->bahanBaku->coa_persediaan_id) {
                // coa_persediaan_id berisi kode COA, bukan ID COA
                $coa = Coa::where('kode_akun', $detail->bahanBaku->coa_persediaan_id)->first();
                if ($coa) {
                    $coaId = $coa->id;
                    $coaCode = $coa->kode_akun;
                    $coaName = $coa->nama_akun;
                    $memo = $coa->nama_akun; // Langsung pakai nama COA spesifik
                } else {
                    throw new \Exception("COA Persediaan untuk bahan baku '{$detail->bahanBaku->nama_bahan}' tidak ditemukan (Kode: {$detail->bahanBaku->coa_persediaan_id})");
                }
            } else {
                throw new \Exception("Bahan baku '{$detail->bahanBaku->nama_bahan}' belum memiliki COA Persediaan. Silakan set di master data bahan baku.");
            }
            
        } elseif ($detail->bahan_pendukung_id && $detail->bahanPendukung) {
            // WAJIB gunakan COA Persediaan spesifik dari bahan pendukung
            if ($detail->bahanPendukung->coa_persediaan_id) {
                // coa_persediaan_id berisi kode COA, bukan ID COA
                $coa = Coa::where('kode_akun', $detail->bahanPendukung->coa_persediaan_id)->first();
                if ($coa) {
                    $coaId = $coa->id;
                    $coaCode = $coa->kode_akun;
                    $coaName = $coa->nama_akun;
                    $memo = $coa->nama_akun; // Langsung pakai nama COA spesifik
                } else {
                    throw new \Exception("COA Persediaan untuk bahan pendukung '{$detail->bahanPendukung->nama_bahan}' tidak ditemukan (Kode: {$detail->bahanPendukung->coa_persediaan_id})");
                }
            } else {
                throw new \Exception("Bahan pendukung '{$detail->bahanPendukung->nama_bahan}' belum memiliki COA Persediaan. Silakan set di master data bahan pendukung.");
            }
            
        } else {
            throw new \Exception("Detail pembelian tidak memiliki bahan baku atau bahan pendukung yang valid");
        }
        
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
        
        switch ($pembelian->payment_method) {
            case 'cash':
                if ($pembelian->bank_id) {
                    // Gunakan akun kas/bank spesifik
                    $coa = Coa::find($pembelian->bank_id);
                    if ($coa) {
                        $coaId = $coa->id;
                        $coaCode = $coa->kode_akun;
                        $coaName = $coa->nama_akun;
                        $memo = $coa->nama_akun; // Langsung pakai nama COA
                    }
                }
                
                // Fallback ke Kas
                if (!$coaId) {
                    $coa = $this->getCoaByCode('112'); // Kas
                    $coaId = $coa->id;
                    $coaCode = $coa->kode_akun;
                    $coaName = $coa->nama_akun;
                    $memo = $coa->nama_akun;
                }
                break;
                
            case 'transfer':
                if ($pembelian->bank_id) {
                    // Gunakan akun bank spesifik
                    $coa = Coa::find($pembelian->bank_id);
                    if ($coa) {
                        $coaId = $coa->id;
                        $coaCode = $coa->kode_akun;
                        $coaName = $coa->nama_akun;
                        $memo = $coa->nama_akun; // Langsung pakai nama COA
                    }
                }
                
                // Fallback ke Kas di Bank
                if (!$coaId) {
                    $coa = $this->getCoaByCode('111'); // Kas Bank
                    $coaId = $coa->id;
                    $coaCode = $coa->kode_akun;
                    $coaName = $coa->nama_akun;
                    $memo = $coa->nama_akun;
                }
                break;
                
            case 'credit':
            default:
                // Utang Usaha
                $coa = $this->getCoaByCode('210'); // Hutang Usaha
                $coaId = $coa->id;
                $coaCode = $coa->kode_akun;
                $coaName = $coa->nama_akun;
                $memo = $coa->nama_akun;
                break;
        }
        
        return [
            'coa_id' => $coaId,
            'coa_code' => $coaCode,
            'coa_name' => $coaName,
            'memo' => $memo
        ];
    }
    
    /**
     * Dapatkan COA berdasarkan kode
     */
    private function getCoaByCode(string $code): Coa
    {
        $coa = Coa::where('kode_akun', $code)->first();
        
        if (!$coa) {
            throw new \Exception("COA dengan kode {$code} tidak ditemukan");
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
        
        // Buat jurnal entries untuk setiap line
        $journalData = [];
        foreach ($lines as $line) {
            $journalData[] = [
                'coa_id' => $line['coa_id'],
                'tanggal' => $tanggal,
                'keterangan' => $keterangan,
                'debit' => $line['debit'],
                'kredit' => $line['credit'],
                'referensi' => $pembelian->nomor_pembelian,
                'tipe_referensi' => 'pembelian',
                'created_by' => 1,
            ];
        }
        
        // Insert semua jurnal
        try {
            JurnalUmum::insert($journalData);
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to insert jurnal for pembelian: " . $e->getMessage());
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
        // Hapus jurnal yang sudah ada untuk pembelian ini
        // Note: Kita tidak bisa cari berdasarkan ref_id karena JurnalUmum tidak punya field itu
        // Jadi kita akan hapus berdasarkan referensi yang akan di-set nanti
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