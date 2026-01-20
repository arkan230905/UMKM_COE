<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BopLainnya extends Model
{
    use HasFactory;

    protected $table = 'bop_lainnyas';

    protected $fillable = [
        'kode_akun',
        'nama_akun',
        'budget',
        'kuantitas_per_jam',
        'aktual',
        'periode',
        'metode_pembebanan',
        'keterangan',
        'is_active'
    ];

    protected $casts = [
        'budget' => 'decimal:2',
        'aktual' => 'decimal:2',
        'kuantitas_per_jam' => 'integer',
        'is_active' => 'boolean'
    ];

    /**
     * Boot method untuk auto-calculate
     */
    protected static function booted()
    {
        static::saving(function ($model) {
            // Auto-sync nama akun dari COA
            if ($model->kode_akun && !$model->nama_akun) {
                $coa = \App\Models\Coa::where('kode_akun', $model->kode_akun)->first();
                if ($coa) {
                    $model->nama_akun = $coa->nama_akun;
                }
            }
        });
    }

    /**
     * Relasi ke COA
     */
    public function coa()
    {
        return $this->belongsTo(\App\Models\Coa::class, 'kode_akun', 'kode_akun');
    }

    /**
     * Scope untuk BOP aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Format budget untuk tampilan
     */
    public function getBudgetFormattedAttribute(): string
    {
        return 'Rp ' . number_format($this->budget, 0, ',', '.');
    }

    /**
     * Format aktual untuk tampilan
     */
    public function getAktualFormattedAttribute(): string
    {
        return 'Rp ' . number_format($this->aktual ?? 0, 0, ',', '.');
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
     * Hitung biaya per jam
     */
    public function getBiayaPerJamAttribute(): float
    {
        return $this->kuantitas_per_jam > 0 ? ($this->budget ?? 0) / $this->kuantitas_per_jam : 0;
    }

    /**
     * Format biaya per jam untuk tampilan
     */
    public function getBiayaPerJamFormattedAttribute(): string
    {
        return 'Rp ' . number_format($this->biaya_per_jam, 0, ',', '.');
    }
}