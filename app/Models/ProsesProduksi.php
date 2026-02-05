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
        'kapasitas_per_jam',
        'jabatan_id',
    ];

    protected $casts = [
        'tarif_btkl' => 'decimal:2',
        'kapasitas_per_jam' => 'integer',
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
        // Get the last kode_proses with PRO- prefix, ordered by the numeric part
        $lastProses = self::where('kode_proses', 'LIKE', 'PRO-%')
            ->orderByRaw('CAST(SUBSTRING(kode_proses, 5) AS UNSIGNED) DESC')
            ->first();
        
        if ($lastProses && $lastProses->kode_proses) {
            // Extract number from PRO-XXX format
            $lastNumber = (int) substr($lastProses->kode_proses, 4);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }
        
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
     * Get BTKL terkait
     */
    public function btkl()
    {
        return $this->belongsTo(Btkl::class, 'btkl_id');
    }

    /**
     * Get jabatan terkait
     */
    public function jabatan()
    {
        return $this->belongsTo(\App\Models\Jabatan::class, 'jabatan_id');
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
        return $query->where('tarif_btkl', '>', 0);
    }

    /**
     * Hitung biaya BTKL per unit produk
     * Formula: Tarif per Jam ÷ Kapasitas per Jam
     *
     * @return float
     */
    public function getBiayaPerProdukAttribute(): float
    {
        if ($this->kapasitas_per_jam > 0) {
            return $this->tarif_btkl / $this->kapasitas_per_jam;
        }
        return 0;
    }

    /**
     * Format biaya per produk untuk tampilan
     *
     * @return string
     */
    public function getBiayaPerProdukFormattedAttribute(): string
    {
        $biaya = $this->getBiayaPerProdukAttribute();
        if ($biaya > 0) {
            return format_rupiah_clean($biaya);
        }
        return '-';
    }

    /**
     * Get efisiensi produksi (unit per rupiah)
     *
     * @return float
     */
    public function getEfisiensiProduksiAttribute(): float
    {
        if ($this->tarif_btkl > 0) {
            return $this->kapasitas_per_jam / $this->tarif_btkl;
        }
        return 0;
    }

    /**
     * Relasi ke BOP Proses
     */
    public function bopProses()
    {
        return $this->hasOne(BopProses::class, 'proses_produksi_id');
    }

    /**
     * Check if this process has BOP configured
     */
    public function hasBop(): bool
    {
        return $this->bopProses !== null;
    }

    /**
     * Get total cost per unit (BTKL + BOP)
     */
    public function getTotalCostPerUnitAttribute(): float
    {
        $btklCost = $this->biaya_per_produk;
        $bopCost = $this->bopProses ? $this->bopProses->bop_per_unit : 0;
        
        return $btklCost + $bopCost;
    }
}
