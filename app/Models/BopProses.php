<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BopProses extends Model
{
    use HasFactory;

    protected $table = 'bop_proses';

    protected $fillable = [
        'user_id',
        'nama_bop_proses',
        'komponen_bop', // JSON array of components with rate_per_produk
        'total_bop_per_produk', // Total BOP per produk from components
        'total_biaya_per_produk', // Total biaya per produk (BTKL + BOP)
        'total_bop_per_jam', // Backward compatibility
        'kapasitas_per_jam', // Read-only dari BTKL
        'bop_per_unit', // Same as total_bop_per_produk
        'keterangan',
        'budget',
        'aktual',
        'is_active'
    ];

    protected $casts = [
        'komponen_bop' => 'array',
        'total_bop_per_produk' => 'decimal:2',
        'total_biaya_per_produk' => 'decimal:2',
        'total_bop_per_jam' => 'decimal:2',
        'kapasitas_per_jam' => 'integer',
        'bop_per_unit' => 'decimal:4',
        'budget' => 'decimal:2',
        'aktual' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    /**
     * Boot method untuk auto-calculate dan multi-tenant
     */
    protected static function booted()
    {
        static::creating(function ($model) {
            // CRITICAL: Auto-fill user_id for multi-tenant isolation
            if (empty($model->user_id) && auth()->check()) {
                $model->user_id = auth()->id();
            }
        });
        
        static::saving(function ($model) {
            // Calculate total BOP per produk from komponen_bop JSON
            $totalBopPerProduk = 0;
            
            if ($model->komponen_bop && is_array($model->komponen_bop)) {
                foreach ($model->komponen_bop as $komponen) {
                    if (isset($komponen['rate_per_produk'])) {
                        $totalBopPerProduk += floatval($komponen['rate_per_produk']);
                    } elseif (isset($komponen['rate_per_hour'])) {
                        // Fallback for old structure - treat rate_per_hour as rate_per_produk
                        $totalBopPerProduk += floatval($komponen['rate_per_hour']);
                    }
                }
            }
            
            // Calculate BTKL per produk
            $btklPerProduk = 0;
            if ($model->prosesProduksi && $model->prosesProduksi->kapasitas_per_jam > 0) {
                $btklPerProduk = floatval($model->prosesProduksi->tarif_btkl) / floatval($model->prosesProduksi->kapasitas_per_jam);
            }
            
            // Calculate total biaya per produk (BTKL + BOP)
            $totalBiayaPerProduk = $btklPerProduk + $totalBopPerProduk;
            
            // Calculate total BOP per jam (backward compatibility)
            $totalBopPerJam = 
                floatval($model->listrik_per_jam ?? 0) +
                floatval($model->gas_bbm_per_jam ?? 0) +
                floatval($model->penyusutan_mesin_per_jam ?? 0) +
                floatval($model->maintenance_per_jam ?? 0) +
                floatval($model->gaji_mandor_per_jam ?? 0) +
                floatval($model->lain_lain_per_jam ?? 0);
            
            // Set calculated values
            $model->total_bop_per_produk = $totalBopPerProduk;
            $model->total_biaya_per_produk = $totalBiayaPerProduk;
            $model->bop_per_unit = $totalBopPerProduk; // For backward compatibility
            $model->total_bop_per_jam = $totalBopPerJam;

            // Auto-sync kapasitas dari BTKL
            if ($model->prosesProduksi) {
                $model->kapasitas_per_jam = $model->prosesProduksi->kapasitas_per_jam;
            }
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