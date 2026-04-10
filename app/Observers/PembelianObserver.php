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
        \Log::info('PembelianObserver created called', [
            'pembelian_id' => $pembelian->id,
            'nomor_pembelian' => $pembelian->nomor_pembelian
        ]);
        
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
            \Log::info('Creating pembelian journal', [
                'pembelian_id' => $pembelian->id,
                'nomor_pembelian' => $pembelian->nomor_pembelian,
                'details_count' => $pembelian->details->count()
            ]);
            
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
            
            // Create entries per item untuk akun persediaan spesifik
            foreach($pembelian->details as $detail) {
                $subtotal = ($detail->jumlah ?? 0) * ($detail->harga_satuan ?? 0);
                
                \Log::info('Processing pembelian detail', [
                    'detail_id' => $detail->id,
                    'subtotal' => $subtotal,
                    'has_bahan_baku' => !is_null($detail->bahanBaku),
                    'has_bahan_pendukung' => !is_null($detail->bahanPendukung)
                ]);
                
                if ($detail->bahanBaku) {
                    // Cari akun persediaan spesifik untuk bahan baku ini
                    $coaPersediaan = $this->findSpecificPersediaanCoa($detail->bahanBaku, 'bahan_baku');
                    
                    if ($coaPersediaan) {
                        $entries[] = [
                            'code' => $coaPersediaan->kode_akun, 
                            'debit' => $subtotal, 
                            'credit' => 0,
                            'memo' => 'Persediaan ' . $detail->bahanBaku->nama_bahan
                        ];
                        
                        \Log::info('Using specific COA for bahan baku', [
                            'item_name' => $detail->bahanBaku->nama_bahan,
                            'kode_akun' => $coaPersediaan->kode_akun,
                            'nama_akun' => $coaPersediaan->nama_akun,
                            'subtotal' => $subtotal
                        ]);
                    } else {
                        // Fallback ke akun umum jika tidak ada spesifik
                        $coaUmum = \App\Models\Coa::where('kode_akun', '114')->first();
                        if ($coaUmum) {
                            $entries[] = [
                                'code' => $coaUmum->kode_akun, 
                                'debit' => $subtotal, 
                                'credit' => 0,
                                'memo' => 'Persediaan Bahan Baku: ' . $detail->bahanBaku->nama_bahan
                            ];
                            
                            \Log::warning('Using fallback COA for bahan baku', [
                                'item_name' => $detail->bahanBaku->nama_bahan,
                                'fallback_kode' => $coaUmum->kode_akun,
                                'fallback_nama' => $coaUmum->nama_akun,
                                'subtotal' => $subtotal
                            ]);
                        }
                    }
                } elseif ($detail->bahanPendukung) {
                    // Cari akun persediaan spesifik untuk bahan pendukung ini
                    $coaPersediaan = $this->findSpecificPersediaanCoa($detail->bahanPendukung, 'bahan_pendukung');
                    
                    if ($coaPersediaan) {
                        $entries[] = [
                            'code' => $coaPersediaan->kode_akun, 
                            'debit' => $subtotal, 
                            'credit' => 0,
                            'memo' => 'Persediaan ' . $detail->bahanPendukung->nama_bahan
                        ];
                        
                        \Log::info('Using specific COA for bahan pendukung', [
                            'item_name' => $detail->bahanPendukung->nama_bahan,
                            'kode_akun' => $coaPersediaan->kode_akun,
                            'nama_akun' => $coaPersediaan->nama_akun,
                            'subtotal' => $subtotal
                        ]);
                    } else {
                        // Fallback ke akun umum jika tidak ada spesifik
                        $coaUmum = \App\Models\Coa::where('kode_akun', '115')->first();
                        if ($coaUmum) {
                            $entries[] = [
                                'code' => $coaUmum->kode_akun, 
                                'debit' => $subtotal, 
                                'credit' => 0,
                                'memo' => 'Persediaan Bahan Pendukung: ' . $detail->bahanPendukung->nama_bahan
                            ];
                            
                            \Log::warning('Using fallback COA for bahan pendukung', [
                                'item_name' => $detail->bahanPendukung->nama_bahan,
                                'fallback_kode' => $coaUmum->kode_akun,
                                'fallback_nama' => $coaUmum->nama_akun,
                                'subtotal' => $subtotal
                            ]);
                        }
                    }
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
                    'ppn_nominal' => $pembelian->ppn_nominal ?? 0,
                    'payment_method' => $pembelian->payment_method,
                    'bank_id' => $pembelian->bank_id,
                    'entries_count' => count($entries)
                ]);
            } else {
                Log::warning('No journal entries created for pembelian - COA not found', [
                    'pembelian_id' => $pembelian->id,
                    'total' => $total
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
     * Find specific persediaan COA for an item
     */
    private function findSpecificPersediaanCoa($item, $type)
    {
        $itemName = $item->nama_bahan;
        
        // Cari akun persediaan yang mengandung kata kunci dari nama item
        $keywords = $this->extractKeywords($itemName);
        
        \Log::info('Finding specific COA for item', [
            'item_name' => $itemName,
            'type' => $type,
            'keywords' => $keywords
        ]);
        
        // Strategi pencarian bertingkat
        $coaPersediaan = null;
        
        // 1. Cari dengan kode akun spesifik yang sudah diketahui
        $specificCodes = [
            'air' => '1150',
            'minyak' => '1151', 
            'gas' => '1152',
            'ayam' => '1141'
        ];
        
        foreach ($keywords as $keyword) {
            if (isset($specificCodes[$keyword])) {
                $coaPersediaan = \App\Models\Coa::where('kode_akun', $specificCodes[$keyword])->first();
                if ($coaPersediaan) {
                    \Log::info('Found COA by specific code mapping', [
                        'keyword' => $keyword,
                        'kode_akun' => $coaPersediaan->kode_akun,
                        'nama_akun' => $coaPersediaan->nama_akun
                    ]);
                    return $coaPersediaan;
                }
            }
        }
        
        // 2. Cari berdasarkan nama akun yang mengandung keyword
        foreach ($keywords as $keyword) {
            $coaPersediaan = \App\Models\Coa::where('tipe_akun', 'Asset')
                ->where('nama_akun', 'like', '%pers%')
                ->where('nama_akun', 'like', '%' . $keyword . '%')
                ->where(function($query) use ($type) {
                    if ($type === 'bahan_baku') {
                        $query->where('nama_akun', 'like', '%bahan%baku%');
                    } else {
                        $query->where('nama_akun', 'like', '%bahan%pendukung%');
                    }
                })
                ->orderBy('kode_akun')
                ->first();
                
            if ($coaPersediaan) {
                \Log::info('Found COA by keyword search', [
                    'keyword' => $keyword,
                    'kode_akun' => $coaPersediaan->kode_akun,
                    'nama_akun' => $coaPersediaan->nama_akun
                ]);
                return $coaPersediaan;
            }
        }
        
        // 3. Cari berdasarkan kode akun range (1140-1149 untuk bahan baku, 1150-1159 untuk bahan pendukung)
        $startCode = $type === 'bahan_baku' ? '1140' : '1150';
        $endCode = $type === 'bahan_baku' ? '1149' : '1159';
        
        foreach ($keywords as $keyword) {
            $coaPersediaan = \App\Models\Coa::where('tipe_akun', 'Asset')
                ->where('kode_akun', '>=', $startCode)
                ->where('kode_akun', '<=', $endCode)
                ->where('nama_akun', 'like', '%' . $keyword . '%')
                ->orderBy('kode_akun')
                ->first();
                
            if ($coaPersediaan) {
                \Log::info('Found COA by code range search', [
                    'keyword' => $keyword,
                    'kode_akun' => $coaPersediaan->kode_akun,
                    'nama_akun' => $coaPersediaan->nama_akun
                ]);
                return $coaPersediaan;
            }
        }
        
        \Log::warning('No specific COA found, will use fallback', [
            'item_name' => $itemName,
            'type' => $type,
            'keywords' => $keywords
        ]);
            
        return null;
    }
    
    /**
     * Extract keywords from item name for COA matching
     */
    private function extractKeywords($itemName)
    {
        $itemName = strtolower($itemName);
        
        // Mapping kata kunci untuk matching yang lebih baik
        $keywordMap = [
            'air galon' => ['air'],
            'air' => ['air'],
            'minyak goreng' => ['minyak'],
            'minyak' => ['minyak'],
            'gas' => ['gas'],
            'tepung terigu' => ['tepung', 'terigu'],
            'tepung maizena' => ['tepung', 'maizena'],
            'tepung' => ['tepung'],
            'lada' => ['lada'],
            'bubuk kaldu' => ['kaldu'],
            'bubuk bawang putih' => ['bawang'],
            'bubuk' => ['bubuk'],
            'kemasan' => ['kemasan'],
            'listrik' => ['listrik'],
            'cabe merah' => ['cabe'],
            'cabe' => ['cabe'],
            'ayam potong' => ['ayam'],
            'ayam kampung' => ['ayam'],
            'ayam' => ['ayam'],
            'bebek' => ['bebek'],
            'garam' => ['garam'],
            'gula' => ['gula'],
            'beras' => ['beras']
        ];
        
        // Cari mapping yang cocok (prioritas yang lebih spesifik dulu)
        foreach ($keywordMap as $pattern => $keywords) {
            if (strpos($itemName, $pattern) !== false) {
                \Log::info('Keyword mapping found', [
                    'item_name' => $itemName,
                    'pattern' => $pattern,
                    'keywords' => $keywords
                ]);
                return $keywords;
            }
        }
        
        // Jika tidak ada mapping, gunakan kata-kata dari nama item
        $words = explode(' ', $itemName);
        $keywords = array_filter($words, function($word) {
            return strlen($word) > 2; // Skip kata pendek seperti "di", "ke", dll
        });
        
        \Log::info('Using default keywords from item name', [
            'item_name' => $itemName,
            'keywords' => array_values($keywords)
        ]);
        
        return array_values($keywords);
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
