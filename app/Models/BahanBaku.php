<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BahanBaku extends Model
{
    use HasFactory;

    protected $table = 'bahan_bakus'; // <--- PENTING: samakan dengan nama tabel di migration
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
        'stok',
        'stok_minimum',
        'keterangan',
        'satuan_id'
    ];

    protected $casts = [
        'harga_satuan' => 'float',
        'stok' => 'float',
        'stok_minimum' => 'float',
    ];
    
    protected $appends = ['stok_aman', 'status_stok', 'harga_per_gram', 'stok_dalam_gram'];

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
        return $this->belongsTo(Satuan::class);
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
}
