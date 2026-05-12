<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BomProsesBop extends Model
{
    use HasFactory;

    protected $table = 'bom_proses_bops';

    protected $fillable = [
        'bom_proses_id',
        'komponen_bop_id',
        'kuantitas',
        'tarif',
        'total_biaya'
    ];

    protected $casts = [
        'kuantitas' => 'decimal:4',
        'tarif' => 'decimal:2',
        'total_biaya' => 'decimal:2'
    ];

    /**
     * Boot method untuk auto-calculate total_biaya
     */
    protected static function booted()
    {
        static::saving(function ($model) {
            $model->total_biaya = $model->kuantitas * $model->tarif;
        });
        
        // Update parent BomProses setelah save
        static::saved(function ($model) {
            if ($model->bomProses) {
                $model->bomProses->recalculateBop();
            }
        });
        
        // Update parent BomProses setelah delete
        static::deleted(function ($model) {
            if ($model->bomProses) {
                $model->bomProses->recalculateBop();
            }
        });
    }

    /**
     * Relasi ke BOM Proses
     */
    public function bomProses()
    {
        return $this->belongsTo(BomProses::class, 'bom_proses_id');
    }

    /**
     * Relasi ke Komponen BOP (sistem baru)
     */
    public function komponenBop()
    {
        return $this->belongsTo(KomponenBop::class, 'komponen_bop_id');
    }
    
    /**
     * Relasi ke BOP (sistem lama - backward compatibility)
     */
    public function bop()
    {
        return $this->belongsTo(Bop::class, 'bop_id');
    }
    
    /**
     * Get nama komponen BOP (support sistem lama dan baru)
     */
    public function getNamaBopAttribute()
    {
        // Cek sistem baru dulu (komponen_bop_id)
        if ($this->komponenBop) {
            return $this->komponenBop->nama_komponen;
        }
        
        // Fallback ke sistem lama (bop_id)
        if ($this->bop) {
            return $this->bop->nama_akun;
        }
        
        return 'BOP';
    }
}
