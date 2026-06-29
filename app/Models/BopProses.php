<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BopProses extends Model
{
    use \App\Traits\HasUserScope;
    use HasFactory;

    protected $table = 'bop_proses';

    protected $fillable = [
        'user_id',
        'produk_id',
        'periode',
        'jumlah_produksi',
        'nama_bop_proses',
        'komponen_bahan_pendukung', // JSON: Bahan pendukung yang akan berkurang stoknya saat produksi
        'komponen_lainnya', // JSON: Komponen lainnya (listrik, gas, penyusutan, dll)
        'total_bop_per_produk',
        'total_biaya_per_produk',
        'jumlah_produksi_perbulan', // Jumlah produksi produk per bulan untuk perhitungan Rp/produk
        'keterangan',
        'is_active'
    ];

    protected $casts = [
        'komponen_bahan_pendukung' => 'array',
        'komponen_lainnya' => 'array',
        'total_bop_per_produk' => 'decimal:2',
        'total_biaya_per_produk' => 'decimal:2',
        'jumlah_produksi' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    /**
     * Relationship to Produk
     */
    public function produk()
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }

    /**
     * Boot method untuk auto-set user_id
     */
    protected static function booted()
    {
        static::creating(function ($model) {
            // Auto-set user_id for multi-tenant isolation
            if (empty($model->user_id) && auth()->check()) {
                $model->user_id = auth()->id();
            }
        });
        
        static::saving(function ($model) {
            // Auto-calculate total_bop_per_produk from components
            $totalBop = 0;
            
            // Sum from komponen_bahan_pendukung
            if ($model->komponen_bahan_pendukung && is_array($model->komponen_bahan_pendukung)) {
                foreach ($model->komponen_bahan_pendukung as $komponen) {
                    $totalBop += round((float)($komponen['total'] ?? 0), 2);
                }
            }
            
            // Sum from komponen_lainnya
            if ($model->komponen_lainnya && is_array($model->komponen_lainnya)) {
                foreach ($model->komponen_lainnya as $komponen) {
                    $totalBop += round((float)($komponen['nilai_per_produk'] ?? 0), 2);
                }
            }
            
            $model->total_bop_per_produk = round($totalBop, 2);
        });
    }

    /**
     * Scope untuk BOP aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope untuk filter berdasarkan periode
     */
    public function scopePeriode($query, $periode)
    {
        return $query->where('periode', $periode);
    }

    /**
     * Scope untuk filter berdasarkan produk
     */
    public function scopeByProduk($query, $produkId)
    {
        return $query->where('produk_id', $produkId);
    }

    /**
     * Get BOP per unit accessor
     */
    public function getBopPerUnitAttribute()
    {
        return $this->total_bop_per_produk; // Already per unit
    }

    /**
     * Get all komponen (bahan pendukung + lainnya) for display
     */
    public function getAllKomponenAttribute(): array
    {
        $allKomponen = [];
        
        // Add bahan pendukung
        if ($this->komponen_bahan_pendukung && is_array($this->komponen_bahan_pendukung)) {
            foreach ($this->komponen_bahan_pendukung as $komponen) {
                $allKomponen[] = [
                    'type' => 'bahan_pendukung',
                    'component' => $komponen['nama'] ?? 'N/A',
                    'rate_per_produk' => $komponen['total'] ?? 0,
                    'coa_debit' => $komponen['coa_debit'] ?? null,
                    'coa_kredit' => $komponen['coa_kredit'] ?? null,
                    'keterangan' => $komponen['keterangan'] ?? '',
                    'bahan_pendukung_id' => $komponen['bahan_pendukung_id'] ?? null,
                    'qty_per_produk' => $komponen['qty_per_produk'] ?? 1,
                ];
            }
        }
        
        // Add lainnya
        if ($this->komponen_lainnya && is_array($this->komponen_lainnya)) {
            foreach ($this->komponen_lainnya as $komponen) {
                $allKomponen[] = [
                    'type' => 'lainnya',
                    'component' => $komponen['nama_komponen'] ?? 'N/A',
                    'rate_per_produk' => $komponen['nilai_per_produk'] ?? 0,
                    'coa_debit' => $komponen['coa_debit'] ?? null,
                    'coa_kredit' => $komponen['coa_kredit'] ?? null,
                    'keterangan' => $komponen['keterangan'] ?? '',
                ];
            }
        }
        
        return $allKomponen;
    }

    /**
     * Format BOP per produk untuk tampilan
     */
    public function getTotalBopFormattedAttribute(): string
    {
        $value = $this->total_bop_per_produk;
        
        if ($value == floor($value)) {
            return 'Rp ' . number_format($value, 0, ',', '.');
        }
        
        $formatted = number_format($value, 2, ',', '.');
        $formatted = rtrim($formatted, '0');
        $formatted = rtrim($formatted, ',');
        
        return 'Rp ' . $formatted;
    }

    /**
     * Check if BOP sudah dikonfigurasi
     */
    public function isConfigured(): bool
    {
        return $this->total_bop_per_produk > 0;
    }

    /**
     * Get komponen bahan pendukung count
     */
    public function getBahanPendukungCountAttribute(): int
    {
        return is_array($this->komponen_bahan_pendukung) ? count($this->komponen_bahan_pendukung) : 0;
    }

    /**
     * Get komponen lainnya count
     */
    public function getLainnyaCountAttribute(): int
    {
        return is_array($this->komponen_lainnya) ? count($this->komponen_lainnya) : 0;
    }
}
