<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KategoriPegawai extends Model
{
    use HasFactory;

    protected $table = 'kategori_pegawai';
    protected $fillable = [
        'user_id',
        'nama', 
        'deskripsi',
        'tipe_gaji',
        'is_produksi'
    ];

    protected $casts = [
        'nama' => 'string',
        'deskripsi' => 'string',
        'tipe_gaji' => 'string',
        'is_produksi' => 'boolean',
    ];

    /**
     * Boot method untuk auto-fill user_id untuk multi-tenant isolation
     */
    protected static function booted()
    {
        static::creating(function ($model) {
            // CRITICAL: Auto-fill user_id for multi-tenant isolation
            if (empty($model->user_id) && auth()->check()) {
                $model->user_id = auth()->id();
            }
        });
    }

    /**
     * Relasi ke jabatan
     */
    public function jabatans(): HasMany
    {
        return $this->hasMany(Jabatan::class, 'kategori_id');
    }

    /**
     * Relasi ke pegawai
     */
    public function pegawais(): HasMany
    {
        return $this->hasMany(Pegawai::class, 'kategori_id');
    }

    /**
     * Scope untuk mencari kategori
     */
    public function scopeSearch($query, $search)
    {
        return $query->where('nama', 'like', "%{$search}%")
                    ->orWhere('deskripsi', 'like', "%{$search}%");
    }
}
