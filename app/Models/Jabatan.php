<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Jabatan extends Model
{
    // Removed duplicate boot() method - using booted() instead
    
    protected $fillable = [
        'kode_jabatan', 
        'nama', 
        'kategori',
        'kategori_id',
        'gaji_pokok', 
        'tunjangan', 
        'tunjangan_transport',
        'tunjangan_konsumsi',
        'asuransi', 
        'tarif',
        'tarif_per_jam', 
        'deskripsi',
        'user_id'
    ];

    protected $casts = [
        'gaji_pokok' => 'decimal:2',
        'tunjangan' => 'decimal:2',
        'tunjangan_transport' => 'decimal:2',
        'tunjangan_konsumsi' => 'decimal:2',
        'asuransi' => 'decimal:2',
        'tarif' => 'decimal:2',
        'tarif_per_jam' => 'decimal:2',
    ];

    /**
     * Relasi ke kategori pegawai
     */
    public function kategori(): BelongsTo
    {
        return $this->belongsTo(KategoriPegawai::class, 'kategori_id');
    }

    /**
     * Relasi ke pegawai - using proper foreign key relationship
     */
    public function pegawais(): HasMany
    {
        return $this->hasMany(Pegawai::class, 'jabatan_id');
    }

    /**
     * Get count of active employees for this position
     */
    public function getJumlahPegawaiAttribute()
    {
        return $this->pegawais()->count();
    }
    
    /**
     * Calculate automatic BTKL rate based on tariff and employee count
     */
    public function getTarifBtklAttribute()
    {
        $jumlahPegawai = $this->pegawais()->count();
        return $this->tarif_per_jam * $jumlahPegawai;
    }

    /**
     * Scope untuk mencari jabatan
     */
    public function scopeSearch($query, $search)
    {
        return $query->where('nama', 'like', "%{$search}%")
                    ->orWhere('kategori', 'like', "%{$search}%");
    }

    /**
     * Scope untuk filter by kategori
     */
    public function scopeByKategori($query, $kategori)
    {
        return $query->where('kategori', $kategori);
    }

    /**
     * Boot method untuk model
     */
    protected static function booted()
    {
        parent::booted();
        
        // Auto-assign user_id saat creating
        static::creating(function ($model) {
            if (empty($model->user_id) && auth()->check()) {
                $model->user_id = auth()->id();
            }
        });
        
        // Global scope untuk data isolation (multi-tenant)
        static::addGlobalScope('user', function ($builder) {
            if (auth()->check()) {
                $builder->where('user_id', auth()->id());
            }
        });
    }
}
