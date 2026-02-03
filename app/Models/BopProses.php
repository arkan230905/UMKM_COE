<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BopProses extends Model
{
    use HasFactory;

    protected $table = 'bop_proses';

    protected $fillable = [
        'proses_produksi_id',
        'listrik_per_jam',
        'gas_bbm_per_jam',
        'penyusutan_mesin_per_jam',
        'maintenance_per_jam',
        'gaji_mandor_per_jam',
        'lain_lain_per_jam',
        'komponen_bop',
        'total_bop_per_jam',
        'budget',
        'aktual',
        'kapasitas_per_jam', // Read-only dari BTKL
        'bop_per_unit',
        'is_active'
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
        'budget' => 'decimal:2',
        'aktual' => 'decimal:2',
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
            // Calculate total BOP per jam from JSON components (new structure)
            if ($model->komponen_bop) {
                $komponenBop = is_array($model->komponen_bop) ? $model->komponen_bop : json_decode($model->komponen_bop, true);
                if (is_array($komponenBop)) {
                    $model->total_bop_per_jam = array_sum(array_column($komponenBop, 'rate_per_hour'));
                }
            }

            // Auto-sync kapasitas dari BTKL
            if ($model->prosesProduksi) {
                $model->kapasitas_per_jam = $model->prosesProduksi->kapasitas_per_jam;
            }

            // Auto-calculate BOP per unit
            if ($model->kapasitas_per_jam > 0) {
                $model->bop_per_unit = $model->total_bop_per_jam / $model->kapasitas_per_jam;
            } else {
                $model->bop_per_unit = 0;
            }

            // Set budget to total_bop_per_jam * 8 (8 jam per shift)
            $model->budget = $model->total_bop_per_jam * 8;
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
     * Get komponen BOP dalam array
     */
    public function getKomponenBopAttribute(): array
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
     */
    public function getBopPerUnitFormattedAttribute(): string
    {
        return 'Rp ' . number_format($this->bop_per_unit, 2, ',', '.');
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
     * Hitung selisih budget vs aktual
     */
    public function getSelisihAttribute(): float
    {
        return ($this->budget ?? 0) - ($this->aktual ?? 0);
    }

    /**
     * Format selisih untuk tampilan
     */
    public function getSelisihFormattedAttribute(): string
    {
        $selisih = $this->selisih;
        return 'Rp ' . number_format(abs($selisih), 0, ',', '.') . ($selisih < 0 ? ' (Over)' : '');
    }

    /**
     * Get status class untuk warna
     */
    public function getStatusClassAttribute(): string
    {
        return $this->selisih >= 0 ? 'success' : 'danger';
    }

    /**
     * Get status text
     */
    public function getStatusTextAttribute(): string
    {
        return $this->selisih >= 0 ? 'Under Budget' : 'Over Budget';
    }

    /**
     * Format budget untuk tampilan
     */
    public function getBudgetFormattedAttribute(): string
    {
        return 'Rp ' . number_format($this->budget ?? 0, 0, ',', '.');
    }

    /**
     * Format aktual untuk tampilan
     */
    public function getAktualFormattedAttribute(): string
    {
        return 'Rp ' . number_format($this->aktual ?? 0, 0, ',', '.');
    }
}