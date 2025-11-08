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
    
    protected $casts = [
        'harga_satuan' => 'float',
        'stok' => 'float',
    ];
    
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
     */
    public function convertToKg($quantity, $fromUnit)
    {
        $fromUnit = strtoupper(trim($fromUnit));
        
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
     */
    public function hasEnoughStock($quantity, $unit)
    {
        // Jika stok tidak terbatas (null), langsung return true
        if ($this->stok === null) {
            return true;
        }
        
        // Konversi jumlah yang diminta ke KG
        $quantityInKg = $this->convertToKg($quantity, $unit);
        
        // Jika stok 0, maka tidak cukup
        if ($this->stok <= 0) {
            return false;
        }
        
        // Bandingkan dengan stok yang ada
        return $this->stok >= $quantityInKg;
    }

    /**
     * Get available stock in specified unit
     */
    public function getAvailableStock($unit = 'KG')
    {
        // Jika stok tidak terbatas (null), return nilai besar
        if ($this->stok === null) {
            return PHP_FLOAT_MAX; // Angka yang sangat besar
        }
        
        // Jika stok 0, langsung return 0
        if ($this->stok <= 0) {
            return 0;
        }
        
        try {
            $faktorKonversi = $this->convertToKg(1, $unit);
            
            // Pastikan tidak terjadi pembagian dengan nol
            if ($faktorKonversi == 0) {
                return 0;
            }
            
            $kgToUnit = 1 / $faktorKonversi;
            $available = $this->stok * $kgToUnit;
            
            // Pastikan tidak mengembalikan nilai negatif
            return max(0, $available);
        } catch (\Exception $e) {
            \Log::error("Error in getAvailableStock: " . $e->getMessage());
            return 0;
        }
    }
}
