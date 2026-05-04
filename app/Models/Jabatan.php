<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Jabatan extends Model
{
    protected $fillable = [
        'user_id',
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
        'deskripsi'
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
     * Relasi ke pegawai dengan multi-tenant isolation
     */
    public function pegawais(): HasMany
    {
        return $this->hasMany(Pegawai::class, 'jabatan_id')
                    ->where('user_id', auth()->id());
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
