<?php

namespace App\Observers;

use App\Models\Pembelian;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Services\JournalService;
use Illuminate\Support\Facades\Log;

class PembelianObserver
{
    protected $journalService;

    public function __construct()
    {
        $this->journalService = new JournalService();
    }

    /**
     * Handle the Pembelian "created" event.
     *
     * @param  \App\Models\Pembelian  $pembelian
     * @return void
     */
    public function created(Pembelian $pembelian)
    {
        // Load relationships to ensure COA data is available
        $pembelian->load([
            'details.bahanBaku.coaPembelian',
            'details.bahanPendukung.coaPembelian'
        ]);
        
        $this->createPembelianJournal($pembelian);
    }

    /**
     * Handle the Pembelian "updated" event.
     *
     * @param  \App\Models\Pembelian  $pembelian
     * @return void
     */
    public function updated(Pembelian $pembelian)
    {
        // Load relationships to ensure COA data is available
        $pembelian->load([
            'details.bahanBaku.coaPembelian',
            'details.bahanPendukung.coaPembelian'
        ]);
        
        // Delete old journal entries and create new ones
        $this->deletePembelianJournals($pembelian->id);
        $this->createPembelianJournal($pembelian);
    }

    /**
     * Handle the Pembelian "deleted" event.
     *
     * @param  \App\Models\Pembelian  $pembelian
     * @return void
     */
    public function deleted(Pembelian $pembelian)
    {
        // Delete associated journal entries when pembelian is deleted
        $this->deletePembelianJournals($pembelian->id);
    }
    
