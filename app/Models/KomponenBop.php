<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KomponenBop extends Model
{
    use HasFactory;

    protected $table = 'komponen_bops';

    protected $fillable = [
        'kode_komponen',
        'nama_komponen',
        'satuan',
        'tarif_per_satuan',
        'is_active'
    ];

    protected $casts = [
        'tarif_per_satuan' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    /**
     * Boot method untuk auto-generate kode_komponen
     */
    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->kode_komponen)) {
                $model->kode_komponen = self::generateKode();
            }
        });
        
        // Prevent deletion if used in BOM
        static::deleting(function ($model) {
            if ($model->bomProsesBops()->exists()) {
                throw new \Exception('Komponen BOP tidak dapat dihapus karena digunakan dalam BOM');
            }
        });
    }

    /**
     * Generate kode komponen otomatis
     */
    public static function generateKode(): string
    {
        $lastKomponen = self::orderBy('id', 'desc')->first();
        $nextNumber = $lastKomponen ? ((int) substr($lastKomponen->kode_komponen, 4)) + 1 : 1;
        return 'BOP-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Relasi ke proses yang menggunakan komponen ini
     */
    public function prosesBops()
    {
        return $this->hasMany(ProsesBop::class, 'komponen_bop_id');
    }

    /**
     * Relasi ke BOM Proses BOP
     */
    public function bomProsesBops()
    {
        return $this->hasMany(BomProsesBop::class, 'komponen_bop_id');
    }

    /**
     * Get proses produksi melalui pivot
     */
    public function prosesProduksis()
    {
        return $this->belongsToMany(ProsesProduksi::class, 'proses_bops', 'komponen_bop_id', 'proses_produksi_id')
            ->withPivot('kuantitas_default')
            ->withTimestamps();
    }

    /**
     * Scope untuk komponen aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
