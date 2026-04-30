<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BopProses extends Model
{
    use HasFactory;

    protected $table = 'bop_proses';

    protected $fillable = [
        'nama_bop_proses',
        'proses_produksi_id',
        'listrik_per_jam',
        'gas_bbm_per_jam',
        'penyusutan_mesin_per_jam',
        'maintenance_per_jam',
        'gaji_mandor_per_jam',
        'lain_lain_per_jam',
        'komponen_bop',
        'total_bop_per_jam',
        'kapasitas_per_jam',
        'bop_per_unit',
        'periode',
        'keterangan',
        'is_active',
    ];

    protected $casts = [
        'listrik_per_jam' => 'decimal:2',
        'gas_bbm_per_jam' => 'decimal:2',
        'penyusutan_mesin_per_jam' => 'decimal:2',
        'maintenance_per_jam' => 'decimal:2',
        'gaji_mandor_per_jam' => 'decimal:2',
        'lain_lain_per_jam' => 'decimal:2',
        'komponen_bop' => 'array',
        'total_bop_per_jam' => 'decimal:2',
        'kapasitas_per_jam' => 'integer',
        'bop_per_unit' => 'decimal:4',
        'is_active' => 'boolean'
    ];

    /**
     * Boot method untuk auto-calculate
     */
    protected static function booted()
    {
        static::saving(function ($model) {
            // Calculate total BOP per jam from components
            $totalBop = 0;
            
            // Check if we have JSON komponen_bop (new structure)
            if ($model->komponen_bop) {
                $komponenBop = is_array($model->komponen_bop) ? $model->komponen_bop : json_decode($model->komponen_bop, true);
                if (is_array($komponenBop) && isset($komponenBop[0]['rate_per_hour'])) {
                    // New JSON structure
                    $totalBop = array_sum(array_column($komponenBop, 'rate_per_hour'));
                }
            }
            
            // If no JSON data or total is 0, calculate from individual fields (old structure)
            if ($totalBop == 0) {
                $totalBop = 
                    floatval($model->listrik_per_jam ?? 0) +
                    floatval($model->gas_bbm_per_jam ?? 0) +
                    floatval($model->penyusutan_mesin_per_jam ?? 0) +
                    floatval($model->maintenance_per_jam ?? 0) +
                    floatval($model->gaji_mandor_per_jam ?? 0) +
                    floatval($model->lain_lain_per_jam ?? 0);
            }
            
            $model->total_bop_per_jam = $totalBop;

            // Auto-sync kapasitas dari BTKL
            if ($model->prosesProduksi) {
                $model->kapasitas_per_jam = $model->prosesProduksi->kapasitas_per_jam;
            }

            // Auto-calculate BOP per unit using per-product basis
            // BOP per unit is same as total BOP (no division by capacity)
            $model->bop_per_unit = $totalBop;
        });
    }

    /**
     * Relasi ke Proses Produksi (BTKL)
     */
    public function prosesProduksi()
    {
        return $this->belongsTo(ProsesProduksi::class, 'proses_produksi_id');
    }

    /**
     * Scope untuk BOP aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get komponen BOP dalam array - raw data dari database
     */
    public function getRawKomponenBopAttribute()
    {
        return $this->attributes['komponen_bop'] ?? null;
    }

    /**
     * Get komponen BOP dalam format lama untuk backward compatibility
     */
    public function getKomponenBopLegacyAttribute(): array
    {
        return [
            'listrik_per_jam' => $this->listrik_per_jam ?? 0,
            'gas_bbm_per_jam' => $this->gas_bbm_per_jam ?? 0,
            'penyusutan_mesin_per_jam' => $this->penyusutan_mesin_per_jam ?? 0,
            'maintenance_per_jam' => $this->maintenance_per_jam ?? 0,
            'gaji_mandor_per_jam' => $this->gaji_mandor_per_jam ?? 0,
            'lain_lain_per_jam' => $this->lain_lain_per_jam ?? 0,
        ];
    }

    /**
     * Format BOP per unit untuk tampilan
     * Hanya tampilkan desimal jika ada nilai desimal
     */
    public function getBopPerUnitFormattedAttribute(): string
    {
        $value = $this->bop_per_unit;
        
        // Jika nilai adalah integer (tidak ada desimal), tampilkan tanpa desimal
        if ($value == floor($value)) {
            return 'Rp ' . number_format($value, 0, ',', '.');
        }
        
        // Jika ada desimal, format dengan 2 desimal dan hapus trailing zeros
        $formatted = number_format($value, 2, ',', '.');
        
        // Hapus trailing zeros setelah koma
        $formatted = rtrim($formatted, '0');
        $formatted = rtrim($formatted, ',');
        
        return 'Rp ' . $formatted;
    }

    /**
     * Format total BOP per jam untuk tampilan
     */
    public function getTotalBopPerJamFormattedAttribute(): string
    {
        return 'Rp ' . number_format($this->total_bop_per_jam, 0, ',', '.');
    }

    /**
     * Cek apakah BOP sudah dikonfigurasi
     */
    public function isConfigured(): bool
    {
        return $this->total_bop_per_jam > 0;
    }

    /**
     * Get efisiensi BOP (unit per rupiah)
     */
    public function getEfisiensiBopAttribute(): float
    {
        if ($this->total_bop_per_jam > 0) {
            return $this->kapasitas_per_jam / $this->total_bop_per_jam;
        }
        return 0;
    }

    /**
     * Get biaya per produk (BTKL per pcs + BOP per pcs)
     */
    public function getBiayaPerProdukAttribute(): float
    {
        if ($this->kapasitas_per_jam > 0 && $this->prosesProduksi) {
            $btklPerPcs = $this->prosesProduksi->tarif_btkl / $this->kapasitas_per_jam;
            return $this->bop_per_unit + $btklPerPcs;
        }
        return 0;
    }

    /**
     * Format biaya per produk untuk tampilan
     * Hanya tampilkan desimal jika ada nilai desimal
     */
    public function getBiayaPerProdukFormattedAttribute(): string
    {
        $value = $this->biaya_per_produk;
        
        // Jika nilai adalah integer (tidak ada desimal), tampilkan tanpa desimal
        if ($value == floor($value)) {
            return 'Rp ' . number_format($value, 0, ',', '.');
        }
        
        // Jika ada desimal, format dengan 2 desimal dan hapus trailing zeros
        $formatted = number_format($value, 2, ',', '.');
        
        // Hapus trailing zeros setelah koma
        $formatted = rtrim($formatted, '0');
        $formatted = rtrim($formatted, ',');
        
        return 'Rp ' . $formatted;
    }

}