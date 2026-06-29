<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Bop extends Model
{
    use \App\Traits\HasUserScope;
    use HasFactory;

    protected $table = 'bops';
    protected $primaryKey = 'id';
    public $incrementing = true;

    protected $fillable = [
        'user_id',
        'produk_id',
        'periode',
        'jumlah_produksi',
        'coa_id',
        'kode_akun',
        'nama_akun',
        'budget',
        'keterangan',
        'aktual',
        'is_active'
    ];
    
    /**
     * Boot method to auto-fill user_id for multi-tenant isolation
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->user_id) && auth()->check()) {
                $model->user_id = auth()->id();
            }
        });
    }

    protected $casts = [
        'budget' => 'decimal:2',
        'aktual' => 'decimal:2',
        'jumlah_produksi' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    /**
     * Relasi ke model Produk
     */
    public function produk()
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }

    /**
     * Relasi ke model Coa
     */
    public function coa()
    {
        return $this->belongsTo(Coa::class, 'coa_id', 'id');
    }

    protected $appends = ['sisa_budget', 'sisa_budget_formatted', 'bop_per_unit'];

    /**
     * Scope untuk filter akun beban aktif
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
     * Hitung BOP per unit produksi
     * 
     * @return float
     */
    public function getBopPerUnitAttribute()
    {
        if ($this->jumlah_produksi > 0) {
            return $this->budget / $this->jumlah_produksi;
        }
        return 0;
    }

    /**
     * Format sisa budget
     *
     * @return string
     */
    public function getSisaBudgetFormattedAttribute()
    {
        return number_format($this->sisa_budget, 2, ',', '.');
    }
    
    /**
     * Hitung sisa budget
     *
     * @return float
     */
    public function getSisaBudgetAttribute()
    {
        // Hitung total realisasi dari transaksi terkait
        $totalRealisasi = $this->aktual ?? 0;
        
        return $this->budget - $totalRealisasi;
    }

    /**
     * Cek apakah BOP memiliki budget yang sudah diatur
     *
     * @return bool
     */
    public function hasBudget()
    {
        return !is_null($this->budget) && $this->budget > 0;
    }
}
