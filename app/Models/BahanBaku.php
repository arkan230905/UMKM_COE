<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BahanBaku extends Model
{
    use HasFactory;

    protected $table = 'bahan_bakus'; // <--- PENTING: samakan dengan nama tabel di migration
    
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
    
    // Nonaktifkan sementara mass assignment protection untuk testing
    protected $guarded = [];
    
    protected $fillable = [
        'nama_bahan',
        'kode_bahan',
        'satuan',
        'satuan_dasar',
        'faktor_konversi',
        'harga_satuan',
        'harga_per_satuan_dasar',
        'harga_rata_rata',
        'saldo_awal',
        'tanggal_saldo_awal',
        'stok_minimum',
        'stok',
        'keterangan',
        'satuan_id',
        'deskripsi',
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
        // NOTE: harga_satuan_display is NOT fillable - it's only for display
    ];

    protected $casts = [
        'harga_satuan' => 'float',
        'harga_per_satuan_dasar' => 'float',
        'harga_rata_rata' => 'float',
        'saldo_awal' => 'decimal:4',
        'tanggal_saldo_awal' => 'date',
        'stok_minimum' => 'float',
        'sub_satuan_1_konversi' => 'decimal:4',
        'sub_satuan_1_nilai' => 'decimal:4',
        'sub_satuan_2_konversi' => 'decimal:4',
        'sub_satuan_2_nilai' => 'decimal:4',
        'sub_satuan_3_konversi' => 'decimal:4',
        'sub_satuan_3_nilai' => 'decimal:4',
    ];
    
    protected $appends = ['stok_aman', 'status_stok', 'harga_per_gram', 'stok_dalam_gram', 'stok'];

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
                \Log::info("Skipping stock movement for unsaved BahanBaku. Stock will be set on initial save.");
                return;
            }
            
            \Log::warning("Legacy stok setter used for BahanBaku ID {$this->id}. Use StockService instead.", [
                'current_stock' => $currentStock,
                'new_value' => $value,
                'difference' => $difference
            ]);
            
            // Create a stock movement for the difference
            \App\Models\StockMovement::create([
                'item_type' => 'material',
                'item_id' => $this->id,
                'direction' => $difference > 0 ? 'in' : 'out',
                'qty' => abs($difference),
                'unit' => $this->satuan->nama ?? 'unit',
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
     * Get the harga per gram attribute.
     *
     * @return float
     */
    public function getHargaPerGramAttribute()
    {
        // Jika harga per satuan dasar tidak ada, return 0
        if (empty($this->harga_per_satuan_dasar)) {
            return 0;
        }

        // Jika satuan dasar adalah gram, return harga per satuan dasar
        if ($this->satuan_dasar === 'gram') {
            return (float) $this->harga_per_satuan_dasar;
        }
        
        // Pastikan faktor_konversi valid (tidak nol)
        if (empty($this->faktor_konversi) || $this->faktor_konversi <= 0) {
            return 0;
        }
        
        // Hitung berdasarkan konversi
        return (float) $this->harga_per_satuan_dasar / (float) $this->faktor_konversi;
    }

    /**
     * Konversi harga dari satuan apapun ke satuan utama
     * Enhanced with specific conversion examples from user requirements
     * 
     * @param float $harga Harga dalam satuan yang dibeli
     * @param string $fromUnit Satuan pembelian
     * @param float $quantity Jumlah yang dibeli
     * @return array [convertedQuantity, hargaPerSatuanUtama]
     */
    public function konversiKeSatuanUtama($harga, $fromUnit, $quantity)
    {
        // Default values jika tidak ada
        $satuanUtama = $this->satuan->nama ?? $this->satuan ?? 'KG';
        
        // Normalisasi satuan
        $fromUnit = strtoupper(trim($fromUnit));
        $satuanUtama = strtoupper(trim($satuanUtama));
        
        // Jika satuan sama, tidak perlu konversi
        if ($fromUnit === $satuanUtama) {
            return [
                'quantity' => $quantity,
                'harga_per_satuan_utama' => $harga
            ];
        }
        
        // Enhanced conversion logic based on user examples
        $konversiFaktor = $this->getKonversiFaktor($fromUnit, $satuanUtama);
        
        // Hitung jumlah dalam satuan utama
        $quantityUtama = $quantity * $konversiFaktor;
        
        // Hitung harga per satuan utama
        // Jika beli 500g @ Rp 10.000, maka 500g = 0.5kg
        // Harga per kg = Rp 10.000 / 0.5kg = Rp 20.000/kg
        if ($quantityUtama > 0) {
            $hargaPerSatuanUtama = ($harga * $quantity) / $quantityUtama;
        } else {
            $hargaPerSatuanUtama = $harga;
        }
        
        return [
            'quantity' => $quantityUtama,
            'harga_per_satuan_utama' => $hargaPerSatuanUtama
        ];
    }
    
    /**
     * Get conversion factor from purchase unit to main unit
     * Based on user examples and standard conversions
     * 
     * @param string $fromUnit Purchase unit
     * @param string $satuanUtama Main unit
     * @return float Conversion factor
     */
    private function getKonversiFaktor($fromUnit, $satuanUtama)
    {
        // Konversi untuk satuan utama KG
        if ($satuanUtama === 'KG' || $satuanUtama === 'KILOGRAM') {
            switch($fromUnit) {
                case 'KG':
                case 'KILOGRAM':
                    return 1; // 15 kg = 15 kg
                case 'G':
                case 'GRAM':
                    return 0.001; // 500 gram = 0.5 kg
                case 'ONS':
                    return 0.1; // 1 ons = 0.1 kg
                case 'L':
                case 'LITER':
                    return 1; // 15 liter = 15 liter (asumsi density = 1)
                case 'ML':
                case 'MILILITER':
                    return 0.001; // 1000 ml = 1 liter = 1 kg
                    
                // Konversi kemasan berdasarkan contoh user
                case 'TABUNG':
                    return 30; // 1 tabung = 30 kg (contoh gas)
                case 'BUNGKUS':
                    return 0.01; // 500 gram = 50 bungkus, jadi 1 bungkus = 10g = 0.01kg
                case 'BOTOL':
                    return 0.5; // Asumsi 1 botol = 500ml = 0.5kg
                case 'KALENG':
                    return 0.4; // Asumsi 1 kaleng = 400g = 0.4kg
                case 'SACHET':
                    return 0.01; // Asumsi 1 sachet = 10g = 0.01kg
                    
                // Satuan kemasan standar
                case 'PCS':
                case 'BUAH':
                case 'PACK':
                case 'PAK':
                case 'BOX':
                    return 1; // Default 1:1
                case 'DUS':
                    return 12; // Asumsi 1 dus = 12 unit
                default:
                    return 1;
            }
        }
        
        // Konversi untuk satuan utama LITER
        if ($satuanUtama === 'L' || $satuanUtama === 'LITER') {
            switch($fromUnit) {
                case 'L':
                case 'LITER':
                    return 1; // 15 liter = 15 liter
                case 'ML':
                case 'MILILITER':
                    return 0.001; // 1000 ml = 1 liter
                case 'KG':
                case 'KILOGRAM':
                    return 1; // Asumsi 1 kg = 1 liter
                case 'G':
                case 'GRAM':
                    return 0.001; // 1000 gram = 1 kg = 1 liter
                case 'BOTOL':
                    return 0.5; // 1 botol = 500ml = 0.5L
                default:
                    return 1;
            }
        }
        
        // Default untuk satuan lain
        return 1;
    }
    
    /**
     * Convert quantity to satuan dasar
     * 
     * @param float $quantity Jumlah yang akan dikonversi
     * @param string $fromUnit Satuan asal
     * @return float Jumlah dalam satuan dasar
     */
    public function convertToSatuanDasar($quantity, $fromUnit)
    {
        $fromUnit = strtoupper(trim($fromUnit));
        
        // Daftar konversi ke gram (asumsikan satuan dasar adalah gram)
        $konversi = [
            'KG' => 1000,
            'KILOGRAM' => 1000,
            'K' => 1000,
            'G' => 1,
            'GRAM' => 1,
            'GR' => 1,
            'LITER' => 1000, // Asumi 1L = 1000g untuk air
            'L' => 1000,
            'ML' => 1,
            'PCS' => 1, // Tidak bisa dikonversi ke gram, asumsikan 1 pcs = 1 satuan dasar
            'PC' => 1,
            'BUAH' => 1,
            'PIECE' => 1,
            'PACK' => 1,
            'PAK' => 1,
            'BUNGKUS' => 1,
            'KALENG' => 1,
            'BOX' => 1,
            'BOTOL' => 1,
            'DUS' => 1,
            'KODI' => 1,
            'LUSIN' => 1,
            'GROSS' => 1,
        ];
        
        // Cek alias
        $unitAliases = [
            'KILO' => 'KG',
            'K' => 'KG',
            'GRAM' => 'G',
            'GRM' => 'G',
            'MILILITER' => 'ML',
            'LITER' => 'L',
            'ONS' => 'HG',
            'KUINTAL' => 'KW',
        ];
        
        if (isset($unitAliases[$fromUnit])) {
            $fromUnit = $unitAliases[$fromUnit];
        }
        
        // Pastikan satuan ada dalam daftar konversi
        if (!array_key_exists($fromUnit, $konversi)) {
            // Log warning untuk satuan tidak dikenali
            \Log::warning("Satuan tidak dikenali: $fromUnit, menggunakan konversi 1:1 untuk bahan baku ID: " . $this->id);
            return $quantity;
        }
        
        $faktorKonversi = $konversi[$fromUnit] ?? 1;
        return $quantity * $faktorKonversi;
    }
    
    /**
     * Convert quantity from satuan dasar
     * 
     * @param float $quantity Jumlah dalam satuan dasar
     * @param string $toUnit Satuan tujuan
     * @return float Jumlah dalam satuan yang diinginkan
     */
    public function convertFromSatuanDasar($quantity, $toUnit)
    {
        $toUnit = strtoupper(trim($toUnit));
        
        // Daftar konversi dari gram ke satuan lain
        $konversi = [
            'KG' => 0.001,
            'KILOGRAM' => 0.001,
            'K' => 0.001,
            'G' => 1,
            'GRAM' => 1,
            'GR' => 1,
            'LITER' => 0.001,
            'L' => 0.001,
            'ML' => 0.001,
            'PCS' => 1,
            'PC' => 1,
            'BUAH' => 1,
            'PIECE' => 1,
            'PACK' => 1,
            'PAK' => 1,
            'BUNGKUS' => 1,
            'KALENG' => 1,
            'BOX' => 1,
            'BOTOL' => 1,
            'DUS' => 1,
            'KODI' => 1,
            'LUSIN' => 1,
            'GROSS' => 1,
        ];
        
        // Validasi satuan
        if (!array_key_exists($toUnit, $konversi)) {
            \Log::warning("Satuan '{$toUnit}' tidak dikenali, menggunakan konversi 1:1", [
                'bahan_baku_id' => $this->id,
                'satuan' => $toUnit
            ]);
        }
        
        $faktorKonversi = $konversi[$toUnit] ?? 1;
        return $quantity * $faktorKonversi;
    }
    
    /**
     * Update harga rata-rata berdasarkan pembelian baru
     * 
     * @param float $hargaBaru Harga per satuan pembelian yang baru
     * @param float $jumlahBaru Jumlah yang dibeli (dalam satuan utama)
     * @return void
     */
    public function updateHargaRataRata($hargaBaru, $jumlahBaru)
    {
        $hargaRataRataLama = $this->harga_rata_rata ?? $this->harga_satuan ?? 0;
        $totalStokLama = $this->stok ?? 0;
        
        // Hitung total stok setelah pembelian
        $totalStokBaru = $totalStokLama + $jumlahBaru;
        
        if ($totalStokBaru > 0) {
            // Weighted average: (harga_lama * stok_lama + harga_baru * jumlah_baru) / total_stok_baru
            $totalHargaLama = $hargaRataRataLama * $totalStokLama;
            $totalHargaBaru = $hargaBaru * $jumlahBaru;
            $hargaRataRataBaru = ($totalHargaLama + $totalHargaBaru) / $totalStokBaru;
            
            $this->update([
                'harga_rata_rata' => $hargaRataRataBaru,
                'harga_satuan' => $hargaRataRataBaru, // Update harga_satuan juga
                'stok' => $totalStokBaru // Update stok juga
            ]);
        }
    }
    
    /**
     * Update harga dan trigger biaya bahan update
     */
    public function updateHarga($hargaBaru)
    {
        $this->update([
            'harga_satuan' => $hargaBaru,
            'harga_rata_rata' => $hargaBaru
        ]);
        
        // Trigger observer untuk update biaya bahan dan BOM
        $this->refresh();
    }
    
    /**
     * Update harga ke harga pembelian terakhir (bukan weighted average)
     * 
     * @param float $hargaBaru Harga per satuan pembelian yang baru
     * @return void
     */
    public function updateHargaTerakhir($hargaBaru)
    {
        $this->update([
            'harga_satuan' => $hargaBaru,
            'harga_rata_rata' => $hargaBaru // Juga update rata-rata ke harga terakhir
        ]);
    }
    
    /**
     * Get harga rata-rata saat ini
     * 
     * @return float
     */
    public function getHargaRataRataSaatIni()
    {
        return $this->harga_rata_rata ?? $this->harga_satuan ?? 0;
    }
    
    /**
     * Recalculate harga rata-rata dari semua pembelian
     */
    public function recalculateHargaRataRataDariPembelian()
    {
        $hargaService = new \App\Services\HargaService();
        return $hargaService->recalculateHargaRataRata($this->id);
    }
    
    /**
     * Validate dan dapatkan laporan harga rata-rata
     */
    public function validateHargaRataRata()
    {
        $hargaService = new \App\Services\HargaService();
        return $hargaService->validateHargaRataRata($this->id);
    }
    
    /**
     * Get riwayat pembelian detail
     */
    public function getRiwayatPembelian($limit = 10)
    {
        $hargaService = new \App\Services\HargaService();
        return $hargaService->getPurchaseHistory($this->id, $limit);
    }

    /**
     * Get the stok dalam gram attribute.
     *
     * @return float
     */
    public function getStokDalamGramAttribute()
    {
        // Jika stok tidak ada, return 0
        if (is_null($this->stok)) {
            return 0;
        }

        // Jika satuan dasar adalah gram, return stok langsung
        if ($this->satuan_dasar === 'gram') {
            return (float) $this->stok;
        }
        
        // Pastikan faktor_konversi valid (tidak nol)
        if (empty($this->faktor_konversi) || $this->faktor_konversi <= 0) {
            return 0;
        }
        
        // Konversi ke gram berdasarkan faktor konversi
        return (float) $this->stok * (float) $this->faktor_konversi;
    }
    
    /**
     * Get the pembelian details for the bahan baku.
     */
    public function pembelianDetails()
    {
        return $this->hasMany(PembelianDetail::class, 'bahan_baku_id');
    }
    
    /**
     * Get the bom details for the bahan baku.
     */
    public function bomDetails()
    {
        return $this->hasMany(BomDetail::class, 'bahan_baku_id');
    }
    
    /**
     * Get the stok aman attribute.
     */
    public function getStokAmanAttribute()
    {
        return $this->stok > $this->stok_minimum;
    }
    
    /**
     * Get the status stok attribute.
     */
    public function getStatusStokAttribute()
    {
        if ($this->stok <= 0) {
            return 'Habis';
        } elseif ($this->stok <= $this->stok_minimum) {
            return 'Hampir Habis';
        }
        return 'Aman';
    }
    
    /**
     * Set the harga_satuan attribute.
     *
     * @param  mixed  $value
     * @return void
     */
    public function setHargaSatuanAttribute($value)
    {
        $this->attributes['harga_satuan'] = (float)$value;
    }

    /**
     * Get the satuan that owns the BahanBaku
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
     * Convert quantity from any unit to KG
     * 
     * @param float $quantity Jumlah yang akan dikonversi
     * @param string $fromUnit Satuan asal
     * @return float Jumlah dalam KG
     */
    public function convertToKg($quantity, $fromUnit)
    {
        $fromUnit = strtoupper(trim($fromUnit));
        $quantity = (float) $quantity;
        
        // Daftar konversi satuan ke KG
        $konversi = [
            // Satuan Berat
            'KG' => 1,          // Kilogram
            'HG' => 0.1,        // Hektogram
            'DAG' => 0.01,      // Dekagram
            'G' => 0.001,       // Gram
            'GR' => 0.001,      // Gram (alternatif)
            'ONS' => 0.1,       // Ons
            'KW' => 100,        // Kuintal
            'KWINTAL' => 100,   // Kuintal (alternatif)
            'TON' => 1000,      // Ton
            'LBS' => 0.453592,  // Pound
            'OZ' => 0.0283495,  // Ounce
            
            // Satuan Volume (untuk cairan)
            'LITER' => 1,       // Liter (asumsi 1L = 1kg untuk air)
            'L' => 1,           // Liter (singkatan)
            'ML' => 0.001,      // Mililiter
            
            // Satuan Standar
            'PCS' => 1,         // Buah/pieces
            'BUAH' => 1,        // Buah
            'BOTOL' => 1,       // Botol
            'PAK' => 1,         // Pak
            'BUNGKUS' => 1,     // Bungkus
            'KALENG' => 1,      // Kaleng
            'BOX' => 1,         // Box (seperti untuk telur)
            
            // Satuan Khusus
            'SENDOK_MAKAN' => 0.015,  // 1 sdm ≈ 15ml ≈ 15g
            'SENDOK_TEH' => 0.005,    // 1 sdt ≈ 5ml ≈ 5g
            'GELAS' => 0.25,          // 1 gelas ≈ 250ml ≈ 250g
        ];
        
        // Normalisasi satuan
        $unitAliases = [
            'GRAM' => 'G',
            'GRM' => 'G',
            'KILO' => 'KG',
            'KILOGRAM' => 'KG',
            'MILILITER' => 'ML',
            'LITER' => 'L',
            'ONS' => 'HG',
            'KUINTAL' => 'KW',
        ];
        
        // Cek alias
        if (isset($unitAliases[$fromUnit])) {
            $fromUnit = $unitAliases[$fromUnit];
        }
        
        // Pastikan satuan ada dalam daftar konversi
        if (!array_key_exists($fromUnit, $konversi)) {
            // Log warning untuk satuan tidak dikenali
            \Log::warning("Satuan tidak dikenali: $fromUnit, menggunakan konversi 1:1 untuk bahan baku ID: " . $this->id);
            return $quantity;
        }
        
        $faktorKonversi = $konversi[$fromUnit];
        $result = $quantity * $faktorKonversi;
        
        // Log untuk debugging
        \Log::debug("Konversi: $quantity $fromUnit = $result KG (Bahan Baku ID: {$this->id})");
        
        return $result;
    }

    /**
     * Check if enough stock is available
     * 
     * @param float $quantity Jumlah yang dibutuhkan
     * @param string $unit Satuan dari jumlah yang dibutuhkan
     * @return bool
     */
    public function hasEnoughStock($quantity, $unit)
    {
        try {
            // Jika stok tidak terbatas (null), langsung return true
            if ($this->stok === null) {
                return true;
            }
            
            $quantityInKg = $this->convertToKg($quantity, $unit);
            return $this->stok >= $quantityInKg;
        } catch (\Exception $e) {
            \Log::error("Error checking stock for BahanBaku ID {$this->id}", [
                'error' => $e->getMessage(),
                'quantity' => $quantity,
                'unit' => $unit,
                'stok' => $this->stok
            ]);
            return false;
        }
    }

    /**
     * Get available stock in specified unit
     * 
     * @param string $unit Satuan yang diinginkan untuk hasil
     * @param int $precision Jumlah digit desimal
     * @return float
     */
    public function getAvailableStock($unit = 'KG', $precision = 4)
    {
        try {
            // Jika stok tidak terbatas (null), return nilai yang sangat besar
            if ($this->stok === null) {
                return PHP_FLOAT_MAX;
            }
            
            return $this->convertFromKg($this->stok, $unit);
        } catch (\Exception $e) {
            \Log::error("Error in getAvailableStock: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Convert quantity from KG to specified unit
     * 
     * @param float $quantity Jumlah dalam KG
     * @param string $toUnit Satuan tujuan
     * @return float Jumlah dalam satuan yang diinginkan
     * @throws \InvalidArgumentException Jika satuan tidak valid
     */
    public function convertFromKg($quantity, $toUnit)
    {
        $toUnit = strtoupper(trim($toUnit));
        $quantity = (float) $quantity;
        
        // Daftar konversi dari KG ke satuan lain
        $konversi = [
            // Satuan Berat
            'KG' => 1,              // Kilogram
            'HG' => 10,             // Hektogram
            'DAG' => 100,           // Dekagram
            'G' => 1000,            // Gram
            'GR' => 1000,           // Gram (alternatif)
            'ONS' => 10,            // Ons
            'KW' => 0.01,           // Kuintal
            'KWINTAL' => 0.01,      // Kuintal (alternatif)
            'TON' => 0.001,         // Ton
            'LBS' => 2.20462,       // Pound
            'OZ' => 35.274,         // Ounce
            
            // Satuan Volume (untuk cairan)
            'LITER' => 1,           // Liter (asumsi 1L = 1kg untuk air)
            'L' => 1,               // Liter (singkatan)
            'ML' => 1000,           // Mililiter
            
            // Satuan Standar
            'PCS' => 1,             // Buah/pieces
            'BUAH' => 1,            // Buah
            'BOTOL' => 1,           // Botol
            'PAK' => 1,             // Pak
            'BUNGKUS' => 1,         // Bungkus
            'DUS' => 1,             // Dus
            'KALENG' => 1,          // Kaleng
            'SACHET' => 1,          // Sachet
            'TABLET' => 1,          // Tablet
            'KAPSUL' => 1,          // Kapsul
            'TUBE' => 1,            // Tube
            'POTONG' => 1,          // Potong
            'LEMBAR' => 1,          // Lembar
            'ROLL' => 1,            // Roll
            'METER' => 1,           // Meter
            'CM' => 100,            // Centimeter
            'MM' => 1000,           // Milimeter
            'INCH' => 39.3701,      // Inci
            'KODI' => 0.05,         // Kodi (1 kodi = 20 buah)
            'LUSIN' => 1/12,        // Lusin (1 lusin = 12 buah)
            'GROSS' => 1/144,       // Gross (1 gross = 12 lusin = 144 buah)
        ];
        
        // Validasi satuan
        if (!array_key_exists($toUnit, $konversi)) {
            \Log::warning("Satuan '{$toUnit}' tidak dikenali, menggunakan konversi 1:1", [
                'bahan_baku_id' => $this->id,
                'satuan' => $toUnit
            ]);
        }
        
        // Jika satuan tidak dikenali, asumsikan 1:1
        $faktorKonversi = $konversi[$toUnit] ?? 1;
        $result = $quantity * $faktorKonversi;
        
        // Log untuk debugging
        \Log::debug("Konversi dari KG: {$quantity} KG = {$result} {$toUnit} (Bahan Baku ID: {$this->id})");
        
        return $result;
    }
    
    /**
     * Konversi berdasarkan data produksi yang sudah ada
     * Menggunakan konversi dari tabel KonversiProduksi jika ada, fallback ke konversi standar
     */
    public function konversiBerdasarkanProduksi($jumlah, $dariSatuan, $keSatuan)
    {
        // PRIORITAS 1: Gunakan konversi dari master data bahan baku (sub_satuan)
        $masterConversion = $this->convertToSubUnit($jumlah, $dariSatuan, $keSatuan);
        if ($masterConversion !== $jumlah) {
            // Konversi berhasil menggunakan master data
            return $masterConversion;
        }
        
        // PRIORITAS 2: Cek apakah ada konversi produksi yang sudah tercatat
        $konversiProduksi = \App\Models\KonversiProduksi::where('bahan_baku_id', $this->id)
            ->where('satuan_asli', $dariSatuan)
            ->where('satuan_hasil', $keSatuan)
            ->where('is_active', true)
            ->first();
            
        if ($konversiProduksi && $konversiProduksi->faktor_konversi > 0) {
            // Gunakan konversi dari data produksi yang sudah ada
            return $jumlah * $konversiProduksi->faktor_konversi;
        }
        
        // PRIORITAS 3: Fallback ke konversi standar
        return $this->convertFromKg($jumlah, $keSatuan);
    }
    
    /**
     * Convert menggunakan data master sub_satuan
     */
    public function convertToSubUnit($jumlah, $dariSatuan, $keSatuan)
    {
        // Jika satuan sama, tidak perlu konversi
        if (strtolower($dariSatuan) === strtolower($keSatuan)) {
            return $jumlah;
        }
        
        $dariSatuanLower = strtolower($dariSatuan);
        $keSatuanLower = strtolower($keSatuan);
        $satuanUtama = strtolower($this->satuan->nama ?? '');
        
        // Konversi dari satuan utama ke sub satuan
        if ($dariSatuanLower === $satuanUtama || str_contains($satuanUtama, $dariSatuanLower)) {
            // Cek sub_satuan_1 (Potong) - USE NILAI instead of KONVERSI
            if ($this->sub_satuan_1_id && $this->sub_satuan_1_nilai > 0) {
                $subSatuan1 = \App\Models\Satuan::find($this->sub_satuan_1_id);
                if ($subSatuan1 && str_contains(strtolower($subSatuan1->nama), $keSatuanLower)) {
                    // 1 Ekor = 6 Potong, jadi sub_satuan_1_nilai = 6
                    return $jumlah * $this->sub_satuan_1_nilai;
                }
            }
            
            // Cek sub_satuan_2 (Kilogram) - USE NILAI instead of KONVERSI
            if ($this->sub_satuan_2_id && $this->sub_satuan_2_nilai > 0) {
                $subSatuan2 = \App\Models\Satuan::find($this->sub_satuan_2_id);
                if ($subSatuan2 && str_contains(strtolower($subSatuan2->nama), $keSatuanLower)) {
                    return $jumlah * $this->sub_satuan_2_nilai;
                }
            }
            
            // Cek sub_satuan_3 (Gram) - USE NILAI instead of KONVERSI
            if ($this->sub_satuan_3_id && $this->sub_satuan_3_nilai > 0) {
                $subSatuan3 = \App\Models\Satuan::find($this->sub_satuan_3_id);
                if ($subSatuan3 && str_contains(strtolower($subSatuan3->nama), $keSatuanLower)) {
                    return $jumlah * $this->sub_satuan_3_nilai;
                }
            }
        }
        
        // Konversi dari sub satuan ke satuan utama (kebalikan) - USE NILAI instead of KONVERSI
        // Cek sub_satuan_1 (Potong ke Ekor)
        if ($this->sub_satuan_1_id && $this->sub_satuan_1_nilai > 0) {
            $subSatuan1 = \App\Models\Satuan::find($this->sub_satuan_1_id);
            if ($subSatuan1 && str_contains(strtolower($subSatuan1->nama), $dariSatuanLower) && 
                ($keSatuanLower === $satuanUtama || str_contains($satuanUtama, $keSatuanLower))) {
                // 6 Potong = 1 Ekor, jadi 1 Potong = 1/6 Ekor
                return $jumlah / $this->sub_satuan_1_nilai;
            }
        }
        
        // Cek sub_satuan_2 (Kilogram ke Ekor)
        if ($this->sub_satuan_2_id && $this->sub_satuan_2_nilai > 0) {
            $subSatuan2 = \App\Models\Satuan::find($this->sub_satuan_2_id);
            if ($subSatuan2 && str_contains(strtolower($subSatuan2->nama), $dariSatuanLower) && 
                ($keSatuanLower === $satuanUtama || str_contains($satuanUtama, $keSatuanLower))) {
                return $jumlah / $this->sub_satuan_2_nilai;
            }
        }
        
        // Cek sub_satuan_3 (Gram ke Ekor)
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
     * Get active konversi for this bahan baku
     */
    public function getActiveKonversi($dariSatuan, $keSatuan)
    {
        return \App\Models\KonversiProduksi::where('bahan_baku_id', $this->id)
            ->where('satuan_asli', $dariSatuan)
            ->where('satuan_hasil', $keSatuan)
            ->where('is_active', true)
            ->first();
    }
    
    /**
     * Get stok dalam berbagai satuan berdasarkan konversi manual dari pembelian
     */
    public function getStokDalamBerbagaiSatuan()
    {
        $stokSatuan = [];
        
        // Stok dalam satuan utama
        $stokSatuan[$this->satuan->nama ?? 'unit'] = $this->stok;
        
        // Ambil konversi manual dari pembelian terbaru
        $konversiManual = \App\Models\PembelianDetailKonversi::whereHas('pembelianDetail', function($query) {
                $query->where('bahan_baku_id', $this->id);
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
        $baseUnit = strtoupper($this->satuan->nama ?? 'KG');
        
        \Log::info("CONVERSION START - Bahan Baku ID {$this->id}:", [
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
        
        // Fallback to standard conversion (for common units like gram, ons, etc.)
        $convertedQty = $this->convertToKg($quantity, $fromUnit);
        
        // If base unit is not KG, we need to convert from KG to base unit
        if ($baseUnit !== 'KG') {
            // This is a simplified approach - in real implementation, 
            // you might need more sophisticated conversion logic
            \Log::warning("CONVERSION - Base unit is not KG, using direct conversion:", [
                'base_unit' => $baseUnit,
                'converted_from_kg' => $convertedQty
            ]);
        }
        
        \Log::info("CONVERSION COMPLETE - Bahan Baku ID {$this->id}:", [
            'input' => "{$quantity} {$fromUnit}",
            'output' => "{$convertedQty} {$baseUnit}",
            'method' => 'standard_conversion'
        ]);
        
        return $convertedQty;
    }

    /**
     * Convert from sub-units defined in bahan baku master data
     * This uses the conversion factors stored in the bahan_baku table
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
        \Log::info("STOCK UPDATE START - Bahan Baku ID {$this->id}:", [
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

        \Log::info("STOCK UPDATE CALCULATION - Bahan Baku ID {$this->id}:", [
            'nama_bahan' => $this->nama_bahan,
            'type' => $type,
            'qty' => $qty,
            'stok_lama' => $stokLama,
            'stok_baru' => $stokBaru,
            'satuan_utama' => $this->satuan->nama ?? 'KG',
            'description' => $description
        ]);

        // Method 1: Direct update using save()
        $this->stok = $stokBaru;
        $saveResult = $this->save();

        \Log::info("STOCK UPDATE SAVE ATTEMPT - Bahan Baku ID {$this->id}:", [
            'save_result' => $saveResult,
            'model_stok_after_save' => $this->stok,
            'expected_stok' => $stokBaru
        ]);

        // Method 2: Fallback using direct DB update if save() fails
        if (!$saveResult || abs($this->stok - $stokBaru) > 0.0001) {
            \Log::warning("STOCK UPDATE - Save method failed, using direct DB update");
            
            $dbUpdateResult = \DB::table('bahan_bakus')
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
        
        \Log::info("STOCK UPDATE FINAL - Bahan Baku ID {$this->id}:", [
            'bahan_baku_id' => $this->id,
            'expected_stock' => $stokBaru,
            'actual_stock' => $finalStock,
            'update_successful' => $success,
            'difference' => ($finalStock - $stokBaru)
        ]);

        return $success;
    }

    /**
     * Sinkronisasi stok master dengan stock movements (untuk konsistensi dengan laporan stok)
     */
    public function syncStokWithMovements()
    {
        \Log::info("SYNC STOCK WITH MOVEMENTS - Bahan Baku ID {$this->id}:", [
            'nama_bahan' => $this->nama_bahan,
            'current_master_stok' => $this->saldo_awal
        ]);

        // Hitung stok real-time dari stock movements
        $stockIn = \App\Models\StockMovement::where('item_type', 'material')
            ->where('item_id', $this->id)
            ->where('direction', 'in')
            ->sum('qty');

        $stockOut = \App\Models\StockMovement::where('item_type', 'material')
            ->where('item_id', $this->id)
            ->where('direction', 'out')
            ->sum('qty');

        $stokRealTime = $stockIn - $stockOut;

        \Log::info("SYNC STOCK CALCULATION - Bahan Baku ID {$this->id}:", [
            'stock_in' => $stockIn,
            'stock_out' => $stockOut,
            'calculated_stock' => $stokRealTime,
            'master_stock' => $this->saldo_awal,
            'difference' => ($this->saldo_awal - $stokRealTime)
        ]);

        // Update jika ada perbedaan
        if (abs($this->saldo_awal - $stokRealTime) > 0.01) {
            $oldStock = $this->saldo_awal;
            $this->saldo_awal = $stokRealTime;
            $result = $this->save();

            \Log::info("SYNC STOCK UPDATE - Bahan Baku ID {$this->id}:", [
                'old_stock' => $oldStock,
                'new_stock' => $stokRealTime,
                'update_successful' => $result
            ]);

            return [
                'updated' => true,
                'old_stock' => $oldStock,
                'new_stock' => $stokRealTime,
                'difference' => ($oldStock - $stokRealTime)
            ];
        }

        return [
            'updated' => false,
            'stock' => $this->saldo_awal,
            'message' => 'Stok sudah konsisten'
        ];
    }

    /**
     * Get stok real-time dari stock movements (konsisten dengan laporan stok)
     */
    public function getStokRealTimeAttribute()
    {
        $stockIn = \App\Models\StockMovement::where('item_type', 'material')
            ->where('item_id', $this->id)
            ->where('direction', 'in')
            ->sum('qty');

        $stockOut = \App\Models\StockMovement::where('item_type', 'material')
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
     * Cek apakah stok master konsisten dengan stock movements
     */
    public function isStokConsistent()
    {
        return abs($this->stok - $this->stok_real_time) <= 0.01;
    }

    /**
     * Sinkronisasi semua bahan baku (static method)
     */
    public static function syncAllStokWithMovements()
    {
        $results = [];
        $bahanBakus = self::all();

        foreach ($bahanBakus as $bahan) {
            $result = $bahan->syncStokWithMovements();
            if ($result['updated']) {
                $results[] = [
                    'id' => $bahan->id,
                    'nama_bahan' => $bahan->nama_bahan,
                    'old_stock' => $result['old_stock'],
                    'new_stock' => $result['new_stock'],
                    'difference' => $result['difference']
                ];
            }
        }

        return $results;
    }
}