    /**
     * Create journal entries for pembelian
     */
    private function createPembelianJournal(Pembelian $pembelian)
    {
        try {
            // Calculate total from details if total_harga is 0
            $total = $pembelian->total_harga ?? 0;
            if ($total == 0 && $pembelian->details && $pembelian->details->count() > 0) {
                $total = $pembelian->details->sum(function($detail) {
                    return ($detail->jumlah ?? 0) * ($detail->harga_satuan ?? 0);
                });
            }

            // Skip journal creation if total is 0
            if ($total <= 0) {
                Log::info('Skipping journal creation for pembelian with zero total', [
                    'pembelian_id' => $pembelian->id,
                    'total' => $total
                ]);
                return;
            }

            $entries = [];
            
            // Group entries by material type (bahan baku vs bahan pendukung)
            $totalBahanBaku = 0;
            $totalBahanPendukung = 0;
            $bahanBakuItems = [];
            $bahanPendukungItems = [];
            
            foreach($pembelian->details as $detail) {
                $subtotal = ($detail->jumlah ?? 0) * ($detail->harga_satuan ?? 0);
                
                if ($detail->bahanBaku) {
                    $totalBahanBaku += $subtotal;
                    $bahanBakuItems[] = $detail->bahanBaku->nama_bahan;
                } elseif ($detail->bahanPendukung) {
                    $totalBahanPendukung += $subtotal;
                    $bahanPendukungItems[] = $detail->bahanPendukung->nama_bahan;
                }
            }
            
            // Debit Persediaan Bahan Baku jika ada
            if ($totalBahanBaku > 0) {
                $coaPersediaanBahanBaku = \App\Models\Coa::where('kode_akun', '114')->first();
                    
                if ($coaPersediaanBahanBaku) {
                    $entries[] = [
                        'code' => $coaPersediaanBahanBaku->kode_akun, 
                        'debit' => $totalBahanBaku, 
                        'credit' => 0,
                        'memo' => 'Persediaan Bahan Baku: ' . implode(', ', array_unique($bahanBakuItems))
                    ];
                } else {
                    Log::warning('COA Persediaan Bahan Baku (114) not found', [
                        'pembelian_id' => $pembelian->id,
                        'total_bahan_baku' => $totalBahanBaku
                    ]);
                }
            }
            
            // Debit Persediaan Bahan Pendukung jika ada
            if ($totalBahanPendukung > 0) {
                $coaPersediaanBahanPendukung = \App\Models\Coa::where('kode_akun', '115')->first();
                    
                if ($coaPersediaanBahanPendukung) {
                    $entries[] = [
                        'code' => $coaPersediaanBahanPendukung->kode_akun, 
                        'debit' => $totalBahanPendukung, 
                        'credit' => 0,
                        'memo' => 'Persediaan Bahan Pendukung: ' . implode(', ', array_unique($bahanPendukungItems))
                    ];
                } else {
                    Log::warning('COA Persediaan Bahan Pendukung (115) not found', [
                        'pembelian_id' => $pembelian->id,
                        'total_bahan_pendukung' => $totalBahanPendukung
                    ]);
                }
            }
            
            // Tambahkan PPN Masukan jika ada (selalu PPN Masukan untuk pembelian)
            if (($pembelian->ppn_nominal ?? 0) > 0) {
                $coaPpnMasukan = \App\Models\Coa::where('tipe_akun', 'Asset')
                    ->where(function($query) {
                        $query->where('nama_akun', 'like', '%ppn%masukan%')
                              ->orWhere('kode_akun', '127') // Use exact code from database
                              ->orWhere('nama_akun', 'like', '%ppn%masukkan%');
                    })
                    ->first();
                    
                if ($coaPpnMasukan) {
                    $entries[] = [
                        'code' => $coaPpnMasukan->kode_akun, 
                        'debit' => $pembelian->ppn_nominal, 
                        'credit' => 0,
                        'memo' => 'PPN Masukan ' . ($pembelian->ppn_persen ?? 10) . '%'
                    ];
                } else {
                    Log::warning('COA PPN Masukan not found', [
                        'pembelian_id' => $pembelian->id,
                        'ppn_nominal' => $pembelian->ppn_nominal
                    ]);
                }
            }
            
            // Credit Kas/Bank atau Hutang Usaha berdasarkan payment method dan bank_id
            if ($pembelian->payment_method === 'credit') {
                // Credit Hutang Usaha
                $coaHutangUsaha = \App\Models\Coa::where('tipe_akun', 'Liability')
                    ->where(function($query) {
                        $query->where('nama_akun', 'like', '%hutang%usaha%')
                              ->orWhere('kode_akun', '2101');
                    })
                    ->first();
                    
                if ($coaHutangUsaha) {
                    $entries[] = [
                        'code' => $coaHutangUsaha->kode_akun, 
                        'debit' => 0, 
                        'credit' => $total,
                        'memo' => 'Hutang pembelian kredit'
                    ];
                }
            } else {
                // Credit Kas/Bank sesuai payment method
                if ($pembelian->bank_id) {
                    // Jika bank_id ada, gunakan bank yang dipilih
                    $bankCoa = \App\Models\Coa::find($pembelian->bank_id);
                    if ($bankCoa) {
                        $entries[] = [
                            'code' => $bankCoa->kode_akun, 
                            'debit' => 0, 
                            'credit' => $total,
                            'memo' => 'Pembayaran ' . ($pembelian->payment_method === 'cash' ? 'tunai' : 'transfer') . ' pembelian'
                        ];
                    }
                } else {
                    // Jika bank_id tidak ada, tentukan berdasarkan payment_method
                    if ($pembelian->payment_method === 'transfer') {
                        // Transfer -> gunakan akun Bank
                        $coaBank = \App\Models\Coa::where('tipe_akun', 'Asset')
                            ->where(function($query) {
                                $query->where('nama_akun', 'like', '%bank%')
                                      ->orWhere('kode_akun', '1102');
                            })
                            ->first();
                            
                        if ($coaBank) {
                            $entries[] = [
                                'code' => $coaBank->kode_akun, 
                                'debit' => 0, 
                                'credit' => $total,
                                'memo' => 'Pembayaran transfer pembelian'
                            ];
                        }
                    } else {
                        // Cash -> gunakan akun Kas
                        $coaKas = \App\Models\Coa::where('tipe_akun', 'Asset')
                            ->where(function($query) {
                                $query->where('nama_akun', 'like', '%kas%')
                                      ->where('nama_akun', 'not like', '%bank%')
                                      ->orWhere('kode_akun', '1101');
                            })
                            ->first();
                            
                        if ($coaKas) {
                            $entries[] = [
                                'code' => $coaKas->kode_akun, 
                                'debit' => 0, 
                                'credit' => $total,
                                'memo' => 'Pembayaran tunai pembelian'
                            ];
                        }
                    }
                }
            }
            
            // Post journal entry jika ada entries
            if (!empty($entries)) {
                $this->journalService->post(
                    $pembelian->tanggal->format('Y-m-d'),
                    'purchase',
                    $pembelian->id,
                    'Pembelian ' . ($pembelian->vendor->nama_vendor ?? '') . ' - ' . ($pembelian->nomor_pembelian ?? $pembelian->id),
                    $entries
                );
                
                Log::info('Journal created for pembelian', [
                    'pembelian_id' => $pembelian->id,
                    'nomor_pembelian' => $pembelian->nomor_pembelian,
                    'total' => $total,
                    'total_bahan_baku' => $totalBahanBaku,
                    'total_bahan_pendukung' => $totalBahanPendukung,
                    'ppn_nominal' => $pembelian->ppn_nominal ?? 0,
                    'payment_method' => $pembelian->payment_method,
                    'bank_id' => $pembelian->bank_id,
                    'entries_count' => count($entries)
                ]);
            } else {
                Log::warning('No journal entries created for pembelian - COA not found', [
                    'pembelian_id' => $pembelian->id,
                    'total_bahan_baku' => $totalBahanBaku,
                    'total_bahan_pendukung' => $totalBahanPendukung
                ]);
            }
            
        } catch (\Exception $e) {
            Log::error('Failed to create journal for pembelian', [
                'pembelian_id' => $pembelian->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Don't throw exception to avoid breaking pembelian creation
            // Just log the error for manual investigation
        }
    }
    
    /**
     * Delete journal entries for a specific pembelian
     */
    private function deletePembelianJournals($pembelianId)
    {
        try {
            $this->journalService->deleteByRef('purchase', $pembelianId);
            
            Log::info('Journal deleted for pembelian', [
                'pembelian_id' => $pembelianId
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to delete journal for pembelian', [
                'pembelian_id' => $pembelianId,
                'error' => $e->getMessage()
            ]);
        }
    }
}
