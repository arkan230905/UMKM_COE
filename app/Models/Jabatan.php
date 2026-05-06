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
<<<<<<< HEAD
     * Relasi ke pegawai - using proper foreign key relationship
=======
     * Relasi ke pegawai dengan multi-tenant isolation
>>>>>>> cb46e8bf88bbf58f140ce82a4feead3f3abd254b
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
     * Generate kode jabatan otomatis per user (multi-tenant)
     */
    public static function generateKode(): string
    {
        // 🔒 MULTI-TENANT: Only get from logged-in user
        $lastJabatan = self::where('user_id', auth()->id())
            ->orderBy('id', 'desc')
            ->first();
        
        if ($lastJabatan) {
            // Extract number from BT-XXX format
            $lastNumber = (int) substr($lastJabatan->kode_jabatan, 3);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }
        
        return 'BT-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Boot method untuk auto-generate kode_jabatan
     */
    protected static function booted()
    {
        parent::boot();
        
        static::creating(function ($model) {
            // CRITICAL: Auto-fill user_id for multi-tenant isolation
            if (empty($model->user_id) && auth()->check()) {
                $model->user_id = auth()->id();
            }
            
            // Auto-generate kode_jabatan if empty
            if (empty($model->kode_jabatan)) {
                $model->kode_jabatan = self::generateKode();
            }
        });
    }
}
