<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProsesProduksi extends Model
{
    use HasFactory;

    protected $table = 'proses_produksis';

    protected $fillable = [
        'kode_proses',
        'nama_proses',
        'deskripsi',
        'tarif_btkl',
        'satuan_btkl',
        'is_active'
    ];

    protected $casts = [
        'tarif_btkl' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    /**
     * Boot method untuk auto-generate kode_proses
     */
    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->kode_proses)) {
                $model->kode_proses = self::generateKode();
            }
        });
        
        // Prevent deletion if used in BOM
        static::deleting(function ($model) {
            if ($model->bomProses()->exists()) {
                throw new \Exception('Proses produksi tidak dapat dihapus karena digunakan dalam BOM');
            }
        });
    }

    /**
     * Generate kode proses otomatis
     */
    public static function generateKode(): string
    {
        $lastProses = self::orderBy('id', 'desc')->first();
        $nextNumber = $lastProses ? ((int) substr($lastProses->kode_proses, 4)) + 1 : 1;
        return 'PRO-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Relasi ke default BOP components
     */
    public function prosesBops()
    {
        return $this->hasMany(ProsesBop::class, 'proses_produksi_id');
    }

    /**
     * Relasi ke BOM Proses
     */
    public function bomProses()
    {
        return $this->hasMany(BomProses::class, 'proses_produksi_id');
    }

    /**
     * Get komponen BOP melalui pivot
     */
    public function komponenBops()
    {
        return $this->belongsToMany(KomponenBop::class, 'proses_bops', 'proses_produksi_id', 'komponen_bop_id')
            ->withPivot('kuantitas_default')
            ->withTimestamps();
    }

    /**
     * Hitung total BOP default untuk proses ini
     */
    public function getTotalBopDefaultAttribute(): float
    {
        return $this->prosesBops->sum(function ($prosesBop) {
            return $prosesBop->kuantitas_default * ($prosesBop->komponenBop->tarif_per_satuan ?? 0);
        });
    }

    /**
     * Scope untuk proses aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
