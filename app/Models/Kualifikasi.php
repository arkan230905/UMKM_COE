<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Kualifikasi extends Model
{
    protected $table = 'kualifikasis';

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
        'produk_id',
        'kode_kualifikasi', 
        'nama_kualifikasi', 
        'kategori',
        'kategori_id',
        'gaji_pokok', 
        'tunjangan', 
        'tunjangan_transport',
        'tunjangan_konsumsi',
        'asuransi', 
        'tarif',
        'tarif_produk',
        'target_produk_per_bulan',
        'target_produksi',
        'deskripsi'
    ];
    
    
    protected $casts = [
        'gaji_pokok' => 'decimal:2',
        'tunjangan' => 'decimal:2',
        'tunjangan_transport' => 'decimal:2',
        'tunjangan_konsumsi' => 'decimal:2',
        'asuransi' => 'decimal:2',
        'tarif' => 'decimal:2',
        'tarif_produk' => 'decimal:2',
        'target_produk_per_bulan' => 'integer',
    ];

    /**
     * Accessor untuk gaji_pokok - fallback ke gaji jika kosong
     * CRITICAL: Ini memastikan field gaji_pokok selalu terisi dari satu source
     */
    public function getGajiPokokAttribute()
    {
        $value = $this->attributes['gaji_pokok'] ?? 0;
        // Jika gaji_pokok kosong/0, fallback ke field gaji
        if (!$value || $value == 0) {
            return $this->attributes['gaji'] ?? 0;
        }
        return $value;
    }

    /**
     * Relasi ke kategori pegawai
     */
    public function kategori(): BelongsTo
    {
        return $this->belongsTo(KategoriPegawai::class, 'kategori_id');
    }

    /**
     * Relasi ke pegawai dengan multi-tenant isolation
     * FIXED: Use kualifikasi (string) instead of kualifikasi_id for matching
     * Note: Don't add where clause here, let global scope handle user_id filtering
     */
    public function pegawais(): HasMany
    {
        // Match by kualifikasi name (string) instead of kualifikasi_id
        // Global scope on Pegawai model will automatically filter by user_id
        return $this->hasMany(Pegawai::class, 'kualifikasi', 'nama_kualifikasi');
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
        return $this->tarif_produk * $jumlahPegawai;
    }

    /**
     * Scope untuk mencari kualifikasi
     */
    public function scopeSearch($query, $search)
    {
        return $query->where('nama_kualifikasi', 'like', "%{$search}%")
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
     * Generate kode kualifikasi otomatis per user (multi-tenant)
     */
    public static function generateKode(): string
    {
        // 🔒 MULTI-TENANT: Only get from logged-in user
        $lastKualifikasi = self::where('user_id', auth()->id())
            ->orderBy('id', 'desc')
            ->first();
        
        if ($lastKualifikasi) {
            // Extract number from BT-XXX format
            $lastNumber = (int) substr($lastKualifikasi->kode_kualifikasi, 3);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }
        
        return 'BT-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Boot method untuk auto-generate kode_kualifikasi
     */
    protected static function booted()
    {
        parent::boot();
        
        static::creating(function ($model) {
            // CRITICAL: Auto-fill user_id for multi-tenant isolation
            if (empty($model->user_id) && auth()->check()) {
                $model->user_id = auth()->id();
            }
            
            // Auto-generate kode_kualifikasi if empty
            if (empty($model->kode_kualifikasi)) {
                $model->kode_kualifikasi = self::generateKode();
            }
        });
    }
}
