<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProsesProduksi extends Model
{
    use \App\Traits\HasUserScope;
    use HasFactory;

    protected $table = 'proses_produksis';

    protected $fillable = [
        'user_id',
        'kode_proses',
        'nama_proses',
        'deskripsi',
        'jabatan_id',
        'tarif_per_produk',
        'jumlah_pegawai',
        'btkl_id',
    ];

    protected $casts = [
        'tarif_per_produk' => 'decimal:2',
        'jumlah_pegawai' => 'integer',
    ];

    /**
     * Boot method untuk auto-generate kode_proses
     */
    protected static function booted()
    {
        static::creating(function ($model) {
            // CRITICAL: Auto-fill user_id for multi-tenant isolation
            if (empty($model->user_id) && auth()->check()) {
                $model->user_id = auth()->id();
            }
            
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
     * MULTI-TENANT: Filter by user_id untuk isolasi data per user
     */
    public static function generateKode(): string
    {
        $userId = auth()->id();
        
        // Get the last kode_proses with PRO- prefix for this user, ordered by the numeric part
        $lastProses = self::where('user_id', $userId)
            ->where('kode_proses', 'LIKE', 'PRO-%')
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
        return $query->where('tarif_per_produk', '>', 0);
    }

    /**
     * Hitung total BTKL per produk
     * Formula: Tarif per Produk × Jumlah Pegawai
     *
     * @return float
     */
    public function getTotalBtklAttribute(): float
    {
        return ($this->tarif_per_produk ?? 0) * ($this->jumlah_pegawai ?? 0);
    }

    /**
     * Hitung biaya BTKL per unit produk (alias untuk total_btkl)
     *
     * @return float
     */
    public function getBiayaPerProdukAttribute(): float
    {
        return $this->getTotalBtklAttribute();
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
     * Get efisiensi produksi (tidak digunakan lagi dalam sistem per produk)
     *
     * @return float
     */
    public function getEfisiensiProduksiAttribute(): float
    {
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

    /**
     * Format tarif per produk untuk tampilan
     *
     * @return string
     */
    public function getTarifPerProdukFormattedAttribute(): string
    {
        return 'Rp ' . number_format($this->tarif_per_produk, 0, ',', '.');
    }

    /**
     * Format tarif per jam untuk tampilan (backward compatibility)
     * Sekarang menampilkan total BTKL
     *
     * @return string
     */
    public function getTarifPerJamFormattedAttribute(): string
    {
        return 'Rp ' . number_format($this->total_btkl, 0, ',', '.');
    }

    /**
     * Get nama BTKL (alias untuk nama_proses)
     *
     * @return string
     */
    public function getNamaBtklAttribute(): string
    {
        return $this->nama_proses ?? '-';
    }

    /**
     * Get satuan untuk konsistensi (tidak digunakan lagi, return default)
     *
     * @return string
     */
    public function getSatuanAttribute(): string
    {
        return 'Produk';
    }

    /**
     * Get deskripsi proses untuk konsistensi dengan model Btkl
     *
     * @return string
     */
    public function getDeskripsiProsesAttribute(): string
    {
        return $this->deskripsi ?? '-';
    }

    /**
     * Validasi konsistensi tarif BTKL dengan jabatan
     * Memastikan tarif sesuai dengan kualifikasi pegawai
     *
     * @return array
     */
    public function validateTarifConsistency(): array
    {
        if (!$this->jabatan) {
            return [
                'is_consistent' => false,
                'message' => 'Jabatan tidak ditemukan',
                'expected_tarif' => 0,
                'current_tarif' => $this->tarif_per_produk
            ];
        }

        $tarifPerProduk = $this->jabatan->tarif_produk ?? 0;
        $isConsistent = abs($this->tarif_per_produk - $tarifPerProduk) < 0.01; // Toleransi 1 sen
        
        return [
            'is_consistent' => $isConsistent,
            'message' => $isConsistent ? 'Tarif sesuai dengan jabatan' : 'Tarif tidak sesuai dengan jabatan',
            'expected_tarif' => $tarifPerProduk,
            'current_tarif' => $this->tarif_per_produk,
            'jabatan_name' => $this->jabatan->nama_jabatan ?? $this->jabatan->nama ?? '-',
            'jumlah_pegawai' => $this->jumlah_pegawai,
            'tarif_per_produk' => $tarifPerProduk
        ];
    }

    /**
     * Get status konsistensi tarif
     *
     * @return bool
     */
    public function getIsConsistentAttribute(): bool
    {
        return $this->validateTarifConsistency()['is_consistent'];
    }
}
