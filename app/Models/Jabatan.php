<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Jabatan extends Model
{
    protected static function boot()
    {
        parent::boot();
        
        // Global scope for multi-tenant isolation
        static::addGlobalScope('user_id', function ($builder) {
            if (auth()->check()) {
                $builder->where('user_id', auth()->id());
            }
        });
    }
    
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
     * Relasi ke pegawai - temporary fix for missing jabatan_id column
     */
    public function pegawais(): HasMany
    {
        // Use jabatan string field instead of jabatan_id until column is added
        return $this->hasMany(Pegawai::class, 'jabatan', 'nama');
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
}
