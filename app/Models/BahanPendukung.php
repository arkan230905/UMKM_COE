<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BahanPendukung extends Model
{
    use HasFactory;

    protected $table = 'bahan_pendukungs';
    
    protected static function boot()
    {
        parent::boot();
        
        // Global scope for multi-tenant isolation
        static::addGlobalScope('user_id', function ($builder) {
            if (auth()->check()) {
                $builder->where('user_id', auth()->id());
            }
        });
    }

    protected $fillable = [
        'kode_bahan',
        'nama_bahan',
        'deskripsi',
        'satuan_id',
        'harga_satuan',
        'saldo_awal',
        'tanggal_saldo_awal',
        'stok_minimum',
        // 'stok', // REMOVED: stok should not be directly fillable, use saldo_awal for initial stock
        'kategori_id',
        'is_active',
        'sub_satuan_1_id',
        'sub_satuan_1_konversi',
        'sub_satuan_1_nilai',
        'sub_satuan_2_id',
        'sub_satuan_2_konversi',
        'sub_satuan_2_nilai',
        'sub_satuan_3_id',
        'sub_satuan_3_konversi',
        'sub_satuan_3_nilai',
        'coa_pembelian_id',    // COA untuk pembelian
        'coa_persediaan_id',    // COA untuk persediaan
        'coa_hpp_id',          // COA untuk HPP
        'user_id',             // Multi-tenant support
    ];

    protected $casts = [
        'harga_satuan' => 'decimal:2',
        'saldo_awal' => 'decimal:4',
        'tanggal_saldo_awal' => 'date',
        'stok_minimum' => 'decimal:4',
        'is_active' => 'boolean',
        'sub_satuan_1_konversi' => 'decimal:4',
        'sub_satuan_1_nilai' => 'decimal:4',
        'sub_satuan_2_konversi' => 'decimal:4',
        'sub_satuan_2_nilai' => 'decimal:4',
        'sub_satuan_3_konversi' => 'decimal:4',
        'sub_satuan_3_nilai' => 'decimal:4',
    ];

    protected $appends = ['stok_aman', 'status_stok', 'stok'];

    /**
     * Boot method untuk auto-generate kode
     */
    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->kode_bahan)) {
                $model->kode_bahan = self::generateKode();
            }
        });
        
        // NOTE: Initial stock movement is handled by BahanPendukungObserver::created()
        // Do not create stock movement here to avoid duplication
    }

    /**
     * Get the stok attribute (maps to real-time stock from stock movements)
     */
    public function getStokAttribute()
    {
        return $this->stok_real_time;
    }

    /**
     * Set the stok attribute (updates through stock movement system)
     * This is a legacy compatibility method - new code should use StockService directly
     */
    public function setStokAttribute($value)
    {
        // For legacy compatibility, we'll create a stock movement
        // But this should be avoided in new code
        $currentStock = $this->stok_real_time;
        $difference = $value - $currentStock;
        
        if (abs($difference) > 0.0001) {
            // Only create stock movement if the model has been saved (has ID)
            if (!$this->id) {
                \Log::info("Skipping stock movement for unsaved BahanPendukung. Stock will be set on initial save.");
                return;
            }
            
            \Log::warning("Legacy stok setter used for BahanPendukung ID {$this->id}. Use StockService instead.", [
                'current_stock' => $currentStock,
                'new_value' => $value,
                'difference' => $difference
            ]);
            
            // Create a stock movement for the difference
            \App\Models\StockMovement::create([
                'item_type' => 'support',
                'item_id' => $this->id,
                'direction' => $difference > 0 ? 'in' : 'out',
                'qty' => abs($difference),
                'unit' => $this->satuanRelation->nama ?? 'unit',
                'unit_cost' => $this->harga_satuan ?? 0,
                'total_cost' => ($this->harga_satuan ?? 0) * abs($difference),
                'ref_type' => 'adjustment',
                'ref_id' => null,
                'tanggal' => now()->format('Y-m-d'),
                'keterangan' => 'Legacy stock adjustment via model setter'
            ]);
        }
    }

    /**
     * Generate kode bahan otomatis
     */
    public static function generateKode(): string
    {
        $lastBahan = self::orderBy('id', 'desc')->first();
        $nextNumber = $lastBahan ? ((int) substr($lastBahan->kode_bahan, 4)) + 1 : 1;
        return 'BPD-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Relasi ke Satuan
     */
    public function satuan()
    {
        return $this->belongsTo(Satuan::class, 'satuan_id')
            ->withDefault([
                'nama' => 'Tidak Diketahui',
                'kode_satuan' => 'N/A'
            ]);
    }

    /**
     * Relasi ke Satuan (alias untuk backward compatibility)
     */
    public function satuanRelation()
    {
        return $this->belongsTo(Satuan::class, 'satuan_id');
    }

    /**
     * Relasi ke Kategori
     */
    public function kategoriBahanPendukung()
    {
        return $this->belongsTo(KategoriBahanPendukung::class, 'kategori_id');
    }

    /**
     * Get the sub satuan 1
     */
    public function subSatuan1()
    {
        return $this->belongsTo(Satuan::class, 'sub_satuan_1_id');
    }

    /**
     * Get the sub satuan 2
     */
    public function subSatuan2()
    {
        return $this->belongsTo(Satuan::class, 'sub_satuan_2_id');
    }

    /**
     * Get the sub satuan 3
     */
    public function subSatuan3()
    {
        return $this->belongsTo(Satuan::class, 'sub_satuan_3_id');
    }

    /**
     * Get the COA for pembelian
     */
    public function coaPembelian()
    {
        return $this->belongsTo(Coa::class, 'coa_pembelian_id', 'kode_akun');
    }

    /**
     * Get the COA for persediaan
     */
    public function coaPersediaan()
    {
        return $this->belongsTo(Coa::class, 'coa_persediaan_id', 'kode_akun');
    }

    /**
     * Get the COA for HPP
     */
    public function coaHpp()
    {
        return $this->belongsTo(Coa::class, 'coa_hpp_id', 'kode_akun');
    }

    /**
     * Check apakah stok aman
     */
    public function getStokAmanAttribute(): bool
    {
        return $this->stok_real_time >= $this->stok_minimum;
    }

    /**
     * Get real-time stock from stock movements
     */
    public function getStokRealTimeAttribute()
    {
        $stockIn = \App\Models\StockMovement::where('item_type', 'support')
            ->where('item_id', $this->id)
            ->where('direction', 'in')
            ->sum('qty');

        $stockOut = \App\Models\StockMovement::where('item_type', 'support')
            ->where('item_id', $this->id)
            ->where('direction', 'out')
            ->sum('qty');

        $netStock = $stockIn - $stockOut;
        
        // If no stock movements exist, use saldo_awal from master data
        if ($stockIn == 0 && $stockOut == 0 && $this->saldo_awal > 0) {
            return (float)$this->saldo_awal;
        }

        return $netStock;
    }

    /**
     * Calculate sub unit price with new logic for decimal values
     * For decimal values (< 1): (harga_utama * nilai * 100) / 100
     * For whole numbers (>= 1): harga_utama / konversi
     */
    public function calculateSubUnitPrice($subUnitNumber)
    {
        $hargaUtama = $this->harga_satuan_display ?? $this->harga_satuan ?? 0;
        
        if ($hargaUtama <= 0) {
            return 0;
        }

        $konversi = null;
        $nilai = null;

        switch ($subUnitNumber) {
            case 1:
                $konversi = $this->sub_satuan_1_konversi ?? 1;
                $nilai = $this->sub_satuan_1_nilai ?? 1;
                break;
            case 2:
                $konversi = $this->sub_satuan_2_konversi ?? 1;
                $nilai = $this->sub_satuan_2_nilai ?? 1;
                break;
            case 3:
                $konversi = $this->sub_satuan_3_konversi ?? 1;
                $nilai = $this->sub_satuan_3_nilai ?? 1;
                break;
            default:
                return 0;
        }

        // Jika nilai adalah desimal (< 1), gunakan rumus baru
        if ($nilai < 1) {
            // Rumus: (harga_utama * nilai * 100) / 100
            // Contoh: nilai = 0.25 -> (25000 * 0.25 * 100) / 100 = (25000 * 25) / 100
            return ($hargaUtama * $nilai * 100) / 100;
        } else {
            // Untuk nilai >= 1, gunakan rumus: harga_utama / nilai
            return $hargaUtama / $nilai;
        }
    }

    /**
     * Get status stok
     */
    public function getStatusStokAttribute(): string
    {
        if ($this->stok_real_time <= 0) {
            return 'Habis';
        } elseif ($this->stok_real_time < $this->stok_minimum) {
            return 'Menipis';
        }
        return 'Aman';
    }

    /**
     * Scope untuk bahan aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope untuk filter kategori by ID
     */
    public function scopeKategori($query, $kategoriId)
    {
        return $query->where('kategori_id', $kategoriId);
    }

    /**
     * Convert quantity based on production requirements (similar to BahanBaku)
     */
    public function konversiBerdasarkanProduksi($jumlah, $dariSatuan, $keSatuan)
    {
        // PRIORITAS 1: Gunakan konversi dari master data bahan pendukung (sub_satuan)
        $masterConversion = $this->convertToSubUnit($jumlah, $dariSatuan, $keSatuan);
        if ($masterConversion !== $jumlah) {
            // Konversi berhasil menggunakan master data
            return $masterConversion;
        }
        
        // PRIORITAS 2: Fallback ke UnitConverter untuk konversi standar
        $converter = new \App\Support\UnitConverter();
        $convertedQty = $converter->convert($jumlah, $dariSatuan, $keSatuan);
        
        // Jika UnitConverter berhasil konversi (hasil berbeda dari input), gunakan hasilnya
        if (abs($convertedQty - $jumlah) > 0.001) {
            return $convertedQty;
        }
        
        // PRIORITAS 3: Return original jika tidak ada konversi yang cocok
        return $jumlah;
    }

    /**
     * Convert to sub unit using master data conversion factors
     */
    public function convertToSubUnit($jumlah, $dariSatuan, $keSatuan)
    {
        // Jika satuan sama, tidak perlu konversi
        if (strtolower($dariSatuan) === strtolower($keSatuan)) {
            return $jumlah;
        }
        
        $dariSatuanLower = strtolower($dariSatuan);
        $keSatuanLower = strtolower($keSatuan);
        $satuanUtama = strtolower($this->satuanRelation->nama ?? '');
        
        // Konversi dari satuan utama ke sub satuan
        if ($dariSatuanLower === $satuanUtama || str_contains($satuanUtama, $dariSatuanLower)) {
            // Cek sub_satuan_1 - USE NILAI instead of KONVERSI
            if ($this->sub_satuan_1_id && $this->sub_satuan_1_nilai > 0) {
                $subSatuan1 = \App\Models\Satuan::find($this->sub_satuan_1_id);
                if ($subSatuan1 && str_contains(strtolower($subSatuan1->nama), $keSatuanLower)) {
                    return $jumlah * $this->sub_satuan_1_nilai;
                }
            }
            
            // Cek sub_satuan_2 - USE NILAI instead of KONVERSI
            if ($this->sub_satuan_2_id && $this->sub_satuan_2_nilai > 0) {
                $subSatuan2 = \App\Models\Satuan::find($this->sub_satuan_2_id);
                if ($subSatuan2 && str_contains(strtolower($subSatuan2->nama), $keSatuanLower)) {
                    return $jumlah * $this->sub_satuan_2_nilai;
                }
            }
            
            // Cek sub_satuan_3 - USE NILAI instead of KONVERSI
            if ($this->sub_satuan_3_id && $this->sub_satuan_3_nilai > 0) {
                $subSatuan3 = \App\Models\Satuan::find($this->sub_satuan_3_id);
                if ($subSatuan3 && str_contains(strtolower($subSatuan3->nama), $keSatuanLower)) {
                    return $jumlah * $this->sub_satuan_3_nilai;
                }
            }
        }
        
        // Konversi dari sub satuan ke satuan utama (kebalikan) - USE NILAI instead of KONVERSI
        // Cek sub_satuan_1
        if ($this->sub_satuan_1_id && $this->sub_satuan_1_nilai > 0) {
            $subSatuan1 = \App\Models\Satuan::find($this->sub_satuan_1_id);
            if ($subSatuan1 && str_contains(strtolower($subSatuan1->nama), $dariSatuanLower) && 
                ($keSatuanLower === $satuanUtama || str_contains($satuanUtama, $keSatuanLower))) {
                return $jumlah / $this->sub_satuan_1_nilai;
            }
        }
        
        // Cek sub_satuan_2
        if ($this->sub_satuan_2_id && $this->sub_satuan_2_nilai > 0) {
            $subSatuan2 = \App\Models\Satuan::find($this->sub_satuan_2_id);
            if ($subSatuan2 && str_contains(strtolower($subSatuan2->nama), $dariSatuanLower) && 
                ($keSatuanLower === $satuanUtama || str_contains($satuanUtama, $keSatuanLower))) {
                return $jumlah / $this->sub_satuan_2_nilai;
            }
        }
        
        // Cek sub_satuan_3
        if ($this->sub_satuan_3_id && $this->sub_satuan_3_nilai > 0) {
            $subSatuan3 = \App\Models\Satuan::find($this->sub_satuan_3_id);
            if ($subSatuan3 && str_contains(strtolower($subSatuan3->nama), $dariSatuanLower) && 
                ($keSatuanLower === $satuanUtama || str_contains($satuanUtama, $keSatuanLower))) {
                return $jumlah / $this->sub_satuan_3_nilai;
            }
        }
        
        // Tidak ada konversi yang cocok, return jumlah asli
        return $jumlah;
    }
    
    /**
     * Get stok dalam berbagai satuan berdasarkan konversi manual dari pembelian
     */
    public function getStokDalamBerbagaiSatuan()
    {
        $stokSatuan = [];
        
        // Stok dalam satuan utama
        $stokSatuan[$this->satuanRelation->nama ?? 'unit'] = $this->stok;
        
        // Ambil konversi manual dari pembelian terbaru
        $konversiManual = \App\Models\PembelianDetailKonversi::whereHas('pembelianDetail', function($query) {
                $query->where('bahan_pendukung_id', $this->id);
            })
            ->with('satuan')
            ->get()
            ->groupBy('satuan_id');
            
        foreach ($konversiManual as $satuanId => $konversiList) {
            $satuan = $konversiList->first()->satuan;
            if ($satuan) {
                // Hitung rata-rata konversi dari semua pembelian
                $totalKonversi = $konversiList->sum('jumlah_konversi');
                $totalSatuanUtama = $konversiList->sum(function($konversi) {
                    return $konversi->pembelianDetail->jumlah_satuan_utama ?? 0;
                });
                
                if ($totalSatuanUtama > 0) {
                    $rataRataKonversi = $totalKonversi / $totalSatuanUtama;
                    $stokSatuan[$satuan->nama] = $this->stok * $rataRataKonversi;
                }
            }
        }
        
        // Fallback ke konversi otomatis jika tidak ada konversi manual
        if (count($stokSatuan) === 1) {
            if ($this->sub_satuan_1_konversi && $this->subSatuan1) {
                $stokSatuan[$this->subSatuan1->nama] = $this->stok * $this->sub_satuan_1_konversi;
            }
            if ($this->sub_satuan_2_konversi && $this->subSatuan2) {
                $stokSatuan[$this->subSatuan2->nama] = $this->stok * $this->sub_satuan_2_konversi;
            }
            if ($this->sub_satuan_3_konversi && $this->subSatuan3) {
                $stokSatuan[$this->subSatuan3->nama] = $this->stok * $this->sub_satuan_3_konversi;
            }
        }
        
        return $stokSatuan;
    }

    /**
     * Convert quantity to base unit (satuan utama) consistently
     * This is the SINGLE SOURCE OF TRUTH for all unit conversions
     * 
     * @param float $quantity Input quantity
     * @param string $fromUnit Input unit
     * @return float Quantity in base unit (satuan utama)
     */
    public function convertToSatuanUtama($quantity, $fromUnit)
    {
        $quantity = (float) $quantity;
        
        // If fromUnit is numeric, it's a satuan ID - convert to name
        if (is_numeric($fromUnit)) {
            $satuanModel = \App\Models\Satuan::find($fromUnit);
            if ($satuanModel) {
                $fromUnit = $satuanModel->nama;
            }
        }
        
        $fromUnit = strtoupper(trim($fromUnit));
        
        // Get base unit (satuan utama)
        $baseUnit = strtoupper($this->satuanRelation->nama ?? 'UNIT');
        
        \Log::info("CONVERSION START - Bahan Pendukung ID {$this->id}:", [
            'nama_bahan' => $this->nama_bahan,
            'quantity' => $quantity,
            'from_unit' => $fromUnit,
            'base_unit' => $baseUnit
        ]);
        
        // If already in base unit, return as is
        if ($fromUnit === $baseUnit) {
            \Log::info("CONVERSION - Already in base unit:", [
                'result' => $quantity,
                'method' => 'no_conversion_needed'
            ]);
            return $quantity;
        }
        
        // Try to find conversion from sub-units first (more accurate)
        $convertedQty = $this->convertFromSubUnits($quantity, $fromUnit);
        if ($convertedQty !== null) {
            \Log::info("CONVERSION - Using sub-unit conversion:", [
                'result' => $convertedQty,
                'method' => 'sub_unit_conversion'
            ]);
            return $convertedQty;
        }
        
        // Fallback to 1:1 conversion for bahan pendukung
        \Log::warning("CONVERSION - No conversion found, using 1:1 ratio:", [
            'input' => "{$quantity} {$fromUnit}",
            'output' => "{$quantity} {$baseUnit}"
        ]);
        
        return $quantity;
    }

    /**
     * Convert from sub-units defined in bahan pendukung master data
     * This uses the conversion factors stored in the bahan_pendukungs table
     * 
     * @param float $quantity
     * @param string $fromUnit
     * @return float|null Converted quantity or null if unit not found
     */
    private function convertFromSubUnits($quantity, $fromUnit)
    {
        $fromUnit = strtoupper(trim($fromUnit));
        
        // Check sub_satuan_1
        // Formula: quantity (in sub unit) × nilai = quantity in base unit
        // Example: 50 ekor × 0.8 kg/ekor = 40 kg
        if ($this->subSatuan1 && strtoupper($this->subSatuan1->nama) === $fromUnit) {
            if ($this->sub_satuan_1_nilai > 0) {
                return $quantity * $this->sub_satuan_1_nilai;
            }
        }
        
        // Check sub_satuan_2
        if ($this->subSatuan2 && strtoupper($this->subSatuan2->nama) === $fromUnit) {
            if ($this->sub_satuan_2_nilai > 0) {
                return $quantity * $this->sub_satuan_2_nilai;
            }
        }
        
        // Check sub_satuan_3
        if ($this->subSatuan3 && strtoupper($this->subSatuan3->nama) === $fromUnit) {
            if ($this->sub_satuan_3_nilai > 0) {
                return $quantity * $this->sub_satuan_3_nilai;
            }
        }
        
        return null; // Unit not found in sub-units
    }

    /**
     * Helper function to update stock consistently
     * Always use converted quantities in base unit (satuan utama)
     * 
     * @param float $qty Quantity in base unit (satuan utama)
     * @param string $type 'in' for stock increase, 'out' for stock decrease
     * @param string $description Optional description for logging
     * @return bool Success status
     */
    public function updateStok($qty, $type = 'in', $description = '')
    {
        \Log::info("STOCK UPDATE START - Bahan Pendukung ID {$this->id}:", [
            'nama_bahan' => $this->nama_bahan,
            'qty' => $qty,
            'type' => $type,
            'description' => $description,
            'current_stok' => $this->stok
        ]);

        if ($qty <= 0) {
            \Log::warning("Invalid quantity for stock update: {$qty}");
            return false;
        }

        $stokLama = (float) ($this->stok ?? 0);
        
        if ($type === 'in') {
            $stokBaru = $stokLama + $qty;
        } elseif ($type === 'out') {
            // Validate sufficient stock for outbound operations
            if ($stokLama < $qty) {
                \Log::error("Insufficient stock for {$this->nama_bahan}. Available: {$stokLama}, Required: {$qty}");
                return false;
            }
            $stokBaru = $stokLama - $qty;
        } else {
            \Log::error("Invalid stock update type: {$type}. Use 'in' or 'out'");
            return false;
        }

        \Log::info("STOCK UPDATE CALCULATION - Bahan Pendukung ID {$this->id}:", [
            'nama_bahan' => $this->nama_bahan,
            'type' => $type,
            'qty' => $qty,
            'stok_lama' => $stokLama,
            'stok_baru' => $stokBaru,
            'satuan_utama' => $this->satuanRelation->nama ?? 'unit',
            'description' => $description
        ]);

        // Method 1: Direct update using save()
        $this->stok = $stokBaru;
        $saveResult = $this->save();

        \Log::info("STOCK UPDATE SAVE ATTEMPT - Bahan Pendukung ID {$this->id}:", [
            'save_result' => $saveResult,
            'model_stok_after_save' => $this->stok,
            'expected_stok' => $stokBaru
        ]);

        // Method 2: Fallback using direct DB update if save() fails
        if (!$saveResult || abs($this->stok - $stokBaru) > 0.0001) {
            \Log::warning("STOCK UPDATE - Save method failed, using direct DB update");
            
            $dbUpdateResult = \DB::table('bahan_pendukungs')
                ->where('id', $this->id)
                ->update(['stok' => $stokBaru]);
            
            \Log::info("STOCK UPDATE - DB Update Result:", [
                'db_update_successful' => $dbUpdateResult,
                'rows_affected' => $dbUpdateResult
            ]);
        }

        // Final verification
        $this->refresh();
        $finalStock = $this->stok;

        $success = (abs($finalStock - $stokBaru) < 0.0001);
        
        \Log::info("STOCK UPDATE FINAL - Bahan Pendukung ID {$this->id}:", [
            'bahan_pendukung_id' => $this->id,
            'expected_stock' => $stokBaru,
            'actual_stock' => $finalStock,
            'update_successful' => $success,
            'difference' => ($finalStock - $stokBaru)
        ]);

        return $success;
    }
}
